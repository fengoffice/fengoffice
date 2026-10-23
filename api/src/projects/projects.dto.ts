import { IsNotEmpty, IsOptional, IsString, MaxLength } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreateProjectDto {
  @ApiProperty({
    example: 'API Project - Test Local',
    description: 'Project name (required). Stored as the project title.',
  })
  @IsString()
  @IsNotEmpty()
  @MaxLength(255)
  name!: string;

  /**
   * Project number — stored as the project object's "Project ID" custom
   * property (code `project_id`). This is the external/business identifier the
   * integration uses to fetch the project back via GET /projects/{project_number}.
   */
  @ApiProperty({
    example: 'MS-0001',
    description:
      'Project number. Persisted as the project custom property with code ' +
      '`project_id` and used as the lookup key for GET /projects/{project_number}. Free text.',
  })
  @IsString()
  @IsNotEmpty()
  @MaxLength(255)
  project_number!: string;

  /**
   * Optional project description — stored as the project object's "Description"
   * custom property (code `description_special`), not the fo_projects column.
   */
  @ApiProperty({
    example: 'Created from the Swagger.',
    description:
      'Project description. Persisted as the project custom property with code ' +
      '`description_special`.',
    required: false,
    nullable: true,
  })
  @IsOptional()
  @IsString()
  description?: string;
}
