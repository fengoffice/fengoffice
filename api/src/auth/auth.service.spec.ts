import { Test, TestingModule } from '@nestjs/testing';
import { JwtService } from '@nestjs/jwt';
import { UnauthorizedException } from '@nestjs/common';
import { AuthService } from './auth.service';
import { PrismaService } from '../prisma/prisma.service';
import * as crypto from 'crypto';

// ── Helpers ──────────────────────────────────────────────────────────────────

function makeContact(overrides: Partial<ReturnType<typeof baseContact>> = {}) {
  return { ...baseContact(), ...overrides };
}

function baseContact() {
  const salt = 'testSalt';
  const password = 'secret';
  const token = crypto.createHash('sha1').update(salt + password).digest('hex');
  return {
    object_id: 42,
    username: 'jdoe',
    first_name: 'John',
    surname: 'Doe',
    display_name: 'John Doe',
    user_type: 1,
    disabled: 0,
    token,
    salt,
    _plain_password: password, // not a real DB column — kept here for test convenience
  };
}

// ── Mock factories ────────────────────────────────────────────────────────────

function buildPrismaMock() {
  return {
    $queryRawUnsafe: jest.fn(),
    $executeRawUnsafe: jest.fn().mockResolvedValue(undefined),
  };
}

function buildJwtMock() {
  return {
    signAsync: jest.fn().mockResolvedValue('mock.access.token'),
  };
}

// ── Test suite ────────────────────────────────────────────────────────────────

