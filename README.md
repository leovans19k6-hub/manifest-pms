# Manifest Stay PMS

Manifest Stay PMS is a PHP/Laravel property management system under active development by Manifest Global.

## Current status

**Development Baseline — `0.1.0-dev`**

Verified implemented scope:

- Laravel 12 application baseline.
- MySQL/MariaDB configuration support.
- Vite frontend build.
- Livewire dependency installed.
- Dashboard UI prototype with static/demo presentation data.
- Product metadata configuration.
- Baseline smoke/config tests.

Not implemented yet: DDD/Modular Monolith architecture, Authentication flow, Organizations, RBAC, Audit, Property, Inventory, Reservations, Guests, Finance, Housekeeping, Reporting, and production-ready Developer Preview.

## Requirements

- PHP 8.2+
- Composer 2.x
- MySQL or MariaDB
- Node.js 20+
- npm
- XAMPP is supported for local Windows development.

## XAMPP setup

Place the project at `C:\xampp\htdocs\manifest-pms`, start MySQL, and open Git Bash/PowerShell in the project directory.

1. Install PHP dependencies: `composer install`
2. Create local environment file: `cp .env.example .env` (or `copy .env.example .env` in CMD)
3. Generate key: `php artisan key:generate`
4. Configure `DB_*` values in `.env`.
5. Create the configured database in MySQL/MariaDB.
6. Run migrations: `php artisan migrate`
7. Create storage link: `php artisan storage:link`
8. Install frontend dependencies: `npm ci`
9. Build assets: `npm run build`
10. Run locally: `php artisan serve`

## Development commands

- Frontend dev server: `npm run dev`
- Tests: `php artisan test`
- Code style verification: `./vendor/bin/pint --test`
- Fix code style: `./vendor/bin/pint`
- Clear Laravel caches: `php artisan optimize:clear`

## Product configuration

`config/manifest.php` contains the company, product, and development version metadata. Application timezone and locale are configured through `.env` values documented in `.env.example`.

## Architecture status

The repository is intentionally a clean Laravel baseline. DDD/Modular Monolith and Foundation modules will be introduced only through verified implementation stories with local test/build evidence.

## Repository workflow

1. Pull latest `main`.
2. Create/implement one verified story.
3. Run migrations, tests, Pint, and Vite build locally.
4. Review `git diff` and `git status`.
5. Commit and push only verified source changes.
