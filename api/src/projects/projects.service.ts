import { Injectable, Logger, NotFoundException } from '@nestjs/common';
import { Prisma } from '@prisma/client';
import { PrismaService } from '../prisma/prisma.service';
import { FengIdsResolver } from '../common/feng-ids.resolver';
import { buildMeta } from '../common/pagination.dto';
import { toIso } from '../common/to-iso';
import { TABLE_PREFIX } from '../common/table-prefix';

// Feng stores both the project number and the project description as project
// object custom properties (resolved generically by code).
const PROJECT_NUMBER_CP_CODE = 'project_id';
const DESCRIPTION_CP_CODE = 'description_special';

export interface ProjectsListFilters {
  page: number;
  per_page: number;
}

export interface CreateProjectData {
  name: string;
  projectNumber: string;
  description?: string;
}

interface RawProjectRow {
  /** Internal fo_projects.object_id — used server-side only, never returned. */
  id: number;
  name: string;
  description: string | null;
  status: number | null;
  start_date: Date | null;
  end_date: Date | null;
  created_on: Date | null;
  updated_on: Date | null;
  project_number: string | null;
}

const ZERO = "'0000-00-00 00:00:00'";

/**
 * Coerce a raw TEXT column value to a string (or null). The MySQL driver can
 * return TEXT columns as Buffer; without this, project_number/description would
 * serialize as `{"type":"Buffer","data":[…]}` instead of a string.
 */
function asText(value: unknown): string | null {
  if (value == null) return null;
  if (Buffer.isBuffer(value)) return value.toString('utf8');
  return String(value);
}

function toDto(row: RawProjectRow) {
  // NOTE: the internal object id (row.id) is intentionally NOT exposed. The
  // project number (custom property) is the external identifier.
  return {
    project_number: asText(row.project_number),
    name: row.name,
    description: asText(row.description),
    status: row.status,
    start_date: toIso(row.start_date),
    end_date: toIso(row.end_date),
    created_on: toIso(row.created_on),
    updated_on: toIso(row.updated_on),
  };
}

// Both `project_number` and `description` come from object-level custom property
// values (cpv_num / cpv_desc), NOT from fo_projects.description.
const SELECT_COLS = `
  p.object_id AS id,
  o.name,
  cpv_desc.value AS description,
  p.status,
  NULLIF(p.start_date, ${ZERO}) AS start_date,
  NULLIF(p.end_date,   ${ZERO}) AS end_date,
  NULLIF(o.created_on, ${ZERO}) AS created_on,
  NULLIF(o.updated_on, ${ZERO}) AS updated_on,
  cpv_num.value AS project_number
`;

const ACTIVE_FILTER = `
  (o.trashed_on IS NULL OR o.trashed_on = ${ZERO})
  AND (o.archived_on IS NULL OR o.archived_on = ${ZERO})
  AND p.type_content IS NOT NULL AND p.type_content != ''
`;

@Injectable()
export class ProjectsService {
  private readonly logger = new Logger(ProjectsService.name);

  constructor(
    private readonly prisma: PrismaService,
    private readonly fengIds: FengIdsResolver,
  ) {}

  /**
   * Resolve the two project custom property definition ids (by code) used by
   * this service. Fails clearly (via FengIdsResolver) if either is missing/disabled.
   */
  private async resolveCpIds(): Promise<{ numCpId: number; descCpId: number }> {
    const [numCpId, descCpId] = await Promise.all([
      this.fengIds.getProjectCustomPropertyIdByCode(PROJECT_NUMBER_CP_CODE),
      this.fengIds.getProjectCustomPropertyIdByCode(DESCRIPTION_CP_CODE),
    ]);
    return { numCpId, descCpId };
  }

  /**
   * SQL fragment that LEFT JOINs the project-number and description custom
   * property values onto the project object (object-level).
   * Both ids are resolved integers, safe to interpolate.
   */
  private cpJoins(numCpId: number, descCpId: number): string {
    return `
      LEFT JOIN ${TABLE_PREFIX}custom_property_values cpv_num
        ON cpv_num.object_id = p.object_id AND cpv_num.custom_property_id = ${numCpId}
      LEFT JOIN ${TABLE_PREFIX}custom_property_values cpv_desc
        ON cpv_desc.object_id = p.object_id AND cpv_desc.custom_property_id = ${descCpId}`;
  }

  /** Fetch one active project row by internal object id, including number + description. */
  private async fetchActiveByObjectId(
    objectId: number,
    numCpId: number,
    descCpId: number,
  ): Promise<RawProjectRow | null> {
    const rows = (await this.prisma.$queryRawUnsafe(
      `SELECT ${SELECT_COLS}
       FROM ${TABLE_PREFIX}projects p
       INNER JOIN ${TABLE_PREFIX}objects o ON o.id = p.object_id
       ${this.cpJoins(numCpId, descCpId)}
       WHERE p.object_id = ? AND ${ACTIVE_FILTER}
       LIMIT 1`,
      objectId,
    )) as RawProjectRow[];
    return rows[0] ?? null;
  }

