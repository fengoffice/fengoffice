import { Body, Controller, Post, Req, UseGuards } from '@nestjs/common';
import {
  ApiBearerAuth,
  ApiOperation,
  ApiProperty,
  ApiResponse,
  ApiTags,
} from '@nestjs/swagger';
import { Throttle, ThrottlerGuard } from '@nestjs/throttler';
import {
  ApiEnvelopeResponse,
  ErrorEnvelopeDto,
} from '../common/envelope.dto';
import { AuthService } from './auth.service';
import { LoginDto, RefreshDto } from './login.dto';
import { Public } from './public.decorator';

// ── Response DTOs ───────────────────────────────────────────────────────────

class AuthUserDto {
  @ApiProperty({ example: 42 })
  id!: number;

  @ApiProperty({ example: 'alvaro' })
  username!: string;

  @ApiProperty({ example: 'Alvaro Gomez' })
  display_name!: string;
}

export class AuthTokensResponseDto {
  @ApiProperty({ description: '7-day JWT (HS256)', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...' })
  access_token!: string;

  @ApiProperty({ description: '30-day single-use refresh token (hex)', example: '5a4f...c2' })
  refresh_token!: string;

  @ApiProperty({ description: 'Access token lifetime in seconds', example: 604800 })
  expires_in!: number;

  @ApiProperty({ type: AuthUserDto })
  user!: AuthUserDto;
}

export class LogoutResponseDto {
  @ApiProperty({ example: 'Logged out successfully' })
  message!: string;
}

// ── Controller ──────────────────────────────────────────────────────────────

@ApiTags('auth')
@Controller('auth')
export class AuthController {
  constructor(private readonly auth: AuthService) {}

  /**
   * Login with username OR email + password.
   * Returns access_token (7 days) + refresh_token (30 days).
   * Rate-limited to 10 attempts per minute per IP.
   */
  @Public()
  @UseGuards(ThrottlerGuard)
  @Throttle({ default: { ttl: 60_000, limit: 10 } })
  @Post('login')
  @ApiOperation({
    summary: 'Login',
    description:
      'Accepts a Feng username OR email address. Returns a 7-day JWT access token and a 30-day refresh token. Rate-limited to 10 req/min per IP.',
  })
  @ApiEnvelopeResponse(AuthTokensResponseDto, {
    description: 'Returns access_token, refresh_token, expires_in, and user info.',
  })
  @ApiResponse({ status: 401, description: 'Invalid credentials, disabled account, or contact-only user.', type: ErrorEnvelopeDto })
  @ApiResponse({ status: 429, description: 'Too many requests.', type: ErrorEnvelopeDto })
  login(@Body() body: LoginDto) {
    return this.auth.login(body.identifier, body.password);
  }

  /**
   * Exchange a valid refresh token for a new access_token + rotated refresh_token.
   * Old refresh token is invalidated on use.
   * Rate-limited to 10 attempts per minute per IP.
   */
  @Public()
  @UseGuards(ThrottlerGuard)
  @Throttle({ default: { ttl: 60_000, limit: 10 } })
  @Post('refresh')
  @ApiOperation({
    summary: 'Refresh tokens',
    description: 'Exchanges a still-valid refresh token for a new access+refresh pair. Single-use — old token is revoked.',
  })
  @ApiEnvelopeResponse(AuthTokensResponseDto, {
    description: 'Returns new access_token and rotated refresh_token.',
  })
  @ApiResponse({ status: 401, description: 'Refresh token invalid, expired, or already used.', type: ErrorEnvelopeDto })
  @ApiResponse({ status: 429, description: 'Too many requests.', type: ErrorEnvelopeDto })
  refresh(@Body() body: RefreshDto) {
    return this.auth.refresh(body.refresh_token);
  }

  /**
   * Invalidate the current refresh token (logout from this device).
   * Requires a valid JWT so we know the request is authenticated,
   * but the body carries the refresh_token to revoke.
   */
  @Post('logout')
  @ApiBearerAuth('access-token')
  @ApiOperation({
    summary: 'Logout',
    description: 'Revokes the given refresh token. Requires a valid Bearer JWT in the Authorization header.',
  })
  @ApiEnvelopeResponse(LogoutResponseDto, { description: 'Refresh token revoked.' })
  @ApiResponse({ status: 401, description: 'Missing or invalid access token.', type: ErrorEnvelopeDto })
  logout(
    @Body() body: RefreshDto,
    @Req() req: { user: { id: number } },
  ) {
    // Pass the authenticated user's ID so the service can verify token ownership.
    return this.auth.logout(body.refresh_token, req.user.id);
  }
}
