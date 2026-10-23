/**
 * Convert a Prisma raw-query datetime value to an ISO 8601 string.
 *
 * Prisma's $queryRawUnsafe returns MySQL DATETIME columns as plain strings
 * in "YYYY-MM-DD HH:MM:SS" format rather than as JavaScript Date objects.
 * This helper handles both cases and treats all values as UTC (Feng stores
 * datetimes in UTC by convention).
 *
 * Returns null for nulls, undefined, empty strings, or unparseable values.
 */
export function toIso(value: Date | string | null | undefined): string | null {
  if (!value) return null;

  if (value instanceof Date) {
    return isNaN(value.getTime()) ? null : value.toISOString();
  }

  if (typeof value === 'string') {
    // MySQL format: "2024-01-15 10:30:00" → treat as UTC
    const normalized = value.includes('T') ? value : value.replace(' ', 'T') + 'Z';
    const d = new Date(normalized);
    return isNaN(d.getTime()) ? null : d.toISOString();
  }

  return null;
}
