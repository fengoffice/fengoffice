/**
 * Swagger / OpenAPI shims for the global response envelope applied by
 * `ResponseInterceptor`. These classes + helper decorator make
 * `/api/docs` emit accurate schemas instead of bare `string` example values.
 *
 * Runtime note: the classes exist purely so Swagger's reflection can pick
 * them up. Nothing in the request pipeline instantiates them — the
 * interceptor builds the envelope dynamically.
 */
import { applyDecorators, Type } from '@nestjs/common';
import { ApiExtraModels, ApiProperty, ApiResponse, getSchemaPath } from '@nestjs/swagger';

/** Pagination metadata attached to list responses by `buildMeta()`. */
export class MetaDto {
  @ApiProperty({ example: 1, description: 'Current page (1-based)' })
  current_page!: number;

  @ApiProperty({ example: 25, description: 'Items per page' })
  per_page!: number;

  @ApiProperty({ example: 137, description: 'Total number of rows across all pages' })
  total!: number;

  @ApiProperty({ example: 6, description: 'Total number of pages' })
  total_pages!: number;
}

/**
 * Generic single-item envelope. The `data` field's real type is substituted
 * at documentation time via the `schema.allOf` trick in `ApiEnvelopeResponse`.
 */
export class EnvelopeDto<T> {
  @ApiProperty({ example: false, description: 'Always false on successful responses.' })
  error!: boolean;

  @ApiProperty({ example: 'Success' })
  message!: string;

  data!: T;
}

/** Paginated envelope — adds `meta`, makes `data` an array. */
export class PaginatedEnvelopeDto<T> {
  @ApiProperty({ example: false })
  error!: boolean;

  @ApiProperty({ example: 'Success' })
  message!: string;

  @ApiProperty({ type: MetaDto })
  meta!: MetaDto;

  data!: T[];
}

/** Error envelope — used by `HttpExceptionFilter`. */
export class ErrorEnvelopeDto {
  @ApiProperty({ example: true })
  error!: boolean;

  // `type: 'object'` + `nullable: true` avoids Swagger reflecting the TS
  // `null` literal type (which it treats as an unresolvable class ref and
  // crashes with "circular dependency ... property key: data").
  @ApiProperty({ type: 'object', nullable: true, example: null, additionalProperties: false })
  data!: null;

  @ApiProperty({ example: 'Validation failed' })
  message!: string;
}

// ─── Decorator helpers ──────────────────────────────────────────────────────

interface EnvelopeOptions {
  status?: number;
  description?: string;
}

/**
 * Document a single-item endpoint as returning `EnvelopeDto<T>`.
 *
 * ```ts
 * @ApiEnvelopeResponse(TaskResponseDto, { description: 'Task detail.' })
 * findOne(...) { ... }
 * ```
 */
export function ApiEnvelopeResponse<TModel extends Type<unknown>>(
  model: TModel,
  options: EnvelopeOptions = {},
) {
  return applyDecorators(
    ApiExtraModels(EnvelopeDto, model),
    ApiResponse({
      status: options.status ?? 200,
      description: options.description,
      schema: {
        allOf: [
          { $ref: getSchemaPath(EnvelopeDto) },
          {
            properties: {
              data: { $ref: getSchemaPath(model) },
            },
          },
        ],
      },
    }),
  );
}

/**
 * Document a list endpoint as returning `PaginatedEnvelopeDto<T>` —
 * `data` becomes `T[]` and `meta` carries pagination info.
 */
export function ApiPaginatedEnvelopeResponse<TModel extends Type<unknown>>(
  model: TModel,
  options: EnvelopeOptions = {},
) {
  return applyDecorators(
    ApiExtraModels(PaginatedEnvelopeDto, MetaDto, model),
    ApiResponse({
      status: options.status ?? 200,
      description: options.description,
      schema: {
        allOf: [
          { $ref: getSchemaPath(PaginatedEnvelopeDto) },
          {
            properties: {
              data: { type: 'array', items: { $ref: getSchemaPath(model) } },
            },
          },
        ],
      },
    }),
  );
}
