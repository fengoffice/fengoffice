import {
  Injectable,
  InternalServerErrorException,
  Logger,
  OnModuleInit,
} from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { TABLE_PREFIX } from './table-prefix';

/**
 * Resolves Feng DB constants that vary per installation at application startup.
 *
 * Why: Object type IDs and dimension IDs are assigned at install time and differ
 * across Feng instances. Hardcoding them breaks portability.
 *
 * Resolved values:
 *  - projectDimensionId    → fo_dimensions WHERE code = 'customer_project'
 *  - projectObjectTypeId   → fo_object_types WHERE name = 'project'
 *  - superAdminGroupId     → fo_permission_groups WHERE name = 'Super Administrator'
 *
 * Parent member / depth for new projects:
 *  Projects created without a "Located under" parent use parent_member_id = 0
 *  and depth = 1 — the same values Feng PHP sets when the user leaves the
 *  "Located under" field empty in the project creation form.
 */
@Injectable()
export class FengIdsResolver implements OnModuleInit {
  private readonly logger = new Logger(FengIdsResolver.name);

  private _projectDimensionId: number | null = null;
  private _projectObjectTypeId: number | null = null;
  private _superAdminGroupId: number | null = null;
  /** Cache of resolved project custom property ids, keyed by CP code. */
  private readonly _projectCpIdByCode = new Map<string, number>();

  constructor(private readonly prisma: PrismaService) {}

  async onModuleInit(): Promise<void> {
    await this.resolve();
  }

  // ── Internal bootstrap ─────────────────────────────────────────────────────

  private async resolve(): Promise<void> {
    // 1. Resolve dimension_id from fo_dimensions by stable code 'customer_project'
    const dimRows = await this.prisma.$queryRawUnsafe(
      `SELECT id FROM ${TABLE_PREFIX}dimensions WHERE code = ? LIMIT 1`,
      'customer_project',
    ) as Array<{ id: number }>;
    if (dimRows.length === 0) {
      throw new Error(
        `FengIdsResolver: cannot find dimension with code='customer_project' ` +
          `in ${TABLE_PREFIX}dimensions. Ensure the Feng database is fully initialised.`,
      );
    }
    this._projectDimensionId = Number(dimRows[0].id);

    // 2. Resolve project object_type_id from fo_object_types
    const typeRows = await this.prisma.$queryRawUnsafe(
      `SELECT id FROM ${TABLE_PREFIX}object_types WHERE name = ? LIMIT 1`,
      'project',
    ) as Array<{ id: number }>;
    if (typeRows.length === 0) {
      throw new Error(
        `FengIdsResolver: cannot find 'project' entry in ${TABLE_PREFIX}object_types. ` +
          'Ensure the Feng database is fully initialised.',
      );
    }
    this._projectObjectTypeId = Number(typeRows[0].id);

    // 3. Resolve Super Administrator permission group ID.
    // Comparing by ID at runtime instead of hardcoding the string in SQL means
    // a renamed group fails fast here at startup rather than silently denying
    // admin access on every permission check.
    const adminRows = await this.prisma.$queryRawUnsafe(
      `SELECT id FROM ${TABLE_PREFIX}permission_groups WHERE name = ? LIMIT 1`,
      'Super Administrator',
    ) as Array<{ id: number }>;
    if (adminRows.length === 0) {
      throw new Error(
        `FengIdsResolver: cannot find 'Super Administrator' group in ` +
          `${TABLE_PREFIX}permission_groups. Ensure the Feng database is fully initialised.`,
      );
    }
    this._superAdminGroupId = Number(adminRows[0].id);

    this.logger.log(
      `Resolved Feng IDs — ` +
        `project_dimension_id=${this._projectDimensionId}, ` +
        `project_object_type_id=${this._projectObjectTypeId}, ` +
        `super_admin_group_id=${this._superAdminGroupId}`,
    );
  }

  // ── Public getters ─────────────────────────────────────────────────────────

  /** fo_dimensions.id for code='customer_project' */
  get projectDimensionId(): number {
    if (this._projectDimensionId === null)
      throw new Error('FengIdsResolver not yet initialised');
    return this._projectDimensionId;
  }

  /** fo_object_types.id for name='project' */
  get projectObjectTypeId(): number {
    if (this._projectObjectTypeId === null)
      throw new Error('FengIdsResolver not yet initialised');
    return this._projectObjectTypeId;
  }

  /** fo_permission_groups.id for name='Super Administrator' */
  get superAdminGroupId(): number {
    if (this._superAdminGroupId === null)
      throw new Error('FengIdsResolver not yet initialised');
    return this._superAdminGroupId;
  }

  /**
   * fo_custom_properties.id for a custom property (identified by `code`) on the
   * project object type.
   *
   * Resolved lazily on first use (not at boot, so a missing definition only
   * fails /projects calls, not the whole API) and cached per code. Throws a
   * clear error when the definition is missing or disabled — mirrors the PHP
   * `CustomProperties::getCustomPropertyByCode($projectType, $code)`
   * + `!$cp->getIsDisabled()` guard.
   */
  async getProjectCustomPropertyIdByCode(code: string): Promise<number> {
    const cached = this._projectCpIdByCode.get(code);
    if (cached !== undefined) return cached;

    const rows = (await this.prisma.$queryRawUnsafe(
      `SELECT cp.id
       FROM ${TABLE_PREFIX}custom_properties cp
       INNER JOIN ${TABLE_PREFIX}object_types ot ON ot.id = cp.object_type_id
       WHERE cp.code = ? AND ot.name = ? AND cp.is_disabled = 0
       LIMIT 1`,
      code,
      'project',
    )) as Array<{ id: number }>;

    if (rows.length === 0) {
      throw new InternalServerErrorException(
        `Project custom property (code '${code}') is not defined or is disabled ` +
          'on the project object type in this Feng installation.',
      );
    }

    const id = Number(rows[0].id);
    this._projectCpIdByCode.set(code, id);
    this.logger.log(
      `Resolved project custom property id=${id} (code='${code}')`,
    );
    return id;
  }
}
