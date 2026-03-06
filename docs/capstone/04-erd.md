# ERD Description

## Core Entities
- `users` belongs to one role label and can own/manage many boarding houses.
- `boarding_houses` has many rooms, inquiries, reservations, reviews, favorites, and optional location record.
- `rooms` belongs to one boarding house and can be referenced by reservations and tenant allocations.
- `amenities` is many-to-many with boarding houses through `boarding_house_amenities`.

## Transactional Entities
- `favorites` links user-to-boarding_house (many-to-many via unique pair).
- `inquiries` stores user messages to a boarding house.
- `reservations` stores booking requests and lifecycle status.
- `tenants` stores active/stale occupancy records.
- `payments` stores rent payment records for tenant occupancy.
- `reviews` stores ratings/comments by users.

## Relationship Cardinalities
- `users (1) -> (M) boarding_houses` via `owner_id`.
- `boarding_houses (1) -> (M) rooms`.
- `boarding_houses (1) -> (M) locations` (or one latest geotag record).
- `boarding_houses (M) <-> (M) amenities` via pivot.
- `users (M) <-> (M) boarding_houses` via favorites.
- `users (1) -> (M) inquiries` and `boarding_houses (1) -> (M) inquiries`.
- `users (1) -> (M) reservations` and `boarding_houses (1) -> (M) reservations`.
- `users (1) -> (M) reviews` and `boarding_houses (1) -> (M) reviews`.
- `tenants (1) -> (M) payments`.

## Distance / Map Logic
- `boarding_houses.latitude` and `boarding_houses.longitude` are the main source for map pins and comparison distance.
- `locations` can store extended geotag metadata/history.
