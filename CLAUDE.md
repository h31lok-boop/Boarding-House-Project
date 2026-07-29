# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

GeoBoard / BoardMatch is a Laravel 12 (PHP 8.2+) web app for boarding-house management with three role-based workspaces (admin, owner, tenant/user), location-aware browsing via Leaflet maps, and an AI-assisted roommate/boarding-house matchmaking system. Frontend is Blade + Alpine.js + Tailwind CSS, bundled by Vite.

## Commands

```bash
# Full dev environment (server + queue listener + vite, concurrently)
composer dev

# Individual pieces
php artisan serve            # app server
npm run dev                  # vite dev server (HMR)
npm run build                # production asset build

# Database
php artisan migrate
php artisan db:seed                                  # runs DatabaseSeeder (geo wiring, DSSC houses, admin)
php artisan db:seed --class=AdminInviteCodeSeeder    # generate admin invite codes

# Tests (Pest)
composer test                              # config:clear + full suite
php artisan test                           # full suite
php artisan test --filter=CompatibilityServiceTest        # single test class
php artisan test tests/Feature/MatchmakingDashboardTest.php   # single file

# Lint / format
./vendor/bin/pint            # Laravel Pint (PHP CS Fixer)
```

First-time setup: `composer setup` (install, copy `.env`, key:generate, migrate, npm install, npm run build).

## Environment

- DB is **MySQL** in `.env.example` (`DB_CONNECTION=mysql`), but tests run against in-memory **SQLite** (see `phpunit.xml`). Guard schema-dependent code — services like `CompatibilityService` call `Schema::hasTable(...)` before querying.
- Local dev host is `final-project.test` (Laravel Herd). Trusted hosts also include `*.ngrok-free.dev` and `localhost` (`bootstrap/app.php`).
- Key integrations (all optional, degrade gracefully when unconfigured): **Google OAuth** (Socialite login), **Google Maps** + OpenStreetMap routing URLs, **DeepSeek** LLM for match explanations.

## Architecture

### Roles and access control
Three roles: `admin` (super-admin), `owner` (property owner), `user` (tenant/student). Role resolution is **dual**: a legacy `users.role` string column AND Spatie `laravel-permission` roles — `User` model methods check both (case-insensitive), so always use the predicates rather than reading `role` directly:
- `isSuperAdmin()` / `isStrictOwner()` — strict, mutually exclusive; used to gate the admin vs owner workspaces.
- `isAdmin()` / `isOwner()` / `isManager()` are **loose aliases** that all return true for admin OR owner — do not use these to separate admin from owner.
- `dashboardRouteName()` maps a user to their dashboard; `/dashboard` redirects through it.

Route middleware aliases `admin`, `owner`, `user` (registered in `bootstrap/app.php`) map to the respective middleware classes and gate the three route groups in `routes/web.php`. Owners hitting an admin route are redirected to `owner.dashboard`, not 403'd.

Authorization gates and policies are registered in `AppServiceProvider::boot()`:
- Policies (`BoardingHousePolicy`, `RoomPolicy`, `ReservationPolicy`, `PaymentPolicy`) enforce per-record owner separation.
- Gates: `manage-users` (super-admin only), `manage-boarding-houses` (super-admin or strict owner), `access-map-features`.

### Route structure (`routes/web.php` + `routes/auth.php`)
All app routes are under `auth`+`verified`, then split into three prefixed/named groups: `admin.*` (`/admin`), `owner.*` (`/owner`), `user.*` (`/user`). Auth (Breeze-based) plus Google OAuth live in `routes/auth.php`. Note many user routes intentionally register the same handler under two names (e.g. `reservations` and `reservations.index`, or `browse` aliasing `boarding-houses.index`) — check for an existing alias before adding a route.

### Controllers
Organized by workspace: top-level shared controllers, `Controllers/Admin`, `Controllers/Owner`, `Controllers/User`, `Controllers/Auth`, `Controllers/Map`. `AdminOwnerController` is the large hub for the admin workspace (users, boarding houses, matchmaking, reviews, reports, notifications, settings). `Owner/OwnerController` mirrors it scoped to owned properties. `User/TenantAreaController` covers the tenant self-service area.

### Services (`app/Services`) — the domain core
- `CompatibilityService` — scores roommate compatibility between two tenants using weighted criteria from `config/matchmaking.php` (`weights`); returns overall score, per-criterion breakdown, highlights, conflicts.
- `BoardingHouseRecommendationService` / `TenantPreferenceRecommendationService` — rank boarding houses for a tenant using `boarding_house_weights` and `max_recommendation_distance_km` from `config/matchmaking.php`.
- `DeepSeekService` — optional LLM calls (via `Http`) that generate human-readable explanations of matches; returns a "not configured" result when no API key, so callers never hard-fail.
- `LocationService` — geocoding / distance helpers backing map features.
- `ReservationLifecycleService` — reservation state transitions.

Tuning matchmaking behavior means editing `config/matchmaking.php`, not the service code.

### Data model
Rich Eloquent domain (~40 models). Central: `User`, `BoardingHouse` (owned via `owner_id`), `Room`, `Reservation`, `Payment`/`PaymentReceipt`, `Review`, `Inquiry`. Matchmaking: `TenantMatchProfile`, `TenantPreference`/`UserPreference`, `RoommateMatchRequest`, `BoardingHouseMatch`, `Favorite`. Geo hierarchy: `Region` → `Province` → `CityMunicipality` → `Barangay` → `Location`. Validation/compliance: `Accreditation`, `ValidationRecord`/`Task`/`Finding`/`Evidence`, `Incident`.

### Frontend
Vite entry points: `resources/css/app.css` and `resources/js/app.js` (`vite.config.js`). Map behavior lives in standalone JS modules `resources/js/boarding-house-map.js` and `boarding-house-browse-map.js` (Leaflet). Blade views under `resources/views` are split by workspace (`admin/`, `user/`, `auth/`) with shared `components/` (including `components/admin/shell.blade.php`, `components/payments`, `components/sidebar`). UI is orange-themed.

## Tests
Pest with `tests/Feature` and `tests/Unit` suites. Feature tests are UI/behavior-focused (many `*UiTest.php` asserting rendered admin pages) plus service tests (`CompatibilityServiceTest`, `BoardingHouseRecommendationServiceTest`, `DeepSeekMatchExplanationTest`). Tests boot against SQLite `:memory:`.

## Demo credentials (after seeding)
- Admin: `admin@boardmatch.com` / `admin123` (role `admin`)
