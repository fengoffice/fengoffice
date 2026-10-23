import { ApiPropertyOptional } from '@nestjs/swagger';
import { Type } from 'class-transformer';
import { IsInt, IsOptional, Max, Min } from 'class-validator';

export class PaginationDto {
  @ApiPropertyOptional({ description: 'Page number (1-based)', default: 1, minimum: 1 })
  @IsOptional()
  @Type(() => Number)
  @IsInt()
  @Min(1)
  page: number = 1;

  @ApiPropertyOptional({ description: 'Items per page (max 100)', default: 25, minimum: 1, maximum: 100 })
  @IsOptional()
  @Type(() => Number)
  @IsInt()
  @Min(1)
  @Max(100)
  per_page: number = 25;
}

export function buildMeta(total: number, page: number, perPage: number) {
  return {
    current_page: page,
    per_page: perPage,
    total,
    total_pages: Math.max(1, Math.ceil(total / perPage)),
  };
}