  async list(filters: ProjectsListFilters) {
    const { page, per_page } = filters;
    const offset = (page - 1) * per_page;
    const { numCpId, descCpId } = await this.resolveCpIds();

    const whereSql = `
      FROM ${TABLE_PREFIX}projects p
      INNER JOIN ${TABLE_PREFIX}objects o ON o.id = p.object_id
      WHERE ${ACTIVE_FILTER}
    `;

    const countRows = await this.prisma.$queryRawUnsafe(
      `SELECT COUNT(*) AS total ${whereSql}`,
    ) as Array<{ total: bigint }>;
    const total = Number(countRows[0]?.total ?? 0);

    const rows = await this.prisma.$queryRawUnsafe(
      `SELECT ${SELECT_COLS}
       FROM ${TABLE_PREFIX}projects p
       INNER JOIN ${TABLE_PREFIX}objects o ON o.id = p.object_id
       ${this.cpJoins(numCpId, descCpId)}
       WHERE ${ACTIVE_FILTER}
       ORDER BY o.name ASC LIMIT ? OFFSET ?`,
      per_page,
      offset,
    ) as RawProjectRow[];

    return {
      data: rows.map(toDto),
      meta: buildMeta(total, page, per_page),
    };
  }

  /** Fetch a single project by its internal object id (server-side use). */
  async findOne(id: number) {
    const { numCpId, descCpId } = await this.resolveCpIds();
    const row = await this.fetchActiveByObjectId(id, numCpId, descCpId);
    if (!row) throw new NotFoundException(`Project ${id} not found`);
    return toDto(row);
  }

  /**
   * Resolve a project by its project number (the `project_id` custom property
   * value), object-level.
   *
   * Single active-aware query: the value match and ACTIVE_FILTER are applied in
   * the same statement (via the cpv_num join), so an active project is always
   * preferred over a trashed/archived one that happens to share the number — we
   * never 404 while a live match exists. ORDER BY keeps the result deterministic
   * if duplicate numbers ever exist in the data.
   */
  async findByProjectNumber(projectNumber: string) {
    const { numCpId, descCpId } = await this.resolveCpIds();

    const rows = (await this.prisma.$queryRawUnsafe(
      `SELECT ${SELECT_COLS}
       FROM ${TABLE_PREFIX}projects p
       INNER JOIN ${TABLE_PREFIX}objects o ON o.id = p.object_id
       ${this.cpJoins(numCpId, descCpId)}
       WHERE cpv_num.value = ? AND ${ACTIVE_FILTER}
       ORDER BY p.object_id ASC
       LIMIT 1`,
      projectNumber,
    )) as RawProjectRow[];

    return rows[0] ? toDto(rows[0]) : null;
  }

