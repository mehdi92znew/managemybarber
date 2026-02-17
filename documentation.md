# 📘 BarberSaaS — Full Product Documentation

> **Version**: 1.1.0  
> **Stack**: Laravel 12 · Blade + Vue 3 · Tailwind CSS · Spatie Permissions  
> **Last Updated**: February 14, 2026

---

## Table of Contents

1. [Product Overview](#1-product-overview)
2. [Architecture](#2-architecture)
3. [Roles & Permissions](#3-roles--permissions)
4. [Database Schema](#4-database-schema)
5. [Core Modules](#5-core-modules)
6. [Commission Engine](#6-commission-engine)
7. [Calendar & Booking](#7-calendar--booking)
8. [Payments](#8-payments)
9. [Subscription / SaaS Layer](#9-subscription--saas-layer)
10. [Dashboards & Analytics](#10-dashboards--analytics)
11. [Multi-Tenant Data Isolation](#11-multi-tenant-data-isolation)
12. [Routing & API](#12-routing--api)
13. [UI/UX Design System](#13-uiux-design-system)
14. [File Structure](#14-file-structure)
15. [Development Setup](#15-development-setup)

---

## 1. Product Overview

**BarberSaaS** is a multi-tenant SaaS platform that allows barbershop owners to:

- Manage their barbers, services, and customers
- Schedule appointments via a visual calendar
- Track commissions automatically
- Monitor revenue and performance dashboards
- Track cash payments

The platform is operated by a **Super Admin** who manages all shops and subscriptions.

### Key Features

| Feature             | Description                                          |
| ------------------- | ---------------------------------------------------- |
| Multi-shop tenancy  | Each shop's data is fully isolated                   |
| Role-based access   | Super Admin, Owner, Barber — each with scoped access |
| Commission tracking | Auto-calculated on appointment completion            |
| Visual calendar     | Click-to-book with double-booking prevention         |
| Modal-based CRUD    | All create/edit via beautiful animated modals        |
| Simple payments     | Cash payment tracking on completion                  |
| SaaS subscriptions  | Managed by super admin                               |

---

## 2. Architecture

### Blade + Vue Hybrid

The app uses **Blade templates** for page layouts and **Vue 3 components** for interactive elements.

```
Browser Request
    │
    ▼
web.php Route → Controller → return view('owner.calendar', $data)
    │
    ▼
Blade Layout (sidebar, nav, structure)
    │
    ▼
<div id="calendar-app" data-barbers='@json($barbers)'>
    │
    ▼
Vue component mounts here (calendar, modals, charts)
```

### Why Blade + Vue (not pure SPA)?

| Benefit                     | Detail                                    |
| --------------------------- | ----------------------------------------- |
| Simple routing              | `web.php` controls everything             |
| SEO friendly                | Server-rendered HTML                      |
| Easy to understand          | Each page = one Blade file                |
| Interactive where needed    | Vue handles calendar, modals, live search |
| No complex state management | No Vuex/Pinia needed                      |

### How Vue Mounts in Blade

```blade
{{-- In Blade template --}}
@section('content')
    <div id="calendar-app"
         data-barbers='@json($barbers)'
         data-services='@json($services)'>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/pages/owner/calendar.js')
@endpush
```

```javascript
// resources/js/pages/owner/calendar.js
import { createApp } from "vue";
import CalendarApp from "./CalendarApp.vue";

const el = document.getElementById("calendar-app");
createApp(CalendarApp, {
    barbers: JSON.parse(el.dataset.barbers),
    services: JSON.parse(el.dataset.services),
}).mount(el);
```

---

## 3. Roles & Permissions

### 3.1 Super Admin 🟣

**Scope**: Entire platform (`shop_id = NULL`)

| Can Do                    | Cannot Do                 |
| ------------------------- | ------------------------- |
| View all shops            | Manage daily appointments |
| Create / suspend shops    | Set commission rules      |
| Manage subscriptions      | Book appointments         |
| View global revenue (MRR) |                           |
| View total barbers        |                           |

### 3.2 Shop Owner 🟢

**Scope**: Their own shop only

| Can Do                     | Cannot Do                 |
| -------------------------- | ------------------------- |
| Manage barbers             | See other shops           |
| Define commission rules    | Manage subscriptions      |
| Manage services            | Access platform analytics |
| View full calendar         |                           |
| Manage customers           |                           |
| View revenue & commissions |                           |
| See barber performance     |                           |

### 3.3 Barber 🔵

**Scope**: Their own data within the shop

| Can Do                       | Cannot Do               |
| ---------------------------- | ----------------------- |
| View own calendar            | See shop revenue        |
| Book appointments            | Modify other barbers    |
| Add clients                  | Change commission rules |
| Select main + extra services | See other barbers' data |
| Mark appointment completed   |                         |
| See own commission/earnings  |                         |

### Permission Matrix

| Action                  | Super Admin | Owner | Barber |
| ----------------------- | :---------: | :---: | :----: |
| View all shops          |     ✅      |  ❌   |   ❌   |
| Manage barbers          |     ❌      |  ✅   |   ❌   |
| Manage services         |     ❌      |  ✅   |   ❌   |
| Manage customers        |     ❌      |  ✅   |   ❌   |
| View all appointments   |     ❌      |  ✅   |   ❌   |
| View own appointments   |     ❌      |  ✅   |   ✅   |
| Book appointment        |     ❌      |  ✅   |   ✅   |
| Complete appointment    |     ❌      |  ✅   |   ✅   |
| View shop revenue       |     ✅      |  ✅   |   ❌   |
| View own earnings       |     ❌      |  ❌   |   ✅   |
| Manage subscriptions    |     ✅      |  ❌   |   ❌   |
| Change commission rules |     ❌      |  ✅   |   ❌   |

---

## 4. Database Schema

### Entity Relationship Diagram

```
shops ──────┬─── users (owner, barbers)
            ├─── services
            ├─── customers
            ├─── appointments ──── appointment_services (pivot)
            │         └──── payments
            └─── subscriptions
```

### 4.1 `shops`

| Column                  | Type       | Notes                                     |
| ----------------------- | ---------- | ----------------------------------------- |
| id                      | bigint PK  |                                           |
| name                    | string     | Shop name                                 |
| slug                    | string     | URL-friendly unique identifier            |
| address                 | text       | Nullable                                  |
| phone                   | string     | Nullable                                  |
| subscription_status     | string     | `trial`, `active`, `suspended`, `expired` |
| subscription_ends_at    | timestamp  | Nullable                                  |
| created_at / updated_at | timestamps |                                           |

### 4.2 `users`

| Column                  | Type         | Notes                              |
| ----------------------- | ------------ | ---------------------------------- |
| id                      | bigint PK    |                                    |
| shop_id                 | FK → shops   | Nullable (null for super_admin)    |
| name                    | string       |                                    |
| email                   | string       | Unique                             |
| password                | string       | Hashed                             |
| role                    | enum         | `super_admin`, `owner`, `barber`   |
| commission_type         | enum         | Nullable — `percentage` or `fixed` |
| commission_value        | decimal(8,2) | Nullable                           |
| is_active               | boolean      | Default: true                      |
| created_at / updated_at | timestamps   |                                    |

### 4.3 `services`

| Column                  | Type         | Notes                         |
| ----------------------- | ------------ | ----------------------------- |
| id                      | bigint PK    |                               |
| shop_id                 | FK → shops   |                               |
| name                    | string       | e.g. "Haircut", "Beard Trim"  |
| price                   | decimal(8,2) |                               |
| duration_minutes        | integer      | Used for end_time calculation |
| is_extra                | boolean      | Extra service flag            |
| is_active               | boolean      | Soft toggle                   |
| created_at / updated_at | timestamps   |                               |

**Examples:**

- Main: Haircut, Beard Trim
- Extra: Design, Wash, Coloring

### 4.4 `customers`

| Column                  | Type       | Notes                      |
| ----------------------- | ---------- | -------------------------- |
| id                      | bigint PK  |                            |
| shop_id                 | FK → shops |                            |
| name                    | string     |                            |
| phone                   | string     | Nullable                   |
| notes                   | text       | Nullable                   |
| last_visit_at           | timestamp  | Auto-updated on completion |
| created_at / updated_at | timestamps |                            |

### 4.5 `appointments` ⭐ Core Engine

| Column                  | Type           | Notes                                 |
| ----------------------- | -------------- | ------------------------------------- |
| id                      | bigint PK      |                                       |
| shop_id                 | FK → shops     |                                       |
| barber_id               | FK → users     | The assigned barber                   |
| customer_id             | FK → customers |                                       |
| start_time              | datetime       |                                       |
| end_time                | datetime       | Calculated from total duration        |
| status                  | string         | `scheduled`, `completed`, `cancelled` |
| notes                   | text           | Nullable                              |
| total_price             | decimal(10,2)  | Sum of service prices at time         |
| commission_amount       | decimal(10,2)  | Stored on completion                  |
| payment_status          | string         | `paid`, `unpaid`                      |
| created_at / updated_at | timestamps     |                                       |

> **Why store `commission_amount`?** To avoid recalculation and maintain history even if commission rules change later.

### 4.6 `appointment_services` (Pivot)

| Column         | Type              | Notes                        |
| -------------- | ----------------- | ---------------------------- |
| id             | bigint PK         |                              |
| appointment_id | FK → appointments |                              |
| service_id     | FK → services     |                              |
| price_at_time  | decimal(8,2)      | Snapshot of price at booking |

> **Why `price_at_time`?** Service prices may change. We freeze the price at booking.

### 4.7 `payments`

| Column                  | Type              | Notes                   |
| ----------------------- | ----------------- | ----------------------- |
| id                      | bigint PK         |                         |
| appointment_id          | FK → appointments |                         |
| amount                  | decimal(10,2)     |                         |
| method                  | string            | `cash` (simple for now) |
| paid_at                 | timestamp         |                         |
| created_at / updated_at | timestamps        |                         |

### 4.8 `subscriptions`

| Column                  | Type         | Notes                            |
| ----------------------- | ------------ | -------------------------------- |
| id                      | bigint PK    |                                  |
| shop_id                 | FK → shops   |                                  |
| plan_name               | string       | e.g. "Basic", "Pro"              |
| price                   | decimal(8,2) |                                  |
| starts_at               | timestamp    |                                  |
| ends_at                 | timestamp    |                                  |
| status                  | string       | `active`, `cancelled`, `expired` |
| created_at / updated_at | timestamps   |                                  |

---

## 5. Core Modules

### 5.1 Shop Management (Super Admin)

**Route prefix**: `/admin`

| Action    | Method | URI                       | Blade View                  |
| --------- | ------ | ------------------------- | --------------------------- |
| Dashboard | GET    | /admin/dashboard          | admin/dashboard.blade.php   |
| List      | GET    | /admin/shops              | admin/shops/index.blade.php |
| Create    | POST   | /admin/shops              | Modal (AJAX)                |
| Update    | PUT    | /admin/shops/{id}         | Modal (AJAX)                |
| Suspend   | PATCH  | /admin/shops/{id}/suspend | AJAX                        |

### 5.2 Barber Management (Owner)

**Route prefix**: `/owner`

| Action | Method | URI                 | UI                           |
| ------ | ------ | ------------------- | ---------------------------- |
| List   | GET    | /owner/barbers      | Table with create/edit modal |
| Create | POST   | /owner/barbers      | Modal → AJAX                 |
| Update | PUT    | /owner/barbers/{id} | Modal → AJAX                 |
| Delete | DELETE | /owner/barbers/{id} | Confirm modal → AJAX         |

**Commission config** is set per barber:

- `commission_type`: `percentage` or `fixed`
- `commission_value`: numeric value (e.g., 40 for 40%)

### 5.3 Service Management (Owner)

| Action | Method | URI                  | UI            |
| ------ | ------ | -------------------- | ------------- |
| List   | GET    | /owner/services      | Table + modal |
| Create | POST   | /owner/services      | Modal         |
| Update | PUT    | /owner/services/{id} | Modal         |
| Delete | DELETE | /owner/services/{id} | Confirm       |

Services are categorized:

- **Main services**: `is_extra = false` (Haircut, Beard)
- **Extra services**: `is_extra = true` (Design, Wash)

### 5.4 Customer Management (Owner)

| Action | Method | URI                   | UI            |
| ------ | ------ | --------------------- | ------------- |
| List   | GET    | /owner/customers      | Table + modal |
| Create | POST   | /owner/customers      | Modal         |
| Update | PUT    | /owner/customers/{id} | Modal         |

- Quick-create from the booking modal

### 5.5 Appointment Lifecycle

```
   ┌──────────┐     complete     ┌───────────┐
   │ SCHEDULED│ ───────────────► │ COMPLETED │
   └────┬─────┘                  └───────────┘
        │ cancel                  (commission calculated,
        ▼                         payment recorded)
   ┌───────────┐
   │ CANCELLED │
   └───────────┘
```

---

## 6. Commission Engine

### Rules

Commission is calculated **ONLY** when an appointment is marked `completed`.

| Type       | Formula                                   | Example                |
| ---------- | ----------------------------------------- | ---------------------- |
| Percentage | `total_price × commission_value / 100`    | €100 × 40% = **€40**   |
| Fixed      | `commission_value` (flat per appointment) | Fixed **€15** per appt |

### Calculation Flow

```
Appointment marked as "completed"
    │
    ▼
total_price = SUM(appointment_services.price_at_time)
    │
    ▼
Get barber's commission_type & commission_value
    │
    ├── percentage → commission = total_price × value / 100
    └── fixed      → commission = value
    │
    ▼
Store in appointments:
    total_price, commission_amount, status = 'completed', payment_status = 'paid'
    + Create Payment record (cash)
    + Update customer.last_visit_at
```

### Service Class

```php
// App\Services\CommissionService
public function applyOnCompletion(Appointment $appointment): void
{
    $totalPrice = $appointment->services->sum('pivot.price_at_time');
    $barber = $appointment->barber;

    $commission = match ($barber->commission_type) {
        'percentage' => round($totalPrice * $barber->commission_value / 100, 2),
        'fixed'      => $barber->commission_value,
        default      => 0,
    };

    $appointment->update([
        'total_price'       => $totalPrice,
        'commission_amount' => $commission,
        'status'            => 'completed',
        'payment_status'    => 'paid',
    ]);

    Payment::create([
        'appointment_id' => $appointment->id,
        'amount'         => $totalPrice,
        'method'         => 'cash',
        'paid_at'        => now(),
    ]);

    $appointment->customer->update(['last_visit_at' => now()]);
}
```

---

## 7. Calendar & Booking

### Calendar UX Flow

```
1. User clicks empty time slot on calendar
2. Booking modal opens (animated slide-up + backdrop blur):
   ├── Select barber (auto-filled for barber login)
   ├── Search customer (live search) / quick-create new
   ├── Select main service (dropdown)
   ├── Select extra services (multi-select chips)
   └── Add notes (textarea)
3. Click "Confirm Booking"
4. AJAX POST to /api/appointments
5. Backend:
   ├── Validate no double-booking
   ├── Calculate end_time = start_time + SUM(durations)
   ├── Create appointment + attach services
   └── Return success
6. Calendar re-renders with new event (no page reload)
```

### Double-Booking Prevention

```sql
SELECT COUNT(*) FROM appointments
WHERE barber_id = ?
  AND status != 'cancelled'
  AND start_time < :new_end_time
  AND end_time > :new_start_time
```

### Calendar Views

| Role   | View                                   |
| ------ | -------------------------------------- |
| Owner  | All barbers' appointments, color-coded |
| Barber | Only their own appointments            |

---

## 8. Payments

**Simplified**: Cash payment tracking only.

When an appointment is marked **completed**:

1. `total_price` calculated from services → stored
2. `commission_amount` calculated → stored
3. `payment_status` → `paid`
4. `Payment` record created with `method = 'cash'`

No separate payment flow. Automatic on completion.

---

## 9. Subscription / SaaS Layer

### Subscription Lifecycle

```
Shop Registration → Trial (14 days)
    │
    ├── Super Admin activates → Active
    │       │
    │       └── Super Admin extends / cancels
    │
    └── Trial expires → Suspended (no access)
```

### States

| State     | Can Access? | Description            |
| --------- | :---------: | ---------------------- |
| trial     |     ✅      | Free trial             |
| active    |     ✅      | Subscription active    |
| suspended |     ❌      | No active subscription |
| expired   |     ❌      | Subscription ended     |

### Middleware: `EnsureShopActive`

Applied to all owner/barber routes. Redirects to "subscription required" page if not active/trial.

---

## 10. Dashboards & Analytics

### Barber Dashboard

| Metric             | Source                                         |
| ------------------ | ---------------------------------------------- |
| Today's Earnings   | `SUM(commission_amount)` where date = today    |
| Monthly Commission | `SUM(commission_amount)` where month = current |
| Appointments Today | `COUNT(appointments)` where date = today       |
| Upcoming Schedule  | Next 5 appointments by start_time              |

### Owner Dashboard

| Metric            | Source                                    |
| ----------------- | ----------------------------------------- |
| Total Revenue     | `SUM(total_price)` completed appointments |
| Total Commissions | `SUM(commission_amount)`                  |
| Net Profit        | Revenue − Commissions                     |
| Top Barber        | Barber with highest revenue (this month)  |
| Top Service       | Most booked service                       |
| Monthly Growth    | This month vs. last month (%)             |

### Super Admin Dashboard

| Metric               | Source                                 |
| -------------------- | -------------------------------------- |
| Total Shops          | `COUNT(shops)`                         |
| Active Subscriptions | `COUNT(shops)` where status = active   |
| MRR                  | `SUM(subscriptions.price)` active only |
| Total Appointments   | `COUNT(appointments)` platform-wide    |

---

## 11. Multi-Tenant Data Isolation

### Strategy: Laravel Global Scope

```php
// App\Models\Scopes\ShopScope
class ShopScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->shop_id) {
            $builder->where($model->getTable() . '.shop_id', auth()->user()->shop_id);
        }
    }
}
```

| Model        | Scoped? | Notes                        |
| ------------ | :-----: | ---------------------------- |
| Service      |   ✅    | `ShopScope` in `booted()`    |
| Customer     |   ✅    |                              |
| Appointment  |   ✅    |                              |
| User         |   ✅    | Custom scope for barbers     |
| Payment      |   ❌    | Via appointment relationship |
| Shop         |   ❌    | Super admin manages all      |
| Subscription |   ❌    | Super admin manages all      |

**Super Admin bypass**: `shop_id = NULL` → scope doesn't apply.

---

## 12. Routing & API

### Web Routes (Blade pages)

| Prefix  |    Role     | Example Routes                      |
| ------- | :---------: | ----------------------------------- |
| /admin  | super_admin | /admin/dashboard, /admin/shops      |
| /owner  |    owner    | /owner/dashboard, /owner/barbers    |
| /barber |   barber    | /barber/dashboard, /barber/calendar |

### API Routes (For Vue AJAX calls)

| Method | URI                             | Purpose               |
| ------ | ------------------------------- | --------------------- |
| GET    | /api/calendar/events            | Fetch calendar events |
| POST   | /api/appointments               | Create appointment    |
| PATCH  | /api/appointments/{id}/complete | Complete + commission |
| POST   | /api/customers                  | Quick-create customer |

---

## 13. UI/UX Design System

### Design Principles

| Principle        | Implementation                          |
| ---------------- | --------------------------------------- |
| Premium feel     | Glassmorphism cards, gradients, shadows |
| Human-friendly   | Intuitive navigation, clear labels      |
| Modal-based CRUD | No separate create/edit pages           |
| Responsive       | Mobile-first, works on tablets          |
| Micro-animations | Smooth transitions, hover effects       |
| Dark mode        | Default dark theme with light accents   |
| Typography       | Inter / Outfit from Google Fonts        |

### Modal System

All create/edit operations use animated modals:

- **Animated entrance**: slide-up + fade-in
- **Backdrop**: dimmed + blur effect
- **Form validation**: inline error messages
- **Success feedback**: toast notification
- **AJAX submit**: no page reload
- **Close**: click outside, ESC key, or X button

### Color Palette (HSL-based)

| Purpose    | Color                       |
| ---------- | --------------------------- |
| Primary    | Deep blue / indigo gradient |
| Accent     | Amber / gold                |
| Success    | Emerald green               |
| Danger     | Rose red                    |
| Background | Slate-900 (dark mode)       |
| Cards      | Slate-800 with glass effect |

---

## 14. File Structure

```
barber_app/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/                    # Super Admin
│   │   │   ├── AdminDashboardController.php
│   │   │   └── AdminShopController.php
│   │   ├── Owner/                    # Shop Owner
│   │   │   ├── OwnerDashboardController.php
│   │   │   ├── BarberController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── AppointmentController.php
│   │   │   └── CalendarController.php
│   │   ├── Barber/                   # Barber
│   │   │   ├── BarberDashboardController.php
│   │   │   ├── BarberCalendarController.php
│   │   │   └── BarberEarningsController.php
│   │   └── Api/                      # AJAX endpoints for Vue
│   │       ├── CalendarApiController.php
│   │       └── AppointmentApiController.php
│   ├── Models/
│   │   ├── Scopes/ShopScope.php
│   │   ├── Shop.php, User.php, Service.php
│   │   ├── Customer.php, Appointment.php
│   │   ├── AppointmentService.php, Payment.php
│   │   └── Subscription.php
│   ├── Services/CommissionService.php
│   └── Http/Middleware/EnsureShopActive.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── admin.blade.php
│   │   │   ├── owner.blade.php
│   │   │   └── barber.blade.php
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   └── shops/index.blade.php
│   │   ├── owner/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── barbers/index.blade.php    # Table + modal
│   │   │   ├── services/index.blade.php   # Table + modal
│   │   │   ├── customers/index.blade.php  # Table + modal
│   │   │   ├── calendar.blade.php         # Vue calendar
│   │   │   └── appointments/index.blade.php
│   │   └── barber/
│   │       ├── dashboard.blade.php
│   │       ├── calendar.blade.php
│   │       └── earnings.blade.php
│   └── js/
│       ├── app.js
│       ├── components/
│       │   ├── BookingModal.vue
│       │   ├── CalendarView.vue
│       │   ├── ConfirmModal.vue
│       │   ├── StatCard.vue
│       │   └── DataChart.vue
│       └── pages/
│           ├── owner/calendar.js
│           ├── owner/dashboard.js
│           ├── barber/calendar.js
│           └── barber/dashboard.js
├── routes/
│   ├── web.php
│   └── auth.php
└── database/
    ├── migrations/ (8 migration files)
    └── seeders/
        ├── RolePermissionSeeder.php
        ├── SuperAdminSeeder.php
        └── DatabaseSeeder.php
```

---

## 15. Development Setup

### Prerequisites

- PHP 8.2+ · Composer · Node.js 18+ · npm

### Installation

```bash
cd c:\laragon\www\barber_plateform\barber_app

composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Run Development Server

```bash
# Option 1: All-in-one
composer dev

# Option 2: Separate terminals
php artisan serve     # Backend → localhost:8000
npm run dev           # Vite → localhost:5173
```

### Default Accounts (After Seeding)

| Role        | Email                | Password |
| ----------- | -------------------- | -------- |
| Super Admin | admin@barbersaas.com | password |

---

> 📌 **This documentation is a living document.** Update it as features are implemented.
