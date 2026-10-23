import { Injectable, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import * as crypto from 'crypto';
import { PrismaService } from '../prisma/prisma.service';
import { TABLE_PREFIX } from '../common/table-prefix';

const REFRESH_TOKEN_TYPE = 'api_refresh';
const REFRESH_TOKEN_TTL_DAYS = 30;

// Minimal interface shared by PrismaService and Prisma transaction clients.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
interface RawExecutor { $executeRawUnsafe(query: string, ...values: any[]): Promise<number> }

interface ContactRow {
  object_id: number;
  username: string;
  first_name: string;
  surname: string;
  display_name: string | null;
  user_type: number;
  disabled: number; // raw tinyint(1) — cast to boolean where needed
  token: string;
  salt: string;
}

interface TokenRow {
  id: number;
  contact_id: number;
  token: string;
  type: string;
  expired_date: Date | string;
}

const CONTACT_SELECT = `
  object_id, username, first_name, surname, display_name,
  user_type, disabled, token, salt
`;

@Injectable()
export class AuthService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly jwt: JwtService,
  ) {}

  // ─── Internal helpers ────────────────────────────────────────────────────

  private async findContactByUsername(username: string): Promise<ContactRow | null> {
    const rows = await this.prisma.$queryRawUnsafe(
      `SELECT ${CONTACT_SELECT}
       FROM ${TABLE_PREFIX}contacts
       WHERE username = ?
       LIMIT 1`,
      username,
    ) as ContactRow[];
    return rows[0] ?? null;
  }

  private async findContactByEmail(email: string): Promise<ContactRow | null> {
    // Single query joining contacts → contact_emails for efficiency.
    const rows = await this.prisma.$queryRawUnsafe(
      `SELECT c.object_id, c.username, c.first_name, c.surname, c.display_name,
              c.user_type, c.disabled, c.token, c.salt
       FROM ${TABLE_PREFIX}contacts c
       INNER JOIN ${TABLE_PREFIX}contact_emails ce ON ce.contact_id = c.object_id
       WHERE ce.email_address = ?
       LIMIT 1`,
      email,
    ) as ContactRow[];
    return rows[0] ?? null;
  }

  private async findContactById(id: number): Promise<ContactRow | null> {
    const rows = await this.prisma.$queryRawUnsafe(
      `SELECT ${CONTACT_SELECT}
       FROM ${TABLE_PREFIX}contacts
       WHERE object_id = ?
       LIMIT 1`,
      id,
    ) as ContactRow[];
    return rows[0] ?? null;
  }

  private validatePassword(contact: ContactRow, plaintext: string): boolean {
    const hash = crypto.createHash('sha1').update(contact.salt + plaintext).digest('hex');
    return hash === contact.token;
  }

  private async issueRefreshToken(
    client: RawExecutor,
    contactId: number,
  ): Promise<string> {
    const raw = crypto.randomBytes(40).toString('hex');
    const expiry = new Date();
    expiry.setDate(expiry.getDate() + REFRESH_TOKEN_TTL_DAYS);

    // Raw INSERT — avoids Prisma reading back the row and hitting Feng's
    // '0000-00-00 00:00:00' zero-date in created_date.
    await client.$executeRawUnsafe(
      `INSERT INTO ${TABLE_PREFIX}contact_external_tokens
         (contact_id, token, type, expired_date)
       VALUES (?, ?, ?, ?)`,
      contactId,
      raw,
      REFRESH_TOKEN_TYPE,
      expiry,
    );

    return raw;
  }

  private buildResponse(contact: ContactRow, access_token: string, refresh_token: string) {
    return {
      access_token,
      refresh_token,
      expires_in: 7 * 24 * 60 * 60, // seconds
      user: {
        id: contact.object_id,
        username: contact.username,
        display_name: contact.display_name ?? `${contact.first_name} ${contact.surname}`.trim(),
      },
    };
  }

  // ─── Public endpoints ────────────────────────────────────────────────────

  async login(identifier: string, password: string) {
    // Resolve contact — try email if identifier contains @, otherwise username.
    // Also fall back: if username lookup fails and identifier has @, try email.
    const isEmail = identifier.includes('@');
    let contact = isEmail
      ? await this.findContactByEmail(identifier)
      : await this.findContactByUsername(identifier);

    // Graceful fallback: username that looks like an email, or email used as username
    if (!contact) {
      contact = isEmail
        ? await this.findContactByUsername(identifier)
        : await this.findContactByEmail(identifier);
    }

    if (!contact) throw new UnauthorizedException('Invalid credentials');
    if (contact.user_type === 0) throw new UnauthorizedException('Invalid credentials');
    if (contact.disabled) throw new UnauthorizedException('Account is disabled');
    if (!this.validatePassword(contact, password)) throw new UnauthorizedException('Invalid credentials');

    const [access_token, refresh_token] = await Promise.all([
      this.jwt.signAsync({ sub: contact.object_id, username: contact.username }),
      this.issueRefreshToken(this.prisma, contact.object_id),
    ]);

    return this.buildResponse(contact, access_token, refresh_token);
  }

  async refresh(rawToken: string) {
    const stored = await this.prisma.$queryRawUnsafe(
      `SELECT id, contact_id, token, type, expired_date
       FROM ${TABLE_PREFIX}contact_external_tokens
       WHERE token = ? AND type = ? AND expired_date > NOW()
       LIMIT 1`,
      rawToken,
      REFRESH_TOKEN_TYPE,
    ) as TokenRow[];

    if (stored.length === 0) throw new UnauthorizedException('Invalid or expired refresh token');
    const row = stored[0];

    const contact = await this.findContactById(row.contact_id);

    if (!contact || contact.user_type === 0 || contact.disabled) {
      throw new UnauthorizedException('Account is no longer active');
    }

    // Rotate: delete old token and insert new one atomically so a failure
    // between the two operations cannot leave the user without a valid refresh token.
    const refresh_token = await this.prisma.$transaction(async (tx: RawExecutor) => {
      await tx.$executeRawUnsafe(
        `DELETE FROM ${TABLE_PREFIX}contact_external_tokens WHERE id = ?`,
        row.id,
      );
      return this.issueRefreshToken(tx, contact.object_id);
    });

    const access_token = await this.jwt.signAsync({
      sub: contact.object_id,
      username: contact.username,
    });

    return this.buildResponse(contact, access_token, refresh_token);
  }

  /**
   * Revokes the given refresh token.
   *
   * @param rawToken  The plain-text refresh token from the request body.
   * @param userId    The authenticated user's ID (from the JWT). Used to ensure
   *                  a user can only revoke their own tokens — prevents a bearer
   *                  of a stolen JWT from revoking a token that doesn't belong to them.
   */
  async logout(rawToken: string, userId: number) {
    await this.prisma.$executeRawUnsafe(
      `DELETE FROM ${TABLE_PREFIX}contact_external_tokens
       WHERE token = ? AND type = ? AND contact_id = ?`,
      rawToken,
      REFRESH_TOKEN_TYPE,
      userId,
    );
    return { message: 'Logged out successfully' };
  }
}
