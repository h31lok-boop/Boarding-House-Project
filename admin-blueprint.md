# BoardMatch Admin Portal — Complete Blueprint

## 1. Admin Dashboard

**Purpose:** Central command center giving admins a real-time snapshot of the entire platform.

### Layout

```
┌─────────────────────────────────────────────────────────┐
│  Header: "Admin Dashboard"  [Search] [Notif] [Profile]  │
├──────────┬──────────┬──────────┬──────────┬─────────────┤
│ KPI Row  │          │          │          │             │
│ Total    │ Active   │ Monthly  │ Pending  │ Unverified  │
│ Users    │ Owners   │ Revenue  │ Reviews  │ Receipts    │
├──────────┴──────────┴──────────┴──────────┴─────────────┤
│ Alerts & Needs Attention banner                         │
├───────────────────────────┬─────────────────────────────┤
│ Recent Registrations      │ Latest Inquiries            │
│ (table: name, email,      │ (table: tenant, property,   │
│  role, date, status)      │  date, status, action)      │
├───────────────────────────┼─────────────────────────────┤
│ Platform Stats            │ Quick Actions               │
│ • Total properties        │ • Add Owner                 │
│ • Total rooms             │ • Add Property              │
│ • Occupancy rate          │ • Review Receipts           │
│ • Revenue this month      │ • System Settings           │
│ • New signups (7d)        │                             │
└───────────────────────────┴─────────────────────────────┘
```

### KPI Cards
| Card | Source | Notes |
|------|--------|-------|
| Total Users | `users` count | All registered users |
| Active Owners | `users` where role=owner | Owners with active status |
| Active Tenants | `tenants` where status=active | Currently housed |
| Total Properties | `boarding_houses` count | Published + pending |
| Monthly Revenue | `payments` where status=paid, this month | SUM(amount) |
| Pending Reviews | `reviews` where status=pending | Moderation queue |
| Unverified Receipts | `payment_receipts` where status=pending_review | New uploads |
| Open Inquiries | `inquiries` where status=new/pending | Needs response |

### Alerts Section
- Properties pending approval
- Payment receipts awaiting verification
- Reports of inappropriate content
- System warnings (failed jobs, storage)
- New owner verification requests

### Recent Activities
- New user registrations
- Properties published/approved
- Large payments processed
- Reviews reported
- Account suspensions

### Quick Actions
- Create Admin User
- Add Owner Account
- Approve Properties
- Verify Receipts
- System Announcement
- Export Report

---

## 2. Sidebar Navigation

### Structure

```
BOARDMATCH                        [Brand logo]
─────────────────────────────────────────────
MAIN
  ┌─ Dashboard                    [grid icon]
  ├─ My Properties                [building icon]  → Manage properties, rooms, tenants
  ├─ Owners                       [users icon]     → All property owners
  ├─ Tenants                      [people icon]    → All tenants across platform
  ├─ Reservations                 [calendar icon]  → All booking requests
  ├─ Payments                     [wallet icon]    → Payment records & receipts
  ├─ Transactions                 [receipt icon]   → Transaction log
  └─ Messages                     [chat icon]      → Inquiries & conversations

ACCOUNT
  ├─ Reports                      [chart icon]     → Analytics & exports
  └─ Settings                     [gear icon]      → System configuration
```

### Item Details

| Item | Route | Purpose |
|------|-------|---------|
| Dashboard | `admin.dashboard` | Platform overview, KPIs, alerts |
| My Properties | `admin.boarding-houses` | Manage all listed properties |
| Owners | `admin.owners` | Owner accounts, verification |
| Tenants | `admin.tenants` | Tenant directory, status |
| Reservations | `admin.reservations` | Booking workflow |
| Payments | `admin.payments` | Revenue, receipts |
| Transactions | `admin.transactions` | Payment journal |
| Messages | `admin.messages` | Inquiries, conversations |
| Reports | `admin.reports` | Analytics, exports |
| Settings | `admin.settings` | System configuration |

---

## 3. Boarding House Management

**Purpose:** Full CRUD for all properties on the platform.

### Page: Property Listing
**Layout:**
```
[Header: "My Properties" + "Add Property" button]
[Search bar | Status filter | Owner filter | Date filter]
[KPI: Total | Published | Pending | Rejected]

Table:
┌──────┬──────────┬───────────┬────────┬────────┬──────────┬──────────┐
│  #   │ Property │   Owner   │ Rooms  │ Status │  Revenue │ Actions  │
├──────┼──────────┼───────────┼────────┼────────┼──────────┼──────────┤
│  1   │ Name     │ Owner     │ 12     │ Active │ PHP 45K  │ [View]   │
│      │ Address  │ email     │ 10 occ │        │          │ [Edit]   │
│      │          │           │        │        │          │ [Toggle] │
└──────┴──────────┴───────────┴────────┴────────┴──────────┴──────────┘
```

