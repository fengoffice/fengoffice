import { Global, Module } from '@nestjs/common';
import { FengIdsResolver } from './feng-ids.resolver';
import { FengPermissionsService } from './feng-permissions.service';

/**
 * Global module that exposes Feng-specific infrastructure services to all
 * feature modules without requiring explicit imports.
 *
 * Provided services:
 *  - FengIdsResolver   — resolves DB-specific IDs (object_type_id, parent_member_id) at boot
 *  - FengPermissionsService — ports Feng's PHP SystemPermissions checks to TypeScript
 */
@Global()
@Module({
  providers: [FengIdsResolver, FengPermissionsService],
  exports: [FengIdsResolver, FengPermissionsService],
})
export class CommonModule {}