describe('AuthService', () => {
  let service: AuthService;
  let prisma: ReturnType<typeof buildPrismaMock>;
  let jwt: ReturnType<typeof buildJwtMock>;

  beforeEach(async () => {
    prisma = buildPrismaMock();
    jwt = buildJwtMock();

    const module: TestingModule = await Test.createTestingModule({
      providers: [
        AuthService,
        { provide: PrismaService, useValue: prisma },
        { provide: JwtService, useValue: jwt },
      ],
    }).compile();

    service = module.get<AuthService>(AuthService);
  });

  // ── login() ─────────────────────────────────────────────────────────────────

  describe('login()', () => {
    it('authenticates by username (happy path)', async () => {
      const contact = makeContact();
      // username lookup → returns contact; issueRefreshToken INSERT
      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([contact]) // findContactByUsername
        .mockResolvedValueOnce([]); // issueRefreshToken has no query

      const result = await service.login(contact.username, contact._plain_password);

      expect(result).toMatchObject({
        access_token: 'mock.access.token',
        refresh_token: expect.any(String),
        expires_in: 7 * 24 * 60 * 60,
        user: {
          id: contact.object_id,
          username: contact.username,
          display_name: contact.display_name,
        },
      });
      expect(jwt.signAsync).toHaveBeenCalledWith({
        sub: contact.object_id,
        username: contact.username,
      });
    });

    it('authenticates by email (happy path)', async () => {
      const contact = makeContact();
      const email = 'jdoe@example.com';
      // identifier contains '@' → tries email first → returns contact
      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([contact]); // findContactByEmail

      const result = await service.login(email, contact._plain_password);

      expect(result.user.id).toBe(contact.object_id);
    });

    it('falls back to username when email lookup finds nothing', async () => {
      const contact = makeContact();
      const emailLikeUsername = 'jdoe@local';
      // First call (email lookup) returns empty; second (username fallback) returns contact
      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([])      // findContactByEmail → not found
        .mockResolvedValueOnce([contact]); // findContactByUsername fallback

      const result = await service.login(emailLikeUsername, contact._plain_password);

      expect(result.user.id).toBe(contact.object_id);
    });

    it('falls back to email when username lookup finds nothing', async () => {
      const contact = makeContact();
      const email = 'jdoe@example.com';
      // identifier has no '@' so it tries username first, fails, then tries email
      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([])       // findContactByUsername → not found
        .mockResolvedValueOnce([contact]); // findContactByEmail fallback

      // login with an identifier that has no '@' so the first attempt is by username
      const result = await service.login('jdoe', contact._plain_password);
      expect(result.user.id).toBe(contact.object_id);
    });

    it('throws UnauthorizedException when user is not found', async () => {
      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([]) // username miss
        .mockResolvedValueOnce([]); // email fallback miss

      await expect(service.login('nobody', 'pass')).rejects.toThrow(
        UnauthorizedException,
      );
    });

    it('throws UnauthorizedException when user_type === 0 (contact-only)', async () => {
      const contact = makeContact({ user_type: 0 });
      prisma.$queryRawUnsafe.mockResolvedValueOnce([contact]);

      await expect(
        service.login(contact.username, contact._plain_password),
      ).rejects.toThrow(UnauthorizedException);
    });

    it('throws UnauthorizedException when account is disabled', async () => {
      const contact = makeContact({ disabled: 1 });
      prisma.$queryRawUnsafe.mockResolvedValueOnce([contact]);

      await expect(
        service.login(contact.username, contact._plain_password),
      ).rejects.toThrow(UnauthorizedException);
    });

    it('throws UnauthorizedException on wrong password', async () => {
      const contact = makeContact();
      prisma.$queryRawUnsafe.mockResolvedValueOnce([contact]);

      await expect(
        service.login(contact.username, 'wrongPassword'),
      ).rejects.toThrow(UnauthorizedException);
    });

    it('uses first_name + surname as display_name when display_name is null', async () => {
      const contact = makeContact({ display_name: null });
      prisma.$queryRawUnsafe.mockResolvedValueOnce([contact]);

      const result = await service.login(contact.username, contact._plain_password);

      expect(result.user.display_name).toBe('John Doe');
    });
  });

  // ── refresh() ───────────────────────────────────────────────────────────────

  describe('refresh()', () => {
    it('rotates token and returns new pair (happy path)', async () => {
      const contact = makeContact();
      const storedRow = {
        id: 99,
        contact_id: contact.object_id,
        token: 'old-refresh-token',
        type: 'api_refresh',
        expired_date: new Date(Date.now() + 86_400_000), // 1 day from now
      };

      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([storedRow])  // token lookup in fo_contact_external_tokens
        .mockResolvedValueOnce([contact]);   // findContactById

      const result = await service.refresh('old-refresh-token');

      expect(result.access_token).toBe('mock.access.token');
      expect(result.refresh_token).not.toBe('old-refresh-token'); // rotated
      // Should have DELETEd the old token
      expect(prisma.$executeRawUnsafe).toHaveBeenCalledWith(
        expect.stringContaining('DELETE'),
        storedRow.id,
      );
    });

    it('throws UnauthorizedException for an expired or unknown token', async () => {
      prisma.$queryRawUnsafe.mockResolvedValueOnce([]); // no rows → invalid token

      await expect(service.refresh('bad-token')).rejects.toThrow(
        UnauthorizedException,
      );
    });

    it('throws UnauthorizedException when account is no longer active', async () => {
      const storedRow = {
        id: 99,
        contact_id: 42,
        token: 'refresh-token',
        type: 'api_refresh',
        expired_date: new Date(Date.now() + 86_400_000),
      };
      const disabledContact = makeContact({ disabled: 1 });

      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([storedRow])
        .mockResolvedValueOnce([disabledContact]);

      await expect(service.refresh('refresh-token')).rejects.toThrow(
        UnauthorizedException,
      );
    });

    it('throws UnauthorizedException when user_type becomes 0 after token issue', async () => {
      const storedRow = {
        id: 99,
        contact_id: 42,
        token: 'refresh-token',
        type: 'api_refresh',
        expired_date: new Date(Date.now() + 86_400_000),
      };
      const demotedContact = makeContact({ user_type: 0 });

      prisma.$queryRawUnsafe
        .mockResolvedValueOnce([storedRow])
        .mockResolvedValueOnce([demotedContact]);

      await expect(service.refresh('refresh-token')).rejects.toThrow(
        UnauthorizedException,
      );
    });
  });

  // ── logout() ────────────────────────────────────────────────────────────────

  describe('logout()', () => {
    it('deletes the refresh token for the authenticated user', async () => {
      const result = await service.logout('some-token', 42);

      expect(prisma.$executeRawUnsafe).toHaveBeenCalledWith(
        expect.stringContaining('DELETE'),
        'some-token',
        'api_refresh',
        42,
      );
      expect(result).toEqual({ message: 'Logged out successfully' });
    });

    it('includes contact_id in the DELETE to prevent cross-user revocation', async () => {
      await service.logout('any-token', 99);

      const [sql, , , userId] = prisma.$executeRawUnsafe.mock.calls[0];
      expect(sql).toContain('contact_id');
      expect(userId).toBe(99);
    });
  });
});
