# Phase 1: Folder Structure, Database Schema, Authentication and Roles

This document covers only Phase 1 for:

`Boarding House Management and Comparison System with Geotagging`

## 1) Recommended Folder Structure

Use this module-oriented structure inside Laravel for clean growth to Phases 2-4:

```text
app/
  Http/
    Controllers/
      SuperDuperAdmin/
        DashboardController.php
      User/
        (phase 3 map controllers)
    Middleware/
      EnsureSuperDuperAdmin.php
  Models/
    BoardingHouse.php
    Room.php
    Amenity.php
    User.php
database/
  seeders/
    Phase1AuthSeeder.php
  sql/
    phase1_schema.sql
resources/
  views/
    superduperadmin/
      dashboard.blade.php
routes/
  web.php
scripts/
  apply_phase1_schema.php
```

Why this structure:
- Keeps role-specific logic separated (`SuperDuperAdmin` namespace).
- Keeps schema patching scripts isolated from app logic.
- Keeps SQL references versioned in `database/sql/`.

## 2) Database Schema (Phase 1 Scope)

Reference schema file:
- `database/sql/phase1_schema.sql`

Applied idempotent patch script:
- `scripts/apply_phase1_schema.php`

Phase 1 schema focus:
- Extend `users.role` enum to include `superduperadmin` and `user`.
- Ensure `boarding_houses` has:
  - `price`
  - `available_rooms`
- Create new tables:
  - `boarding_house_images`
  - `approvals`
- Add supporting foreign keys:
  - `boarding_houses.owner_id -> users.id`
  - `boarding_house_images.boarding_house_id -> boarding_houses.id`
  - `approvals.boarding_house_id -> boarding_houses.id`
  - `approvals.reviewer_id -> users.id`

## 3) Authentication and Roles (Phase 1)

### Role setup

Seeder file:
- `database/seeders/Phase1AuthSeeder.php`

Creates/ensures roles:
- `superduperadmin`
- `admin`
- `manager`
- `owner`
- `tenant`
- `user`

Creates/updates SuperDuperAdmin account:
- Email: `superduperadmin@geoboard.com`
- Password: `SuperDuper123!`

### Middleware

File:
- `app/Http/Middleware/EnsureSuperDuperAdmin.php`

Purpose:
- Restricts route access to users with role `superduperadmin` (legacy role column or Spatie role).

Alias registration:
- `bootstrap/app.php` as `superduperadmin`.

### User role routing logic

Updated file:
- `app/Models/User.php`

Changes:
- Added `isSuperDuperAdmin()` helper.
- `isAdmin()` now treats `superduperadmin` as admin-capable.
- `dashboardRouteName()` now routes super users to:
  - `superduperadmin.dashboard`

### Route + controller + view

Route:
- `routes/web.php`
- Protected group:
  - `/superduperadmin/dashboard`

Controller:
- `app/Http/Controllers/SuperDuperAdmin/DashboardController.php`

View:
- `resources/views/superduperadmin/dashboard.blade.php`

## 4) Phase 1 Apply Commands

From project root:

```bash
php scripts/apply_phase1_schema.php
php artisan optimize:clear
php artisan db:seed --class=Phase1AuthSeeder
```

Optional checks:

```bash
php artisan route:list --name=superduperadmin.dashboard
php artisan db:table boarding_house_images
php artisan db:table approvals
```

## 5) Phase 1 Output Summary

Delivered in this phase:
- Recommended folder structure
- Database schema (SQL reference + applied patch)
- Secure authentication/role foundation for SuperDuperAdmin
- Dedicated SuperDuperAdmin protected dashboard route and UI

Next phase will implement:
- SuperDuperAdmin boarding house CRUD
- map-based geotagging form with Leaflet
