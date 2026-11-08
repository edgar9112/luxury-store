# AGENTS.md

Luxury Store: Laravel 12 + Filament 5 admin + Livewire 4, Tailwind 4 via Vite, MySQL. Runs on Windows/Laragon; PHP 8.2.

## Commands

- Full dev stack: `composer dev` (artisan serve + queue listener + pail + Vite concurrently).
- Frontend only: `npm run dev` / `npm run build`.
- Tests: `php artisan test`; single test: `php artisan test --filter=ExampleTest` or `php artisan test tests/Feature/ExampleTest.php`.
- Format PHP: `php vendor/bin/pint` (no `pint.json`; defaults apply).
- New-machine setup: `powershell -ExecutionPolicy Bypass -File .\scripts\setup-windows.ps1` (creates `.env`, DB, migrations/seed, storage link, assets).
- No CI or pre-commit hooks: run tests and Pint manually.

`php vendor/bin/pint --test` currently fails on scaffold files (`app/Models/User.php`, `bootstrap/providers.php`, `config/auth.php`, `database/factories/UserFactory.php`) partly due to mixed CRLF endings. Don't mix a repo-wide reformat into a feature change.

## Architecture

- Admin panel is mounted at `/admin` by `app/Providers/Filament/AdminPanelProvider.php` (registered in `bootstrap/providers.php`).
- Filament auto-discovers `app/Filament/Resources`, `app/Filament/Pages`, and `app/Filament/Widgets`. These directories do not exist yet; create them for new admin features.
- Public site is plain Blade + Tailwind 4. Vite entries: `resources/css/app.css`, `resources/js/app.js`. No Inertia/SPA.
- The store domain is not built yet: only the default `User` model and Laravel `users`/`cache`/`jobs` migrations exist. Products/orders/etc. still need models and migrations.

## Environment gotchas

- `.env` uses MySQL database `luxury_store`; sessions, cache, and queue all use the database, so the DB must exist and be migrated for the app to function.
- `APP_URL` is `http://luxury-store.test` (Laragon vhost). `php artisan serve` instead serves `http://127.0.0.1:8000`.
- `phpunit.xml` leaves the SQLite overrides commented out, so DB-touching tests run against the real MySQL `luxury_store` database. Configure a test DB before adding `RefreshDatabase` tests.
- Locale and Faker default to Spanish (`es`, `es_ES`); write user-facing text in Spanish.
- Filament allows any authenticated user into `/admin` only when `APP_ENV=local`. In any other environment `User` must implement `Filament\Models\Contracts\FilamentUser` or the panel returns 403.
- Seeded admin from `DatabaseSeeder`: `test@example.com` / `password` (factory default).
- `public/storage` is not linked yet; run `php artisan storage:link` before relying on public uploads.
