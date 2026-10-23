import { existsSync, readFileSync } from 'fs';
import { resolve, join } from 'path';
import { spawnSync } from 'child_process';

interface FengPhpConfig {
  DB_ADAPTER: string | null;
  DB_HOST: string | null;
  DB_USER: string | null;
  DB_PASS: string | null;
  DB_NAME: string | null;
  TABLE_PREFIX: string | null;
  ROOT_URL: string | null;
  DB_CHARSET: string | null;
}

const FENG_CONFIG_KEYS = [
  'DB_ADAPTER',
  'DB_HOST',
  'DB_USER',
  'DB_PASS',
  'DB_NAME',
  'TABLE_PREFIX',
  'ROOT_URL',
  'DB_CHARSET',
] as const;

let bootstrapped = false;

function resolveApiEnvPath() {
  return resolve(__dirname, '..', '..', '.env');
}

function resolveDefaultFengConfigPath() {
  return resolve(__dirname, '..', '..', '..', 'config', 'config.php');
}

export function resolveFengConfigPath() {
  return process.env.FENG_CONFIG_PATH?.trim() || resolveDefaultFengConfigPath();
}

function stripWrappingQuotes(value: string) {
  if (
    (value.startsWith('"') && value.endsWith('"')) ||
    (value.startsWith("'") && value.endsWith("'"))
  ) {
    return value.slice(1, -1);
  }

  return value;
}

function loadOptionalEnvFile() {
  const envPath = resolveApiEnvPath();
  if (!existsSync(envPath)) return;

  const envContents = readFileSync(envPath, 'utf8');
  for (const rawLine of envContents.split(/\r?\n/)) {
    const line = rawLine.trim();
    if (!line || line.startsWith('#')) continue;

    const match = /^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/.exec(line);
    if (!match) continue;

    const [, key, rawValue] = match;
    if (process.env[key] !== undefined) continue;

    process.env[key] = stripWrappingQuotes(rawValue.trim());
  }
}

function parsePhpScalar(rawValue: string) {
  const value = rawValue.trim();

  if (/^true$/i.test(value)) return 'true';
  if (/^false$/i.test(value)) return 'false';
  if (/^null$/i.test(value)) return null;

  return stripWrappingQuotes(value);
}