  async create(data: CreateProjectData, userId: number) {
    // Read resolved IDs from FengIdsResolver (no hardcoded constants).
    const projectObjectTypeId = this.fengIds.projectObjectTypeId;
    const dimensionId = this.fengIds.projectDimensionId;

    // Resolve the project-number + description custom property ids up front so we
    // fail clearly (before any inserts) if either definition is missing/disabled.
    const { numCpId: projectNumberCpId, descCpId: descriptionCpId } =
      await this.resolveCpIds();

    // Projects created via the API have no "Located under" parent (same as
    // leaving the field empty in Feng's UI): parent_member_id = 0, depth = 1.
    const PARENT_MEMBER_ID = 0;
    const MEMBER_DEPTH = 1;
    const STATUS_NOT_STARTED = 5; // matches Feng UI default

    const newId = await this.prisma.$transaction(async (tx: Prisma.TransactionClient) => {
      await tx.$executeRawUnsafe(
        `INSERT INTO ${TABLE_PREFIX}objects
           (object_type_id, name, created_on, created_by_id, updated_on, updated_by_id,
            timezone_id, timezone_value, object_subtype_id,
            trashed_on, trashed_by_id, archived_on, archived_by_id)
         VALUES (?, ?, NOW(), ?, NOW(), ?, 0, 0, 0,
                 '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 0)`,
        projectObjectTypeId,
        data.name,
        userId,
        userId,
      );

      const [{ id }] = await tx.$queryRawUnsafe(
        `SELECT LAST_INSERT_ID() AS id`,
      ) as [{ id: bigint }];
      const objectId = Number(id);

      // fo_projects.description is a legacy column — the real project description
      // lives in the `description_special` custom property (inserted below).
      await tx.$executeRawUnsafe(
        `INSERT INTO ${TABLE_PREFIX}projects
           (object_id, description, status, customer_id, manager_id,
            start_date, end_date, type_content, automatic_status)
         VALUES
           (?, '', ?, 0, ?,
            '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'text', 1)`,
        objectId,
        STATUS_NOT_STARTED,
        userId,
      );

      // Store the project number as the "Project ID" custom property value
      // (object-level, code='project_id')
      await tx.$executeRawUnsafe(
        `INSERT INTO ${TABLE_PREFIX}custom_property_values
           (object_id, custom_property_id, value, currency_id)
         VALUES (?, ?, ?, 0)`,
        objectId,
        projectNumberCpId,
        data.projectNumber,
      );

      // Store the description as the "Description" custom property value
      // (object-level, code='description_special') — not fo_projects.description.
      if (data.description !== undefined) {
        await tx.$executeRawUnsafe(
          `INSERT INTO ${TABLE_PREFIX}custom_property_values
             (object_id, custom_property_id, value, currency_id)
           VALUES (?, ?, ?, 0)`,
          objectId,
          descriptionCpId,
          data.description,
        );
      }

      // parent_member_id = 0 → no "Located under" parent (root-level project).
      // depth = 1 → matches what Feng PHP sets when no parent is selected.
      await tx.$executeRawUnsafe(
        `INSERT INTO ${TABLE_PREFIX}members
           (dimension_id, object_type_id, parent_member_id, depth, name,
            object_id, archived_by_id, archived_on, color, \`order\`,
            description, display_name)
         VALUES
           (?, ?, ?, ?, ?,
            ?, 0, '0000-00-00 00:00:00', 0, 0,
            '', ?)`,
        dimensionId,
        projectObjectTypeId,
        PARENT_MEMBER_ID,
        MEMBER_DEPTH,
        data.name,
        objectId,
        data.name, // display_name — PHP sets this via build_member_display_name(), falls back to name
      );

      const [{ id: rawMemberId }] = await tx.$queryRawUnsafe(
        `SELECT LAST_INSERT_ID() AS id`,
      ) as [{ id: bigint }];
      const memberId = Number(rawMemberId);

      // ── Member permissions ─────────────────────────────────────────────────
      // Feng's PHP UI calls save_member_permissions() after creating a member,
      // which populates fo_contact_member_permissions. Without these rows the
      // project edit form crashes (count() on null) and users cannot see the
      // project in permission-filtered list views.
      //
      // Since this project has no parent (parent_member_id = 0), there are no
      // permissions to inherit. Fall back directly to granting the creator's
      // permission group full access across all content object types registered
      // for this project type in its dimension.
      const creatorRows = await tx.$queryRawUnsafe(
        `SELECT user_type FROM ${TABLE_PREFIX}contacts WHERE object_id = ? LIMIT 1`,
        userId,
      ) as [{ user_type: number }];
      const creatorPgId = creatorRows[0]
        ? Number(creatorRows[0].user_type)
        : null;

      const dotRows = await tx.$queryRawUnsafe(
        `SELECT content_object_type_id
         FROM ${TABLE_PREFIX}dimension_object_type_contents
         WHERE dimension_id = ? AND dimension_object_type_id = ?`,
        dimensionId,
        projectObjectTypeId,
      ) as Array<{ content_object_type_id: number }>;

      if (creatorPgId && dotRows.length > 0) {
        const values = dotRows.map(() => `(?, ?, ?, 1, 1)`).join(', ');
        const params: number[] = dotRows.flatMap((r: { content_object_type_id: number }) => [
          creatorPgId,
          objectId,
          Number(r.content_object_type_id),
        ]);
        await tx.$executeRawUnsafe(
          `INSERT IGNORE INTO ${TABLE_PREFIX}contact_member_permissions
             (permission_group_id, member_id, object_type_id, can_delete, can_write)
           VALUES ${values}`,
          ...params,
        );

        // ── Contact member cache ───────────────────────────────────────────
        // PHP calls afterMemberPermissionChanged → updateContactMemberCache for
        // every user in the affected permission groups after saving permissions.
        // Without this, only Super Administrators see the new project in the
        // dimension panel (everyone else reads from fo_contact_member_cache).
        //
        // We cover: (a) users whose primary role = creatorPgId (user_type)
        //           (b) users who are secondary group members (fo_contact_permission_groups)
        // parent_member_id = 0 because this is a root-level project.
        await tx.$executeRawUnsafe(
          `INSERT IGNORE INTO ${TABLE_PREFIX}contact_member_cache
             (contact_id, member_id, parent_member_id)
           SELECT c.object_id, ?, 0
           FROM ${TABLE_PREFIX}contacts c
           WHERE c.disabled = 0
             AND (
               c.user_type = ?
               OR EXISTS (
                 SELECT 1 FROM ${TABLE_PREFIX}contact_permission_groups cpg
                 WHERE cpg.contact_id = c.object_id
                   AND cpg.permission_group_id = ?
               )
             )`,
          memberId,
          creatorPgId,
          creatorPgId,
        );
      }
      // ──────────────────────────────────────────────────────────────────────

      return objectId;
    });

    this.logger.log(
      `Project created: object_id=${newId} project_number="${data.projectNumber}" name="${data.name}"`,
    );

    return this.findOne(newId);
  }
}
