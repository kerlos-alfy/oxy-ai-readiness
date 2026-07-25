# ADR-004: Database Naming Conventions

**Status:** Accepted
**Date:** 2026-07-24
**Resolves:** `.project/03-Conflicts.md` §6

## Context

`docs/25-Database-Schema.md` specifies an `oxy_settings` table with a column literally named `key`. `KEY` is a reserved word in MySQL/MariaDB DDL, requiring backtick-escaping on every reference (`` `key` ``) or it silently breaks queries/migrations. No other naming convention is stated explicitly anywhere in the docs, so this is also an opportunity to formalize one before more tables are added in later phases.

## Decision

1. **`oxy_settings.key` → `oxy_settings.setting_key`, and `oxy_settings.value` → `oxy_settings.setting_value`**, following the same descriptive-prefix pattern WordPress itself uses (`wp_options.option_name` / `option_value`). Corrected column list: `id, setting_key, setting_value, type, autoload, created_at, updated_at`, with a `UNIQUE KEY setting_key (setting_key)`.
2. **Formal naming convention (applies to every current and future `oxy_*` table):**
   - Table prefix: `{$wpdb->prefix}oxy_{domain}` (e.g. `oxy_audits`, `oxy_score_history`), all snake_case, always plural for collection tables.
   - Every table has a surrogate primary key `id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT`.
   - Foreign key columns are named `{referenced_table_singular}_id` (e.g. `audit_id`, `score_id`) — already the convention in use across `docs/25-Database-Schema.md`; formalized here rather than changed.
   - Timestamp columns are `created_at` / `updated_at`, `DATETIME`, stored in UTC — already the de facto convention; formalized here.
   - **No column may use a MySQL/MariaDB reserved word** (`key`, `value`, `order`, `group`, `condition`, `status` is borderline-safe but should stay quoted-free by using it only as a plain identifier, `read`, `check`, `range`, `desc`, etc.). When a reserved word is the only natural name, prefix it with the entity it belongs to (as done for `setting_key`/`setting_value` above).
   - Cloud-sync fields (`docs/25-Database-Schema.md`'s "CLOUD SYNC" section: `uuid`, `version`, `checksum`, `updated_at`, `sync_status`) are optional per-table additions, not required on every table — only on tables explicitly flagged for future cloud sync in that doc.

## Consequences

- `docs/25-Database-Schema.md`'s `oxy_settings` table definition is corrected in place.
- A new "NAMING CONVENTIONS" subsection is added to `docs/25-Database-Schema.md` so future tables (added in Phase 2 onward) don't reintroduce the same class of bug.
- No other existing table in `docs/25-Database-Schema.md` uses a reserved word; no further schema changes required.