**Filters:** Search (name/address), Status (all/published/pending/rejected), Owner, Date created

**Columns:** ID, Property (name + address), Owner (name + email), Rooms (total/occupied), Status badge, Monthly Revenue, Actions

**Available Actions:**
- View full details
- Edit property (name, description, address, amenities)
- Toggle published/unpublished
- Approve/reject pending properties
- Delete (with confirmation)
- View as owner (impersonate view)

### Page: Property Details
**Layout:**
```
[Header: Property Name + Status badge + Back button]

┌─────────────────────────────────────────────────────────┐
│  Property Info                                          │
│  • Name, description, address, city                     │
│  • Owner name, email, phone                             │
│  • Created, last updated                                │
├─────────────────────────────────────────────────────────┤
│  Gallery / Photos                                       │
│  [Grid of uploaded images]  [Upload new]                │
├─────────────────────────────────────────────────────────┤
│  Rooms                                                  │
│  Table: Room # | Type | Capacity | Rate | Status        │
│  Actions: Edit, Toggle, Delete                          │
├─────────────────────────────────────────────────────────┤
│  Current Tenants                                        │
│  Table: Name | Room | Move-in | Status | Actions        │
├─────────────────────────────────────────────────────────┤
│  Recent Reservations                                    │
│  Table: Tenant | Room | Dates | Status | Actions        │
├─────────────────────────────────────────────────────────┤
│  Revenue Summary                                        │
│  • This month, last month, total                        │
│  • Payment records table                                │
└─────────────────────────────────────────────────────────┘
```

### Workflow
1. Owner submits property → status: `pending`
2. Admin reviews property details, photos, amenities
3. Admin approves → status: `published` (owner can now manage)
4. Admin rejects → owner gets notification with reason
5. Admin can unpublish at any time

### Database Entities
- `boarding_houses` — id, owner_id(users), name, description, address, city_id, latitude, longitude, status, featured, created_at
- `boarding_house_images` — id, boarding_house_id, url, is_primary, sort_order
- `boarding_house_photos` — id, boarding_house_id, url, caption
- `rooms` — id, boarding_house_id, room_number, type, capacity, rate, status

---

## 4. Owner Management

**Purpose:** Manage all property owner accounts.

### Page: Owner List
**Layout:**
```
[Header: "Owners" + "Add Owner" button]
[Search | Status filter | Verification filter]

Table:
┌──────┬──────────┬───────────┬──────────┬──────────────┬──────────┐
│  #   │ Owner    │ Properties│ Revenue  │ Status       │ Actions  │
├──────┼──────────┼───────────┼──────────┼──────────────┼──────────┤
│  1   │ Name     │ 5 props   │ PHP 120K │ Active       │ [View]   │
│      │ email    │ 3 pending │          │ Verified     │ [Edit]   │
│      │ phone    │           │          │              │ [Ban]    │
└──────┴──────────┴───────────┴──────────┴──────────────┴──────────┘
```

**Columns:** ID, Owner (name + email + phone), Properties (total/pending), Revenue (total), Status (active/inactive/banned), Verification (verified/unverified), Joined date, Actions

**Actions:**
- View profile
- Edit account
- Verify owner (approve identity)
- Suspend/unsuspend
- Ban (with reason)
- Delete (with confirmation)
- Impersonate (debug)

### Page: Owner Profile
**Layout:**
```
┌──────────────────────┬──────────────────────────────────┐
│  Profile Card        │  Verification Status             │
│  • Avatar, name      │  [Verify Identity] [Documents]   │
│  • Email, phone      │  • ID submitted: yes/no          │
│  • Role, since       │  • Verified at: date             │
│  • Status badge      │                                   │
├──────────────────────┴──────────────────────────────────┤
│  Property Portfolio                                     │
│  (Table of properties owned, same as Properties table)  │
├─────────────────────────────────────────────────────────┤
│  Revenue Overview                                       │
│  (Total, this month, pending, chart)                    │
├─────────────────────────────────────────────────────────┤
│  Activity Log                                           │
│  • Login history                                        │
│  • Property changes                                     │
│  • Reservation activity                                 │
│  • Payment records                                      │
└─────────────────────────────────────────────────────────┘
```

