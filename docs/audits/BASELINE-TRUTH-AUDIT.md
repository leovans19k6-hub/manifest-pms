# Baseline Truth Audit

## Verified scope

This repository is a Laravel 12 development baseline for Manifest Stay PMS. It contains the default Laravel application structure, Livewire dependency, a dashboard UI prototype, product configuration, and baseline smoke tests.

## Laravel scaffold

- Default `User` model and user/cache/jobs migrations.
- Default application provider, bootstrap, configuration, console route, frontend entrypoints, and test harness.

## Non-default code

- Livewire dependency in `composer.json`.
- `/dashboard` route and `resources/views/dashboard.blade.php` UI prototype.
- `config/manifest.php` product metadata.

## Removed misleading or invalid artifacts

- `DemoPreviewSeeder.php` was removed because it referenced non-existent `organizations` and `organization_memberships` tables and fields not present on the baseline `users` table.
- Nested `_skeleton_backup/` project copy was removed from the delivery artifact.
- Runtime/generated artifacts (`public/build`, SQLite database, PHPUnit cache, Laravel log) were removed from the delivery artifact.

## Migration inventory

- `0001_01_01_000000_create_users_table.php`
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`

No Organization, RBAC, Audit, Property, Inventory, Reservation, Finance, or Housekeeping database schema is implemented.

## Route inventory

- `GET /` renders the Laravel welcome page.
- `GET /dashboard` renders a UI prototype only; it is not verified business functionality.

## Current technical debt

- Authentication is not implemented for the dashboard.
- The dashboard contains prototype/static data and is not connected to PMS domain data.
- DDD/Modular Monolith structure is not implemented.
- Foundation modules (Organization, RBAC, Audit) are not implemented.
- Product-specific domain tests do not yet exist beyond baseline smoke/config tests.
