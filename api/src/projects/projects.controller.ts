import {
  Body,
  Controller,
  ForbiddenException,
  Get,
  HttpStatus,
  NotFoundException,
  Param,
  Post,
  Query,
  Req,
  Res,
} from '@nestjs/common';
import {
  ApiBearerAuth,
  ApiBody,
  ApiOperation,
  ApiParam,
  ApiProperty,
  ApiResponse,
  ApiTags,
} from '@nestjs/swagger';
import {
  ApiEnvelopeResponse,
  ApiPaginatedEnvelopeResponse,
  ErrorEnvelopeDto,
} from '../common/envelope.dto';
import { FengPermissionsService } from '../common/feng-permissions.service';
import { PaginationDto } from '../common/pagination.dto';
import { ProjectsService } from './projects.service';
import { CreateProjectDto } from './projects.dto';
import type { Response } from 'express';

// ── Response DTO (for Swagger schema generation) ─────────────────────────────

export class ProjectResponseDto {
  @ApiProperty({
    example: 'P-1001',
    nullable: true,
    description:
      'Project number — the project custom property with code `project_id`. ' +
      'This is the external identifier; the internal object id is not exposed.',
  })
  project_number!: string | null;

  @ApiProperty({ example: 'API Project - CRM Integration Placeholder' })
  name!: string;

  @ApiProperty({ example: 'Auto-created from CRM deal.', nullable: true })
  description!: string | null;

  @ApiProperty({ example: 0, nullable: true })
  status!: number | null;

  @ApiProperty({ example: '2025-01-02T00:00:00.000Z', nullable: true })
  start_date!: string | null;

  @ApiProperty({ example: '2025-06-30T00:00:00.000Z', nullable: true })
  end_date!: string | null;

  @ApiProperty({ example: '2025-01-02T09:00:00.000Z', nullable: true })
  created_on!: string | null;

  @ApiProperty({ example: '2025-04-01T10:00:00.000Z', nullable: true })
  updated_on!: string | null;
}

@ApiTags('projects')
@ApiBearerAuth('access-token')
@Controller('projects')
export class ProjectsController {
  constructor(
    private readonly projects: ProjectsService,
    private readonly fengPermissions: FengPermissionsService,
  ) {}

  // ── POST /projects ─────────────────────────────────────────────────────────

  /**
   * Idempotent project creation for sequential Power Automate retries.
   *
   * - If a project with the given `project_number` already exists, the existing
   *   project is returned with **200 OK** (no duplicate created).
   * - Otherwise a new project is created and **201 Created** is returned.
   *
   * NOTE: idempotency is best-effort check-then-create. It protects against
   * sequential retries (the common Power Automate case). It does NOT guarantee
   * uniqueness under two *simultaneous* requests with the same number: a DB
   * unique constraint isn't feasible here (fo_custom_property_values is shared by
   * every custom property, so its `value` can't be globally unique). If strict
   * concurrency safety is later required, add a dedicated project-number table
   * with a UNIQUE key, or an advisory lock around the check+create.
   */
  @Post()
  @ApiOperation({
    summary: 'Create project (idempotent)',
    description:
      'Creates a new project (name → title, project_number and description → project ' +
      'custom properties). If a project with the given `project_number` already exists, ' +
      'the existing project is returned with **200 OK** instead of creating a duplicate — ' +
      'making the endpoint safe for sequential Power Automate retries.',
  })
  @ApiBody({ type: CreateProjectDto })
  @ApiEnvelopeResponse(ProjectResponseDto, {
    status: 201,
    description: '**201 Created** — new project was created.',
  })
  @ApiResponse({
    status: 200,
    description:
      '**200 OK** — `project_number` already exists; existing project returned (idempotent retry).',
  })
  @ApiResponse({
    status: 400,
    description: 'Validation error.',
    type: ErrorEnvelopeDto,
  })
  @ApiResponse({
    status: 401,
    description: 'Unauthorized.',
    type: ErrorEnvelopeDto,
  })
  @ApiResponse({
    status: 403,
    description: 'Forbidden — user lacks can_manage_dimension_members permission.',
    type: ErrorEnvelopeDto,
  })
  async create(
    @Body() dto: CreateProjectDto,
    @Req() req: { user: { id: number; username: string } },
    @Res({ passthrough: true }) res: Response,
  ) {
    // Permission check — mirrors Feng's can_manage_dimension_members system permission
    const canCreate = await this.fengPermissions.canManageDimensionMembers(req.user.id);
    if (!canCreate) {
      throw new ForbiddenException(
        'You do not have permission to create projects (can_manage_dimension_members required).',
      );
    }

    // Idempotency on the project number — if a project with this project number
    // already exists, return it (200) instead of creating a duplicate. Best-effort
    // for sequential retries (see the concurrency note on this method). Also fails
    // clearly here if the project-number custom property is not configured.
    const existingByNumber = await this.projects.findByProjectNumber(
      dto.project_number,
    );
    if (existingByNumber) {
      res.status(HttpStatus.OK);
      return existingByNumber;
    }

    // Not found → create. NestJS defaults @Post to 201 Created.
    return this.projects.create(
      {
        name: dto.name,
        projectNumber: dto.project_number,
        description: dto.description,
      },
      req.user.id,
    );
  }

  // ── GET /projects ──────────────────────────────────────────────────────────

  /** GET /projects — paginated list of active (non-trashed, non-archived) projects */
  @Get()
  @ApiOperation({
    summary: 'List projects',
    description:
      'Paginated list of active (non-trashed, non-archived) projects from `fo_projects`.',
  })
  @ApiPaginatedEnvelopeResponse(ProjectResponseDto, {
    description: 'Paginated array of projects.',
  })
  list(@Query() q: PaginationDto) {
    return this.projects.list({ page: q.page, per_page: q.per_page });
  }

  // ── GET /projects/:id ──────────────────────────────────────────────────────

  /** GET /projects/:id — where :id is the project number (custom property `project_id`) */
  @Get(':id')
  @ApiOperation({ summary: 'Get project by project number' })
  @ApiParam({
    name: 'id',
    description: 'Project number (custom property `project_id`)',
    type: String,
  })
  @ApiEnvelopeResponse(ProjectResponseDto, { description: 'Project detail.' })
  @ApiResponse({
    status: 404,
    description: 'Project not found or archived/trashed.',
    type: ErrorEnvelopeDto,
  })
  async findOne(@Param('id') projectNumber: string) {
    const project = await this.projects.findByProjectNumber(projectNumber);
    if (!project) {
      throw new NotFoundException(
        `Project with project number "${projectNumber}" not found`,
      );
    }
    return project;
  }
}