### Verification Workflow
1. Owner registers → status: `active`, verified: `false`
2. Owner submits verification documents
3. Admin reviews documents from owner profile
4. Admin marks as verified → verified: `true`, verified_at: now
5. Unverified owners have feature restrictions (e.g., max 1 property)

### Database Entities
- `users` (role: owner) — id, name, email, password, role, status, email_verified_at
- `owner_verifications` — id, user_id, document_type, document_url, status, verified_at, verified_by, notes
- `owner_documents` — id, user_id, type, file_path, uploaded_at

---

## 5. Tenant Management

**Purpose:** View and manage all tenants across the platform.

### Page: Tenant List
**Layout:**
```
[Header: "Tenants" + "Add Tenant" button]
[Search | Status filter | Property filter]

Table:
┌──────┬──────────┬───────────┬──────────┬────────┬──────────┐
│  #   │ Tenant   │ Property  │ Room     │ Status │ Actions  │
├──────┼──────────┼───────────┼──────────┼────────┼──────────┤
│  1   │ Name     │ BH Name   │ Room 101 │ Active │ [View]   │
│      │ email    │ Address   │ Move-in  │        │ [Edit]   │
│      │ phone    │           │ Oct 2025 │        │          │
└──────┴──────────┴───────────┴──────────┴────────┴──────────┘
```

**Filters:** Search (name/email), Status (active/inactive/former), Property, Move-in date range

**Columns:** ID, Tenant (name + email + phone), Property, Room (number + move-in date), Status badge (Active/Inactive/Former), Payment status, Actions

**Actions:**
- View profile
- Edit details
- Change status
- View reservation history
- View payment history
- Send message
- Delete

### Page: Tenant Profile
**Layout:**
```
┌──────────────────────┬──────────────────────────────────┐
│  Profile Card        │  Current Stay                    │
│  • Avatar, name      │  Property: Name                  │
│  • Email, phone      │  Room: #101                      │
│  • Member since      │  Move-in: Oct 1, 2025            │
│  • Status badge      │  Lease ends: Sep 30, 2026        │
├──────────────────────┴──────────────────────────────────┤
│  Reservation History                                    │
│  Table: ID | Property | Room | Dates | Amount | Status  │
├─────────────────────────────────────────────────────────┤
│  Payment History                                        │
│  Table: ID | Period | Amount | Method | Date | Status   │
├─────────────────────────────────────────────────────────┤
│  Inquiries / Support Tickets                            │
│  Table: Subject | Property | Date | Status | Actions    │
└─────────────────────────────────────────────────────────┘
```

### Database Entities
- `tenants` — id, user_id, boarding_house_id, room_id, status, move_in_date, lease_end_date
- `users` (role: tenant/user) — id, name, email, password, role, status

---

## 6. Reservation Management

**Purpose:** Full reservation lifecycle across all properties.

### Page: Reservation Dashboard
**Layout:**
```
[Header: "Reservations"]
[KPI: Total | Pending | Confirmed | Completed | Cancelled]
[Search | Status filter | Property filter | Date range]

Table:
┌──────┬──────────┬───────────┬──────────┬────────┬──────────┬──────────┐
│  #   │ Tenant   │ Property  │ Dates    │ Amount │ Status   │ Actions  │
├──────┼──────────┼───────────┼──────────┼────────┼──────────┼──────────┤
│ R001 │ Name     │ BH Name   │ Oct 1-30 │ PHP 5K │ Pending  │ [Approve]│
│      │ email    │ Room 101  │          │        │          │ [Reject] │
└──────┴──────────┴───────────┴──────────┴────────┴──────────┴──────────┘
```

### Statuses & Workflow
```
Pending → Approved → Confirmed → Checked-In → Completed
   ↓         ↓
Rejected  Cancelled
```

| Status | Meaning | Admin Action |
|--------|---------|-------------|
| Pending | Awaiting owner/admin approval | Approve or Reject |
| Approved | Owner approved, pending tenant confirmation | — (waiting on tenant) |
| Confirmed | Tenant confirmed, move-in scheduled | Can Cancel |
| Checked-In | Tenant moved in | Mark Completed |
| Completed | Stay finished, archived | View only |
| Rejected | Owner/admin rejected | — (archived) |
| Cancelled | Cancelled by either party | — (archived) |

### Actions per Status
- **Pending:** Approve, Reject, View
- **Approved:** Cancel, View
- **Confirmed:** Cancel, View
- **Checked-In:** Mark Completed, View
- **Completed/Cancelled/Rejected:** View only

