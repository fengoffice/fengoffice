-- Required for idempotent POST /projects to be safe under concurrent retries.
--
-- The API uses INSERT IGNORE INTO fo_contact_external_tokens to detect race
-- conditions: when two requests arrive with the same external_id, the second
-- INSERT IGNORE silently skips (affected=0) instead of creating a duplicate.
-- Without this constraint, INSERT IGNORE degrades to a regular INSERT and both
-- requests succeed, producing duplicate projects.
--
-- Run once per Feng database. Safe to re-run (IF NOT EXISTS guard).

ALTER TABLE fo_contact_external_tokens
  ADD CONSTRAINT uq_type_external_key UNIQUE (type, external_key);
