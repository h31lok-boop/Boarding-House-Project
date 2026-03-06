# Recommended Tech Stack

## Core
- Backend: `Laravel 12` (PHP 8.4)
- Frontend templating: `Blade + Tailwind CSS + Alpine.js`
- Database: `MySQL 8`
- Auth: `Laravel Breeze` + `spatie/laravel-permission`
- Storage: Laravel `public` disk for images
- Maps: `Leaflet.js` + OpenStreetMap tiles

## Why this stack
- Already aligned with the current repository.
- Fast to develop for capstone timeline.
- Clear MVC structure for beginner-friendly maintenance.

## Optional Upgrades (Phase 3+)
- API mode with `Laravel Sanctum`.
- Vue/React frontend.
- Redis queue + notifications.
- Geospatial indexing (MySQL spatial types).
