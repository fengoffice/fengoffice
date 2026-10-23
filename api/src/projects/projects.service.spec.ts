import { Test, TestingModule } from '@nestjs/testing';
import { InternalServerErrorException } from '@nestjs/common';
import { ProjectsService } from './projects.service';
import { PrismaService } from '../prisma/prisma.service';
import { FengIdsResolver } from '../common/feng-ids.resolver';

// Resolved custom property ids used by the mock.
const PROJECT_NUMBER_CP_ID = 181; // code='project_id'
const DESCRIPTION_CP_ID = 52; //     code='description_special'

// ── Mock factories ────────────────────────────────────────────────────────────

function buildPrismaMock() {
  return {
    $queryRawUnsafe: jest.fn(),
    $executeRawUnsafe: jest.fn().mockResolvedValue(undefined),
    $transaction: jest.fn(),
  };
}

function buildFengIdsMock() {
  return {
    projectObjectTypeId: 24,
    projectDimensionId: 4,
    superAdminGroupId: 1,
    getProjectCustomPropertyIdByCode: jest.fn((code: string) =>
      Promise.resolve(code === 'project_id' ? PROJECT_NUMBER_CP_ID : DESCRIPTION_CP_ID),
    ),
  };
}

/** A raw project row as returned by the SELECT (includes the internal id + project_number). */
function makeRow(overrides: Record<string, unknown> = {}) {
  return {
    id: 4955379, // internal object_id — must NOT be exposed by toDto
    name: 'Test project',
    description: 'a description', // now sourced from the description_special CP value
    status: 5,
    customer_id: 0,
    manager_id: 1,
    start_date: null,
    end_date: null,
    created_on: null,
    updated_on: null,
    project_number: 'MS-0001',
    ...overrides,
  };
}

// ── Test suite ────────────────────────────────────────────────────────────────

