# Final Study Objectives — System Traceability

This document is the implementation baseline for BoardMatch. New work must preserve role separation, property ownership boundaries, verified payment handling, and the weighted compatibility algorithm.

## General objective

Develop an intelligent boarding-house management system using a weighted compatibility algorithm for students and boarding-house owners near Davao del Sur State College.

## Administrator module

| Objective | System implementation |
| --- | --- |
| Manage administrator, owner, and tenant accounts | Admin Users workspace and account create/update/archive actions |
| Manage roles and access permissions | Admin/owner/user middleware, policies, gates, and dual legacy/Spatie role resolution |
| Monitor and verify listings | Boarding Houses workspace, approval state, listing details, photos, map coordinates, rooms, and amenities |
| Monitor reservations, payments, and activity | Reservation queue, Payments workspace, receipt verification, notifications, and activity feeds |
| Reports for listings, room availability/occupancy, reservations, transactions, and reviews | Reports and Analytics workspace, CSV export, property performance tables, payment records, and review moderation |
| ML insights for demand, reservations, occupancy, and payment risks | Administrator **ML Insights** workspace at `admin/predictive-insights` |
| Monitor feedback and reviews | Reviews moderation workspace and report records |

## Boarding-house owner module

| Objective | System implementation |
| --- | --- |
| Register and log in | Owner registration and authenticated owner workspace |
| Manage boarding-house information | My Properties, professional photo gallery, amenities, geotagged map location, description, and contact data |
| Manage rooms and availability | Room inventory, rates, capacity, and room status workflows |
| Manage reservations | Owner-scoped reservation queue, walk-in reservation, approval/update workflows |
| Manage cash payment records | Owner-scoped cash payments, official receipts, and transaction records |
| View ML prediction results | Owner **ML Insights** workspace at `owner/predictive-insights`, restricted to owned properties |
| Manage feedback and reviews | Owner-scoped reviews and tenant feedback records |

## Student/tenant module

| Objective | System implementation |
| --- | --- |
| Register and log in | Tenant registration and authenticated tenant workspace |
| Manage student profile | Profile and account settings workspaces |
| Manage boarding-house and lifestyle preferences | Match Preferences with budget, location, amenities, and lifestyle fields |
| View compatibility scores and personalized recommendations | Weighted Compatibility Algorithm and recommendation explanation views |
| View ranked matches | Matchmaking workspace ranked by verified weighted scores |
| Reserve rooms | Listing detail and reservation lifecycle workflows |
| Process rent and reservation payments | Cash-only collection by the property owner or authorized front desk staff, followed by an official system receipt |
| View payment history, status, and receipts | Payments, Transactions, receipt modal/print/download, and webhook-verified statuses |
| View ML demand, reservation, occupancy, and payment-risk insights | Tenant **ML Insights** workspace at `user/predictive-insights`; payment risk is scoped to the signed-in tenant |

## Algorithms and decision boundaries

- The weighted compatibility algorithm is authoritative for compatibility scores and ranking.
- OpenAI may explain a verified score or answer system-usage questions, but it must not modify scores or invent live availability, payment, or reservation facts.
- Predictive insights use ordinary least-squares regression over role-scoped monthly observations. They are decision support, not transaction truth.
- Owner/admin-recorded cash payments remain the source of truth for settled balances and official receipts.

## Evaluation baseline

The system must be evaluated against:

1. **Functional suitability:** objective-to-route traceability, authorization tests, and end-to-end role workflows.
2. **Performance efficiency:** bounded queries, pagination, throttling, production asset builds, and response-time/load evaluation.
3. **Usability:** responsive layouts, consistent headers, centered accessible modals, readable dark mode, and role-appropriate navigation.
4. **Reliability:** validation, transactions, idempotent payment settlement, webhook verification, graceful optional-integration failures, and automated tests.
5. **Portability:** environment-based configuration, Laravel-supported databases, Vite production assets, no client-side secrets, and deployable PHP/MySQL requirements.
