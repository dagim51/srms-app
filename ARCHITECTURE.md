# SRMS Architecture & Domain Design Guide

## 1. Executive Overview

SRMS (Smart Real Estate Management System) is an enterprise-grade platform combining real estate marketplace operations, short-term accommodation stays, property management, urban development projects, and financial transactions.

To ensure maintainability, clear boundaries, and scalability, SRMS utilizes a **Modular Domain-Driven Architecture** under `app/Domains/`. The architecture strictly adheres to a **Zero-Downtime Forwarder Pattern**, ensuring 100% backward compatibility for legacy callers, external packages, and historical database serialized class names.

---

## 2. Directory Structure & Layout

SRMS follows a dual-layer architectural model:

```
app/
├── Domains/                              # Modular Domain Layer
│   ├── Identity/                         # Authentication, Authorization, Users, Tokens
│   ├── Organization/                     # Agencies, Developers, Companies, Teams
│   ├── Location/                         # Geo-hierarchies: Country, City, SubCity, Woreda
│   ├── GIS/                              # Spatial boundaries, GIS layers, coordinates
│   ├── Property/                         # Core properties, amenities, media, categories
│   ├── Listing/                          # Marketplace listings, pricing, promotions
│   ├── Engagement/                       # Inquiries, messaging, viewings, offers, CRM
│   ├── Stay/                             # Short-term rentals, accommodations, rules
│   ├── Booking/                          # Stay bookings, calendars, availability
│   ├── Review/                           # Reviews, dimensions, moderation responses
│   ├── Development/                      # Off-plan projects, buildings, installments
│   ├── CRM/                              # Leads, pipelines, agent commissions
│   ├── Rental/                           # Long-term rentals, leases, tenant profiles
│   ├── PropertyManagement/               # Work orders, cleaning, facility bookings
│   ├── Transaction/                      # Ledger entries, payments, invoices, payouts
│   ├── Trust/                            # Identity verification, disputes, fraud flags
│   ├── Notification/                     # Multi-channel notifications, preferences
│   ├── Subscription/                     # SaaS subscriptions, tiers, entitlements
│   ├── Advertising/                      # Featured ads, marketing campaigns
│   ├── Analytics/                        # Market metrics, valuation models, reports
│   └── Administration/                   # Audit logs, system settings, webhooks
│
├── Http/                                 # Cross-Cutting & Legacy HTTP Layer
│   ├── Controllers/
│   │   ├── Api/V1/                       # API Forwarders & Cross-Domain Controllers
│   │   └── Web/                          # Web Forwarders & Generic Dashboards/Settings
│   ├── Middleware/                       # Authentication, Localization, Security
│   ├── Requests/                         # Legacy Form Request Forwarders
│   └── Resources/                        # Legacy API Resource Forwarders
│
├── Models/                               # Legacy Model Forwarders (`class_alias`)
└── Policies/                             # Legacy Policy Forwarders
```

---

## 3. Domain Anatomy

Each domain in `app/Domains/{DomainName}` is self-contained and encapsulates its business rules:

```
app/Domains/{DomainName}/
├── Models/              # Eloquent models, traits, and scopes
├── Services/            # Core business logic and external integrations
├── Events/              # Domain lifecycle events
├── Listeners/           # Event listeners and asynchronous handlers
├── Exceptions/          # Domain-specific business exceptions
├── Policies/            # Authorization policies for domain models
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/      # REST API controllers for external clients & SPA
│   │   └── Web/         # Blade/Inertia controllers for web routes
│   ├── Requests/        # Form validation and authorization requests
│   └── Resources/       # JSON serialization resources
└── routes/              # Domain-specific route definitions (if isolated)
```

---

## 4. The Zero-Downtime Forwarder Pattern

To enable seamless migration from a monolithic `app/Models` and `app/Http/Controllers` structure without breaking existing routes, jobs, serialized sessions, or polymorphic relationships, SRMS implements the **Forwarder Pattern**:

### 4.1 Model Forwarders
Located in `app/Models/` and registered dynamically via `DomainServiceProvider`:
```php
// app/Models/Property.php
<?php

namespace App\Models;

if (! class_exists('App\Models\Property', false)) {
    class_alias('App\Domains\Property\Models\Property', 'App\Models\Property');
}
```
- **Polymorphic Compatibility:** For polymorphic relations (e.g. `AuditLog`, `Review`, `Dispute`), the canonical short name or legacy class name is preserved so database records remain consistent across queries.
- **Factory Resolution:** Factories reside under `database/factories/Domains/{DomainName}/Models/` and are resolved via custom factory resolution conventions.

### 4.2 Controller Forwarders
Legacy controller locations inherit directly from the domain controller:
```php
// app/Http/Controllers/Api/V1/ListingController.php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Listing\Http\Controllers\Api\V1\ListingController as DomainListingController;

class ListingController extends DomainListingController
{
}
```

### 4.3 Web Controller Forwarders
Domain-specific Web controllers live in `app/Domains/{DomainName}/Http/Controllers/Web/` while generic controllers (e.g. `DashboardWebController`, `AuthController`, `SettingsWebController`) remain in `app/Http/Controllers/Web/`.

---

## 5. Coding Standards & Quality Gates

Every change in SRMS is verified against rigorous automated gates:

| Tool | Purpose | Command | Standard |
|------|---------|---------|----------|
| **PHPUnit / Pest** | Functional & Unit Testing | `php artisan test` | 305/305 tests passing, 0 regressions |
| **Laravel Pint** | Code Style & Formatting | `./vendor/bin/pint --test` | PSR-12 + Laravel strict presets |
| **Larastan (PHPStan)** | Static Analysis | `./vendor/bin/phpstan analyse --memory-limit=2G` | Clean against baseline |
| **Vite** | Asset Compilation | `npm run build` | 0 errors |
| **Composer** | Autoload Integrity | `composer dump-autoload` | 0 warnings |

---

## 6. How to Add New Features

1. **Identify the Domain:** Locate the corresponding domain folder in `app/Domains/{DomainName}`. If no existing domain fits, propose a new domain using `php artisan make:domain`.
2. **Implement Models & Migrations:** Place models in `app/Domains/{DomainName}/Models/`. Always add a forwarder in `app/Models/{ModelName}.php` using `class_alias` to maintain system-wide backwards compatibility.
3. **Encapsulate Business Logic:** Write business operations inside `app/Domains/{DomainName}/Services/`, not inside controllers.
4. **Expose Controllers & Requests:** Create validated form requests in `Http/Requests/` and controllers in `Http/Controllers/Api/V1/` or `Http/Controllers/Web/`.
5. **Run Verification:** Run `php artisan test` and `./vendor/bin/pint --test` before committing.
