import { ApiProperty } from '@nestjs/swagger';
import { IsNotEmpty, IsString } from 'class-validator';

export class LoginDto {
  /**
   * Feng username OR email address — the API accepts either.
   */
  @ApiProperty({
    description: 'Feng username OR email address',
    example: 'roger',
  })
  @IsString()
  @IsNotEmpty()
  identifier!: string;

  @ApiProperty({ example: 'admin' })
  @IsString()
  @IsNotEmpty()
  password!: string;
}

export class RefreshDto {
  @ApiProperty({ description: '30-day single-use refresh token' })
  @IsString()
  @IsNotEmpty()
  refresh_token!: string;
}
