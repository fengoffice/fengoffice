import { getTablePrefix } from '../config/runtime-config';

/**
 * Feng Office table prefix resolved at runtime.
 *
 * The API bootstraps its database settings from Feng's `config/config.php`
 * before NestJS loads application modules, so raw SQL always targets the same
 * table prefix as the PHP app unless an explicit env override is provided.
 */
export const TABLE_PREFIX = getTablePrefix();
