# System Architecture

## Layers
- Presentation Layer: Blade views, Tailwind UI, map and compare pages.
- Application Layer: controllers and form requests.
- Domain Layer: Eloquent models, query scopes, services.
- Data Layer: MySQL migrations, seeders, relationships.

## Request Flow
1. User hits route.
2. Middleware validates authentication + role.
3. Controller validates input and delegates to model/service.
4. Data is fetched/saved through Eloquent.
5. Response is rendered as Blade or JSON.

## Security
- CSRF protection in forms.
- Auth guards via middleware (`auth`, `verified`, role middleware).
- Validation on all writes.
- Authorization checks by role and resource ownership.

## Comparison Engine
- Input: selected boarding house IDs and optional reference coordinates.
- Processing: fetch house + rooms + amenities + review averages.
- Output: normalized comparison matrix for table rendering.

## Geotagging Module
- Coordinates stored in `boarding_houses`.
- Leaflet map renders pin(s) and supports quick visual comparison.
- Distance uses Haversine formula from a fixed point or user-selected coordinates.
