import { Injectable, Logger } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { FengIdsResolver } from './feng-ids.resolver';
import { TABLE_PREFIX } from './table-prefix';

/**
 * Ports Feng's PHP SystemPermissions::userHasSystemPermission() logic to TypeScript.
 *
 * PHP reference: SystemPermissions::userHasSystemPermission() in
 *   application/models/system_permissions/SystemPermissions.class.php
 *
 * Exact PHP flow:
 *  1. isAdministrator() — user_type resolves to 'Super Administrator' group → always allow.
 *  2. ContactPermissionGroups::getPermissionGroupIdsByContactCSV($userId, false)
 *       → SELECT cp.permission_group_id FROM fo_contact_permission_groups cp
 *           INNER JOIN fo_permission_groups p ON p.id = cp.permission_group_id
 *          WHERE cp.contact_id = $userId AND p.is_context = 0
 *       Gives the user's personal group + any non-context secondary groups.
 *       Context groups (per-workspace access groups, is_context=1) are intentionally excluded.
 *  3. fo_system_permissions WHERE <permission> = 1 AND permission_group_id IN (<ids from step 2>)
 *  4. fo_max_system_permissions for user_type (role) — if cap = 0, revoke regardless of step 3.
 *
 * Common bug: omitting the is_context=0 filter causes context groups to grant permissions
 * that the user's actual role and personal group don't have.
 */
@Injectable()
export class FengPermissionsService {
  private readonly logger = new Logger(FengPermissionsService.name);

  constructor(
    private readonly prisma: PrismaService,
    private readonly fengIds: FengIdsResolver,
  ) {}

  /**
   * Returns true if the user with `userId` has the `can_manage_dimension_members`
   * system permission in Feng — i.e., is allowed to create projects/workspaces.
   */
  async canManageDimensionMembers(userId: number): Promise<boolean> {
    // Step 1: load user_type (role group ID) from fo_contacts.
    const contactRows = await this.prisma.$queryRawUnsafe(
      `SELECT user_type FROM ${TABLE_PREFIX}contacts WHERE object_id = ? LIMIT 1`,
      userId,
    ) as Array<{ user_type: number }>;
    if (contactRows.length === 0) return false;
    const userType = Number(contactRows[0].user_type);

    // Step 2: Super Administrator check — mirrors Contact::isAdministrator().
    // superAdminGroupId is resolved once at startup by FengIdsResolver, so this
    // comparison is a simple in-memory equality check with no DB round-trip.
    if (userType === this.fengIds.superAdminGroupId) {
      this.logger.debug(`User ${userId} is Super Administrator — permission granted`);
      return true;
    }

    // Step 3: collect the user's non-context permission group IDs.
    //
    // Mirrors ContactPermissionGroups::getPermissionGroupIdsByContactCSV($userId, false):
    //   INNER JOIN fo_permission_groups + WHERE p.is_context = 0
    //
    // Why is_context = 0 matters:
    //   Feng creates per-workspace context groups (is_context=1) and adds users to them when
    //   granting workspace access. These context groups can appear in fo_system_permissions
    //   with can_manage_dimension_members=1 (copied from parent group at workspace creation).
    //   Including them here would incorrectly grant system-level permission to any user who
    //   has ever been given access to any workspace — even after their role permission is revoked.
    const pgRows = await this.prisma.$queryRawUnsafe(
      `SELECT cp.permission_group_id
       FROM ${TABLE_PREFIX}contact_permission_groups cp
       INNER JOIN ${TABLE_PREFIX}permission_groups p ON p.id = cp.permission_group_id
       WHERE cp.contact_id = ? AND p.is_context = 0`,
      userId,
    ) as Array<{ permission_group_id: number }>;
    if (pgRows.length === 0) {
      this.logger.debug(
        `User ${userId} has no non-context permission groups — permission denied`,
      );
      return false;
    }

    const pgIds = pgRows.map((r: { permission_group_id: number }) => Number(r.permission_group_id));
    const placeholders = pgIds.map(() => '?').join(',');

    // Step 4: check fo_system_permissions for can_manage_dimension_members = 1
    // across all non-context groups collected in step 3.
    const permRows = await this.prisma.$queryRawUnsafe(
      `SELECT COUNT(*) AS cnt
       FROM ${TABLE_PREFIX}system_permissions
       WHERE can_manage_dimension_members = 1
         AND permission_group_id IN (${placeholders})`,
      ...pgIds,
    ) as Array<{ cnt: bigint }>;
    if (Number(permRows[0]?.cnt) === 0) {
      this.logger.debug(
        `User ${userId} — no non-context permission group has can_manage_dimension_members=1`,
      );
      return false;
    }

    // Step 5: role-level cap — mirrors the fo_max_system_permissions check in PHP.
    // If the user's role (user_type) has the cap set to 0, revoke the permission
    // even if a personal/secondary group granted it in step 4.
    const maxRows = await this.prisma.$queryRawUnsafe(
      `SELECT can_manage_dimension_members
       FROM ${TABLE_PREFIX}max_system_permissions
       WHERE permission_group_id = ?`,
      userType,
    ) as Array<{ can_manage_dimension_members: number | null }>;
    if (maxRows.length > 0 && !maxRows[0].can_manage_dimension_members) {
      this.logger.debug(
        `User ${userId} — role cap (fo_max_system_permissions) blocks can_manage_dimension_members`,
      );
      return false;
    }

    return true;
  }
}
