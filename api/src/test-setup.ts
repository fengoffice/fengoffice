/**
 * Jest global setup — runs before every test file.
 *
 * Sets the minimum env vars required so modules that evaluate at import-time
 * (like common/table-prefix.ts and config/runtime-config.ts) don't throw.
 */
process.env.TABLE_PREFIX = 'fo_';
process.env.JWT_SECRET = 'test-secret-for-unit-tests-only';
process.env.DATABASE_URL = 'mysql://test:test@localhost:3306/feng_test';