### Database Entities
- `reservations` — id, user_id(tenant), boarding_house_id, room_id, status, check_in_date, check_out_date, total_amount, owner_approved_at, created_at

---

## 7. Payment Management

**Purpose:** Track all payments and verify receipts.

### Page: Payment Dashboard
**Layout:**
```
[Header: "Payments" + "Record Payment" button]
[KPI: Revenue(month) | Paid | Pending | Overdue]
[Search | Status filter | Property filter | Date range]

Table:
┌──────┬──────────┬───────────┬──────────┬──────────┬──────────┬──────────┐
│  #   │ Tenant   │ Property  │ Amount   │ Due Date │ Status   │ Actions  │
├──────┼──────────┼───────────┼──────────┼──────────┼──────────┼──────────┤
│  1   │ Name     │ BH Name   │ PHP 5K   │ Oct 1    │ Paid     │ [View]   │
│      │ email    │           │          │          │          │ [Edit]   │
└──────┴──────────┴───────────┴──────────┴──────────┴──────────┴──────────┘
```

### Page: Receipt Verification
**Layout:**
```
[Header: "Receipt Verification"]
[KPI: Pending | Approved | Rejected | Total]

Table:
┌──────┬──────────┬───────────┬──────────┬──────────┬──────────┬──────────┐
│  #   │ Tenant   │ Amount    │ Uploaded │ Reference│ Status   │ Actions  │
├──────┼──────────┼───────────┼──────────┼──────────┼──────────┼──────────┤
│  1   │ Name     │ PHP 5K    │ Oct 1    │ REF-123  │ Pending  │ [View]   │
│      │          │           │          │          │          │ [Approve]│
│      │          │           │          │          │          │ [Reject] │
└──────┴──────────┴───────────┴──────────┴──────────┴──────────┴──────────┘
```

### Payment Statuses
- **Paid** — payment completed
- **Pending** — awaiting payment
- **Unpaid** — not yet paid
- **Overdue** — past due date
- **Refunded** — money returned
- **Failed** — payment failed

### Database Entities
- `payments` — id, tenant_id, boarding_house_id, amount, due_date, paid_at, status, reference_no, payment_method, payment_type, notes
- `payment_receipts` — id, payment_id, tenant_id, file_path, status(pending_review/approved/rejected), reviewed_by, reviewed_at, rejection_reason

---

## 8. Transaction Management

**Purpose:** Complete audit log of all financial transactions.

### Page: Transactions
**Layout:**
```
[Header: "Transactions" + "Export" button]
[Search | Status filter | Date range | Payment method]

Table:
┌──────┬──────────┬───────────┬──────────┬──────────┬──────────┬──────────┐
│  ID  │ Tenant   │ Property  │ Type     │ Amount   │ Date     │ Status   │
├──────┼──────────┼───────────┼──────────┼──────────┼──────────┼──────────┤
│  #1  │ Name     │ BH Name   │ Rent     │ PHP 5K   │ Oct 1    │ Paid     │
│      │          │           │ Monthly  │          │          │          │
└──────┴──────────┴───────────┴──────────┴──────────┴──────────┴──────────┘
```

**Columns:** Transaction ID, Tenant, Property, Payment Type, Amount, Date, Status, Actions (View, Receipt)

**Payment Types:** Rent, Deposit, Utility, Fine, Refund, Other

**Export Options:** CSV, Excel, PDF (filtered by date range)

### Database Entities
Same as `payments` table (transactions are a filtered/log view of payments).

---

## 9. Messages & Inquiries

**Purpose:** Central inbox for all tenant/owner communications.

### Page: Inquiries
**Layout:**
```
[Header: "Inquiries"]
[KPI: Total | New | Replied | Closed]
[Search | Status filter | Property filter | Date range]

Table:
┌──────┬──────────┬───────────┬──────────────────┬──────────┬──────────┬──────────┐
│  #   │ Tenant   │ Property  │ Message Preview  │ Status   │ Date     │ Actions  │
├──────┼──────────┼───────────┼──────────────────┼──────────┼──────────┼──────────┤
│  1   │ Name     │ BH Name   │ "Hi, I'm inter…" │ New      │ Oct 1    │ [Reply]  │
│      │ email    │           │                  │          │          │ [View]   │
└──────┴──────────┴───────────┴──────────────────┴──────────┴──────────┴──────────┘
```

### Inbox Structure
- **All Messages** — every inquiry
- **Unread** — new/pending status
- **Replied** — waiting for tenant response
- **Closed** — resolved/archived

