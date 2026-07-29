# Boarding House Project

Boarding House Project is a Laravel web application for managing boarding-house listings, tenant interactions, owner operations, and administrative review workflows.

## Core Modules

- Boarding-house listing and room management
- Owner, tenant, and admin dashboards
- Reservations, bookings, inquiries, and favorites
- Reviews, incidents, and payment-related flows
- Accreditation, validation, and application review
- Location-aware browsing and map-based discovery

## Tech Stack

- Laravel and PHP
- Blade templates for role-based pages
- Eloquent models for properties, rooms, reservations, tenants, reviews, and compliance records
- Database configured through `.env`
- Frontend assets managed through Node tooling

## Project Structure

- `app/Http/Controllers` contains admin, owner, tenant, listing, inquiry, and reservation logic
- `app/Models` includes `BoardingHouse`, `Room`, `Reservation`, `Review`, `TenantProfile`, and validation-related entities
- `resources/views` contains public, admin, and tenant-facing pages
- `routes/web.php` defines browsing, dashboard, and management routes

## Getting Started

1. Install PHP, Composer, Node.js, and a database server.
2. Run `composer install`.
3. Create a `.env` file from `.env.example`.
4. Configure the application and database values in `.env`.
5. Run `php artisan key:generate`.
6. Run `php artisan migrate`.
7. Run `php artisan db:seed`.
8. Run `npm install`.
9. Run `npm run dev`.
10. Start the application with `php artisan serve`.

## Demo Credentials

After seeding, use these local demo accounts:

- Admin: username `admin`, password `admin123`
- Tenant: username `tenant`, password `tenant123`

The admin account is stored with the canonical `admin` role, and the tenant account is stored with the canonical `user` role.

## Notes

- This project includes multiple user-facing flows, so role setup and seeded data may be useful during local testing.
- Check map-related controllers if external location or geocoding services are required.