describe('ProjectsService', () => {
  let service: ProjectsService;
  let prisma: ReturnType<typeof buildPrismaMock>;
  let fengIds: ReturnType<typeof buildFengIdsMock>;

  beforeEach(async () => {
    prisma = buildPrismaMock();
    fengIds = buildFengIdsMock();

    const module: TestingModule = await Test.createTestingModule({
      providers: [
        ProjectsService,
        { provide: PrismaService, useValue: prisma },
        { provide: FengIdsResolver, useValue: fengIds },
      ],
    }).compile();

    service = module.get(ProjectsService);
  });

  describe('findByProjectNumber', () => {
    it('resolves a project via a single active-aware query and hides internal fields', async () => {
      prisma.$queryRawUnsafe.mockResolvedValueOnce([makeRow()]);

      const result = await service.findByProjectNumber('MS-0001');

      // Both project-number and description custom properties are resolved.
      expect(fengIds.getProjectCustomPropertyIdByCode).toHaveBeenCalledWith('project_id');
      expect(fengIds.getProjectCustomPropertyIdByCode).toHaveBeenCalledWith('description_special');

      expect(result).not.toBeNull();
      expect(result!.project_number).toBe('MS-0001');
      expect(result!.description).toBe('a description');
      expect(result as Record<string, unknown>).not.toHaveProperty('id');
      expect(result as Record<string, unknown>).not.toHaveProperty('external_id');
      expect(result as Record<string, unknown>).not.toHaveProperty('customer_id');
      expect(result as Record<string, unknown>).not.toHaveProperty('manager_id');

      // A single statement matches the value AND applies the active filter, so an
      // active project is preferred over a trashed one sharing the same number.
      expect(prisma.$queryRawUnsafe).toHaveBeenCalledTimes(1);
      const [sql, value] = prisma.$queryRawUnsafe.mock.calls[0];
      expect(sql).toContain('cpv_num.value = ?');
      expect(sql).toContain('trashed_on');
      expect(sql).toContain('archived_on');
      expect(value).toBe('MS-0001');
    });

    it('returns null when no active project has that number', async () => {
      prisma.$queryRawUnsafe.mockResolvedValueOnce([]); // no active match

      const result = await service.findByProjectNumber('MISSING');

      expect(result).toBeNull();
      expect(prisma.$queryRawUnsafe).toHaveBeenCalledTimes(1);
    });

    it('coerces Buffer TEXT values to strings', async () => {
      prisma.$queryRawUnsafe.mockResolvedValueOnce([
        makeRow({
          project_number: Buffer.from('MS-0001', 'utf8'),
          description: Buffer.from('a description', 'utf8'),
        }),
      ]);

      const result = await service.findByProjectNumber('MS-0001');

      expect(result!.project_number).toBe('MS-0001');
      expect(result!.description).toBe('a description');
    });

    it('fails clearly when a required project custom property is not configured', async () => {
      fengIds.getProjectCustomPropertyIdByCode.mockRejectedValue(
        new InternalServerErrorException('not defined'),
      );

      await expect(service.findByProjectNumber('MS-0001')).rejects.toBeInstanceOf(
        InternalServerErrorException,
      );
    });
  });

  describe('create', () => {
    // Wire a transaction mock whose `tx` returns the given queued $queryRawUnsafe
    // results in order; captures every $executeRawUnsafe call for assertions.
    function wireTransaction(txExec: jest.Mock, txQuery: jest.Mock) {
      prisma.$transaction.mockImplementation((cb: (tx: unknown) => unknown) =>
        cb({ $executeRawUnsafe: txExec, $queryRawUnsafe: txQuery }),
      );
    }

    function cpvInserts(txExec: jest.Mock) {
      return txExec.mock.calls.filter(([sql]: [string]) =>
        /INSERT INTO\s+\S*custom_property_values/.test(sql),
      );
    }

    it('stores project_number and description as custom property values, not fo_projects.description', async () => {
      const txExec = jest.fn().mockResolvedValue(1);
      const txQuery = jest
        .fn()
        .mockResolvedValueOnce([{ id: 4955379 }]) // LAST_INSERT_ID (objects)
        .mockResolvedValueOnce([{ id: 14709 }]) //   LAST_INSERT_ID (member)
        .mockResolvedValueOnce([{ user_type: 1 }]) // creator user_type
        .mockResolvedValueOnce([]); //               dotRows → skip perms/cache
      wireTransaction(txExec, txQuery);
      // Final findOne() fetch (non-tx) returns the persisted row.
      prisma.$queryRawUnsafe.mockResolvedValueOnce([makeRow()]);

      const result = await service.create(
        { name: 'API Project - Test Local', projectNumber: 'MS-0001', description: 'hi' },
        147,
      );

      expect(result.project_number).toBe('MS-0001');

      const inserts = cpvInserts(txExec);
      expect(inserts).toHaveLength(2);
      // project number CP value
      expect(inserts[0][2]).toBe(PROJECT_NUMBER_CP_ID);
      expect(inserts[0][3]).toBe('MS-0001');
      // description CP value
      expect(inserts[1][2]).toBe(DESCRIPTION_CP_ID);
      expect(inserts[1][3]).toBe('hi');

      // fo_projects insert keeps description empty (legacy column not used):
      // the description text must not appear among its bound params.
      const projInsert = txExec.mock.calls.find(([sql]: [string]) =>
        /INSERT INTO\s+\S*projects\s*\(/.test(sql),
      );
      expect(projInsert).toBeDefined();
      expect(projInsert!.slice(1)).not.toContain('hi');
    });

    it('skips the description CP value when description is omitted', async () => {
      const txExec = jest.fn().mockResolvedValue(1);
      const txQuery = jest
        .fn()
        .mockResolvedValueOnce([{ id: 4955379 }])
        .mockResolvedValueOnce([{ id: 14709 }])
        .mockResolvedValueOnce([{ user_type: 1 }])
        .mockResolvedValueOnce([]);
      wireTransaction(txExec, txQuery);
      prisma.$queryRawUnsafe.mockResolvedValueOnce([makeRow({ description: null })]);

      await service.create({ name: 'X', projectNumber: 'MS-0002' }, 147);

      const inserts = cpvInserts(txExec);
      expect(inserts).toHaveLength(1);
      expect(inserts[0][2]).toBe(PROJECT_NUMBER_CP_ID);
      expect(inserts[0][3]).toBe('MS-0002');
    });

    it('fails clearly (before any inserts) when a custom property is not configured', async () => {
      fengIds.getProjectCustomPropertyIdByCode.mockRejectedValue(
        new InternalServerErrorException('not defined'),
      );

      await expect(
        service.create({ name: 'X', projectNumber: 'MS-0003' }, 147),
      ).rejects.toBeInstanceOf(InternalServerErrorException);
      expect(prisma.$transaction).not.toHaveBeenCalled();
    });
  });
});