### Inquiry Statuses
- **New** — just submitted, needs response
- **Pending** — acknowledged, working on it
- **Replied** — admin responded, awaiting tenant
- **Closed** — resolved

### Workflow
1. Tenant submits inquiry → status: `new`
2. Admin views and replies → status: `replied`
3. Tenant responds → status: `pending` again
4. Admin marks as resolved → status: `closed`

### Database Entities
- `inquiries` — id, user_id(tenant), boarding_house_id, message, reply, status, replied_at, created_at

---

## 10. Reports & Analytics

**Purpose:** Data-driven insights for platform decisions.

### Report Modules

| Report | Content | Format |
|--------|---------|--------|
| Property Report | List of all properties with stats, status, revenue | Table + Export |
| Owner Report | All owners, properties count, revenue, verification | Table + Export |
| Tenant Report | All tenants, active stay, payment history | Table + Export |
| Financial Report | Revenue by month, by property, by owner | Chart + Table |
| Occupancy Report | Occupancy rates by property, by month | Chart |
| Reservation Report | Reservation volume, conversion rate, avg stay | Chart + Table |

### Layout Template
```
[Header: "Reports & Analytics"]
[Tab: Property | Owner | Tenant | Financial | Occupancy | Reservation]

[Summary KPI row for selected report]
[Chart section (if applicable)]
[Data table with export]
```

### Export Features
- CSV export for all table data
- Date range filtering
- Property/owner scoping
- Print-friendly view

---

## 11. Settings

**Purpose:** System-wide configuration.

### Sections

| Section | Content |
|---------|---------|
| Admin Profile | Name, email, avatar, password change |
| User Roles | Role management (admin, owner, tenant), permissions |
| System Settings | Site name, logo, contact email, maintenance mode |
| Notification Settings | Email notifications, alert preferences |
| Payment Settings | Currency, payment methods, due date policy |
| Security | 2FA, session management, API keys |

### Layout
```
[Header: "Settings"]
[Side tabs: Profile | Roles | System | Notifications | Payments | Security]

[Content area based on selected tab]
```

---

## 12. Admin vs Owner Feature Comparison

| Feature | Admin | Owner |
|---------|-------|-------|
| View all properties | ✅ All | ✅ Own only |
| Manage any property | ✅ | ❌ |
| Approve/reject properties | ✅ | ❌ |
| View all tenants | ✅ | ✅ Own properties only |
| View all reservations | ✅ | ✅ Own properties only |
| Manage payments (all) | ✅ | ✅ Own properties only |
| Transaction log (all) | ✅ | ✅ Own properties only |
| Owner verification | ✅ | ❌ |
| User management | ✅ | ❌ |
| System settings | ✅ | ❌ |
| Reports & analytics | ✅ Full | ✅ Own scope |
| Receipt verification | ✅ | ❌ |
| Create admin users | ✅ | ❌ |

---

## 13. Complete Sidebar Structure (Final)

```
BOARDMATCH
─────────────────────────────
MAIN
  Dashboard                   → Platform overview, KPIs
  My Properties               → Manage boarding houses, rooms
  Owners                      → Owner accounts & verification
  Tenants                     → All tenants
  Reservations                → Booking management
  Payments                    → Revenue & receipt verification
  Transactions                → Payment journal
  Messages                    → Inquiries & conversations
─────────────────────────────
ACCOUNT
  Reports                     → Analytics & exports
  Settings                    → System configuration
─────────────────────────────
  [User avatar] Profile       → Admin profile
  [Logout icon] Logout        → Sign out
```

---

## 14. Suggested Database Entities Summary

| Module | Tables |
|--------|--------|
| Auth/Users | `users`, `password_reset_tokens`, `personal_access_tokens` |
| Properties | `boarding_houses`, `boarding_house_images`, `boarding_house_photos` |
| Rooms | `rooms` |
| Tenants | `tenants` |
| Reservations | `reservations` |
| Payments | `payments`, `payment_receipts` |
| Inquiries | `inquiries` |
| Reviews | `reviews` |
| Messages | `messages`, `conversations` |
| Notifications | `notifications` |
| Reports | (queries, no dedicated tables needed) |
| Owner Verification | `owner_verifications`, `owner_documents` |
| System Settings | `settings` (key-value) |
| Activity Log | `activity_logs` |
| Cities | `cities_municipalities` |
| Roommate Matching | `roommate_match_requests`, `compatibility_scores`, `recommendations` |