function parseConfigPhpFallback(configPath: string): FengPhpConfig {
  const file = readFileSync(configPath, 'utf8');
  const config = Object.fromEntries(
    FENG_CONFIG_KEYS.map((key) => [key, null]),
  ) as unknown as FengPhpConfig;

  for (const rawLine of file.split(/\r?\n/)) {
    const line = rawLine.trim();
    if (!line || line.startsWith('//') || line.startsWith('#')) continue;

    const match =
      /^define\s*\(\s*['"]([A-Z0-9_]+)['"]\s*,\s*(.+?)\s*\)\s*;/.exec(line);
    if (!match) continue;

    const [, key, rawValue] = match;
    if (!(FENG_CONFIG_KEYS as readonly string[]).includes(key)) continue;

    config[key as keyof FengPhpConfig] = parsePhpScalar(rawValue);
  }

  return config;
}

function loadConfigPhpViaPhpCli(configPath: string): FengPhpConfig {
  const phpBinary = process.env.PHP_BINARY?.trim() || 'php';
  const phpSnippet = `
    $path = $argv[1] ?? null;
    if (!$path || !is_file($path)) {
      fwrite(STDERR, "Config file not found\\n");
      exit(2);
    }
    $result = include $path;
    if ($result === false) {
      fwrite(STDERR, "Feng config returned false\\n");
      exit(3);
    }
    $keys = ['DB_ADAPTER','DB_HOST','DB_USER','DB_PASS','DB_NAME','TABLE_PREFIX','ROOT_URL','DB_CHARSET'];
    $out = [];
    foreach ($keys as $key) {
      $out[$key] = defined($key) ? constant($key) : null;
    }
    echo json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  `;

  const result = spawnSync(phpBinary, ['-r', phpSnippet, configPath], {
    encoding: 'utf8',
    windowsHide: true,
  });

  if (result.error) {
    throw new Error(result.error.message);
  }

  if (result.status !== 0) {
    const stderr = result.stderr?.trim() || 'Unknown PHP CLI error';
    throw new Error(stderr);
  }

  try {
    return JSON.parse(result.stdout) as FengPhpConfig;
  } catch {
    throw new Error('Unable to parse PHP CLI config output');
  }
}

function loadFengPhpConfig(configPath: string) {
  if (!existsSync(configPath)) {
    throw new Error(`Feng config not found at ${configPath}`);
  }

  try {
    return loadConfigPhpViaPhpCli(configPath);
  } catch {
    return parseConfigPhpFallback(configPath);
  }
}

function parseHostAndPort(dbHost: string) {
  const host = dbHost.trim();
  const ipv6Match = /^\[(.+)\]:(\d+)$/.exec(host);
  if (ipv6Match) {
    return { hostname: ipv6Match[1], port: ipv6Match[2] };
  }

  const hostPortMatch = /^([^:]+):(\d+)$/.exec(host);
  if (hostPortMatch) {
    return { hostname: hostPortMatch[1], port: hostPortMatch[2] };
  }

  return { hostname: host, port: '3306' };
}

function requireConfigValue(
  config: FengPhpConfig,
  key: keyof FengPhpConfig,
): string {
  const value = config[key];
  if (typeof value === 'string' && value.trim() !== '') {
    return value.trim();
  }

  throw new Error(`Missing ${key} in Feng config`);
}

function buildDatabaseUrl(config: FengPhpConfig) {
  const adapter = (config.DB_ADAPTER ?? 'mysqli').toLowerCase();
  if (adapter !== 'mysql' && adapter !== 'mysqli') {
    throw new Error(
      `Unsupported Feng DB adapter "${config.DB_ADAPTER ?? ''}" for Prisma`,
    );
  }

  const { hostname, port } = parseHostAndPort(requireConfigValue(config, 'DB_HOST'));
  const databaseUrl = new URL('mysql://placeholder');

  databaseUrl.username = requireConfigValue(config, 'DB_USER');
  databaseUrl.password = config.DB_PASS ?? '';
  databaseUrl.hostname = hostname;
  databaseUrl.port = port;
  databaseUrl.pathname = `/${requireConfigValue(config, 'DB_NAME')}`;

  return databaseUrl.toString();
}

/**
 * Mirrors the PHP custom_login plugin's verification_format_logo() logic.
 * Checks for custom_logo.svg then custom_logo.png in the Feng root directory.
 * Returns a public-facing URL if found, or falls back to the API's bundled logo.
 * The bundled fallback is chosen by appName:
 *   - contains "feng" (case-insensitive) → ./public/logo-feng.png
 *   - anything else (default)            → ./public/logo-evx.png
 */
function detectLogoUrl(fengRootDir: string, fengRootUrl: string, appName: string): string {
  const candidates = ['custom_logo.svg', 'custom_logo.png'];
  const base = fengRootUrl.replace(/\/$/, '');
  for (const filename of candidates) {
    if (existsSync(join(fengRootDir, filename))) {
      return base ? `${base}/${filename}` : `./${filename}`;
    }
  }
  // Fallback: pick bundled logo based on product name
  const bundledLogo = appName.toLowerCase().includes('feng')
    ? './public/logo-feng.png'
    : './public/logo-evx.png';
  return bundledLogo;
}

export function bootstrapRuntimeConfig() {
  if (bootstrapped) return;

  loadOptionalEnvFile();

  const configPath = resolveFengConfigPath();
  const fengConfig = loadFengPhpConfig(configPath);

  process.env.DATABASE_URL ??= buildDatabaseUrl(fengConfig);
  process.env.TABLE_PREFIX ??=
    requireConfigValue(fengConfig, 'TABLE_PREFIX');

  if (typeof fengConfig.ROOT_URL === 'string' && fengConfig.ROOT_URL.trim() !== '') {
    process.env.FENG_ROOT_URL ??= fengConfig.ROOT_URL.trim();
  }

  process.env.PORT ??= '3000';

  if (!process.env.JWT_SECRET?.trim()) {
    throw new Error(
      `JWT_SECRET is required. Set it in ${resolveApiEnvPath()} or as an environment variable.`,
    );
  }

  // ── Branding ─────────────────────────────────────────────────────────────
  // Mirrors evx_edition_hooks.php: override_product_name → 'EVX Software'.
  // Override via APP_NAME in .env for white-label or Feng Office deployments.
  process.env.APP_NAME ??= 'EVX Software';

  // Auto-detect logo from Feng root (same priority as custom_login plugin:
  // custom_logo.svg → custom_logo.png → bundled ./public/logo.png).
  // Override via APP_LOGO_URL in .env to point to any public image URL.
  if (!process.env.APP_LOGO_URL?.trim()) {
    // __dirname at runtime: api/dist/config/ → ../../../ = Feng root
    const fengRootDir = resolve(__dirname, '..', '..', '..');
    process.env.APP_LOGO_URL = detectLogoUrl(
      fengRootDir,
      process.env.FENG_ROOT_URL ?? '',
      process.env.APP_NAME ?? 'EVX Software',
    );
  }
  // ─────────────────────────────────────────────────────────────────────────

  bootstrapped = true;
}

export function getJwtSecret() {
  const secret = process.env.JWT_SECRET?.trim();
  if (!secret) {
    throw new Error('JWT_SECRET is missing');
  }

  return secret;
}

export function getApiPort() {
  return Number(process.env.PORT ?? '3000');
}

export function getTablePrefix() {
  const tablePrefix = process.env.TABLE_PREFIX?.trim();
  if (!tablePrefix) {
    throw new Error('TABLE_PREFIX is missing');
  }

  return tablePrefix;
}

/** Product / application name. Defaults to 'EVX Software'. Override with APP_NAME in .env. */
export function getAppName(): string {
  return process.env.APP_NAME?.trim() || 'EVX Software';
}

/**
 * Public-facing logo URL.
 * Auto-detected from Feng root (custom_logo.svg → custom_logo.png → ./public/logo.png).
 * Override with APP_LOGO_URL in .env.
 */
export function getAppLogoUrl(): string {
  if (process.env.APP_LOGO_URL?.trim()) return process.env.APP_LOGO_URL.trim();
  const appName = process.env.APP_NAME?.trim() ?? 'EVX Software';
  return appName.toLowerCase().includes('feng')
    ? './public/logo-feng.png'
    : './public/logo-evx.png';
}
