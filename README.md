# Boarding House Project (GeoBoard)

Laravel 12 application for boarding house management, geotagged listings, and role-based operations (`superduperadmin`, `admin`, `owner`, `tenant`, `caretaker`, `osas`).

## Stack

- PHP `8.2+`
- Laravel `12`
- MySQL (default local setup)
- Vite + Tailwind CSS
- Pest/PHPUnit for tests

## Quick Start

1. Install dependencies:
   ```bash
   composer install
   npm install
   ```
2. Create env:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Configure DB values in `.env`.
4. Migrate + seed:
   ```bash
   php artisan migrate:fresh --seed
   ```
5. Build assets:
   ```bash
   npm run build
   ```
6. Start app:
   ```bash
   php artisan serve
   ```

## Development Commands

- Run backend tests:
  ```bash
  php artisan test
  ```
- Run code style checks:
  ```bash
  vendor/bin/pint --test
  ```
- Build frontend:
  ```bash
  npm run build
  ```

## Seeder Password Safety

Seeders read credentials from env:

- `SEED_DEFAULT_PASSWORD`
- `SEED_PASSWORD_SUPERDUPERADMIN`
- `SEED_PASSWORD_ADMIN`
- `SEED_PASSWORD_OWNER`
- `SEED_PASSWORD_MANAGER`
- `SEED_PASSWORD_TENANT`
- `SEED_PASSWORD_USER`

In production, seeders refuse to run with the fallback default password.

## Notes

- Feature tests disable Vite through the base test case (`withoutVite()`), so tests do not require pre-built frontend assets.
- CI runs `npm run build`, Pint, and tests.
- Use `php artisan migrate:fresh --seed` when syncing schema after pulling migration changes.
