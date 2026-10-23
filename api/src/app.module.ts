import { Module } from '@nestjs/common';
import { ThrottlerModule } from '@nestjs/throttler';
import { AuthModule } from './auth/auth.module';
import { CommonModule } from './common/common.module';
import { PrismaModule } from './prisma/prisma.module';
import { ProjectsModule } from './projects/projects.module';

@Module({
  imports: [
    // Rate-limiter store — used selectively on auth endpoints via @UseGuards(ThrottlerGuard).
    // ttl is in milliseconds; this config allows 10 attempts per minute.
    ThrottlerModule.forRoot([{ ttl: 60_000, limit: 10 }]),
    PrismaModule,
    // CommonModule is @Global — FengIdsResolver and FengPermissionsService are
    // available in all feature modules without explicit imports.
    CommonModule,
    AuthModule,
    ProjectsModule,
  ],
})
export class AppModule {}
