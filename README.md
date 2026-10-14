# Villa Management System — Luxury Villa & Resort Operations

[![Laravel 13](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php)](https://php.net)
[![PostgreSQL 18](https://img.shields.io/badge/PostgreSQL-18-336791?style=flat&logo=postgresql)](https://www.postgresql.org)
[![Bootstrap 5.3](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap)](https://getbootstrap.com)
[![Offline Ready](https://img.shields.io/badge/Network-100%25%20Offline%20%2F%20Internal-success?style=flat)]()

**Villa Management System** is an enterprise-grade, full-lifecycle Luxury Villa & Resort Management System built with **Laravel 13**, tailored for high-concurrency operations on **PostgreSQL 18**, and rendered with a responsive **Bootstrap 5.3 + FontAwesome 6 + Bootstrap Icons + Chart.js** interface.

Designed specifically for secluded luxury resorts, private estates, and hospitality networks operating on **secure internal networks without requiring internet access**, all frontend dependencies (stylesheets, scripts, icon glyphs, and webfonts) are pre-bundled inside the application repository.

---

## Table of Contents

1. [Key Capabilities & Features](#key-capabilities--features)
2. [Technology Stack](#technology-stack)
3. [Offline & Internal Network Architecture](#offline--internal-network-architecture)
4. [Prerequisites & System Requirements](#prerequisites--system-requirements)
5. [Installation & Setup with Laragon](#installation--setup-with-laragon)
6. [PostgreSQL 18 Configuration](#postgresql-18-configuration)
7. [Database Seeding & Default Credentials](#database-seeding--default-credentials)
8. [Admin Bulk Data Import System](#admin-bulk-data-import-system)
   - [Supported CSV Schemas](#supported-csv-schemas)
   - [Error Auditing & Telemetry](#error-auditing--telemetry)
9. [Module Overview](#module-overview)
   - [1. Executive Operations Command](#1-executive-operations-command)
   - [2. Villa Fleet Management](#2-villa-fleet-management)
   - [3. Reservations & Invoicing](#3-reservations--invoicing)
   - [4. VIP Guest Directory](#4-vip-guest-directory)
   - [5. Housekeeping & Maintenance Board](#5-housekeeping--maintenance-board)
   - [6. Operational Expenditure Ledger](#6-operational-expenditure-ledger)
10. [Automated Testing Suite](#automated-testing-suite)
11. [License](#license)

---

## Key Capabilities & Features

- **Executive Real-Time Dashboard**: Live KPI tracking for fleet occupancy rate, monthly gross revenue, guest roster, active turnovers, and a visual property status matrix.
- **Interactive Offline Charts**: 6-month settled revenue trajectories and portfolio category distributions powered by a local, bundled Chart.js runtime.
- **Real-Time Property State Engine**: Seamlessly transition villas between `Available`, `Occupied`, `Cleaning`, `Maintenance`, and `Reserved`.
- **Reservation Conflict Prevention**: Database-level overlapping date checks preventing accidental double-bookings for any estate.
- **Turnover Housekeeping Automation**: Automated scheduling of deep-cleaning turnover tasks with assigned cost when a guest checks out.
- **Integrated Guest Folio & Invoicing**: Automated calculation of nights, cleaning fees, concierge charges, hospitality taxes, payment settlements, and printable luxury folios.
- **Multi-Tier VIP Guest Management**: Profiling preferences, dietary specifications, passport/national identification, and lifetime spend history for `VVIP`, `VIP`, and `Standard` guests.
- **Admin Bulk Data Import Hub**: High-throughput CSV ingestion engine for Villas, Guests, Reservations, and Outlays with sample template generators, row validation, and error log diagnostics.
- **Role-Based Access Control (RBAC)**: Distinct permissions for `Admin`, `Manager`, and `Staff` roles protected via middleware.

---

## Technology Stack

| Layer | Component | Version / Specification |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel | 13.x |
| **Language Runtime** | PHP | 8.4+ (Strict typing, constructor promotion) |
| **Primary Database** | PostgreSQL | 18 (JSONB amenities, indexed search, transactional locks) |
| **Testing Database** | SQLite | In-memory (`:memory:`) for test velocity |
| **UI Framework** | Bootstrap | 5.3.x (Self-contained in `public/vendor/bootstrap`) |
| **Iconography** | FontAwesome 6 Free & Bootstrap Icons | WOFF/WOFF2 webfonts bundled locally |
| **Data Visualization**| Chart.js | 4.x UMD (Bundled in `public/vendor/chartjs`) |
| **Asset Pipeline** | Vite 8 | Pre-compiled production bundles |

---

## Offline & Internal Network Architecture

Many high-end resorts, island compounds, and private estates operate on dedicated, isolated intranet segments (LAN/WLAN) without external internet egress.

Villa Management System includes zero external CDN calls. All assets are served directly from the web server:
- `public/vendor/bootstrap/css/bootstrap.min.css`
- `public/vendor/bootstrap/js/bootstrap.bundle.min.js`
- `public/vendor/fontawesome/css/all.min.css` & `public/vendor/fontawesome/webfonts/*`
- `public/vendor/bootstrap-icons/font/bootstrap-icons.min.css` & `public/vendor/bootstrap-icons/font/fonts/*`
- `public/vendor/chartjs/chart.min.js`

---

## Prerequisites & System Requirements

- **PHP**: 8.3 or 8.4 with extensions:
  - `pdo_pgsql` & `pgsql`
  - `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `intl`
- **Database**: PostgreSQL 18 (or PostgreSQL 15+ compatible)
- **Composer**: 2.7+
- **Local Server Stack**: Laragon, XAMPP, or Docker

---

## Installation & Setup with Laragon

### 1. Clone or Copy the Repository
Place this repository into your web root (e.g., `C:\laragon\www\villa-management-system` or `C:\xampp\htdocs\villa-management-system`).

### 2. Verify PHP 8.4 in Laragon
In Laragon, right click -> **PHP** -> **Version** -> select `php-8.4.x`.
Ensure PostgreSQL extensions are enabled in your `php.ini`:
```ini
extension=pdo_pgsql
extension=pgsql
```

### 3. Environment Configuration
Copy `.env.example` to `.env` (or update existing `.env`):
```bash
cp .env.example .env
```

Ensure your PostgreSQL 18 credentials match your setup:
```env
APP_NAME="Villa Management System"
APP_ENV=local
APP_KEY=base64:GmRr2THO3HVQU3HmGvGGAiG/rcodkzoKXrlNI5LSET8=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=villa_management_db
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### 4. Create the Database in PostgreSQL 18
Using `psql` or pgAdmin:
```sql
CREATE DATABASE villa_management_db;
```

### 5. Run Migrations & Seeders
Once PostgreSQL 18 is running, execute:
```bash
php artisan migrate:fresh --seed
```

### 6. Serve the Application
Start the local server:
```bash
php artisan serve
```
Visit **`http://localhost:8000`** in your browser.

---

## PostgreSQL 18 Configuration

The application is tuned for PostgreSQL 18 in `config/database.php`:
```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'villa_management_db'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD', 'postgres'),
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => env('DB_SSLMODE', 'prefer'),
],
```

---

## Database Seeding & Default Credentials

The database seeder provisions demo users, 8 luxury villas, international VIP guests, active and upcoming bookings, maintenance schedules, and resort expenditures.

On the login screen, click any **Quick Demo Fill** button to populate credentials instantly:

| Role | Email Address | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@villamanagement.local` | `password123` | Full access, settings, RBAC, Bulk Import Hub |
| **Operations Manager** | `manager@villamanagement.local` | `password123` | Villa management, reservations, concierge, tasks |
| **Concierge / Staff** | `concierge@villamanagement.local` | `password123` | Check-in/out, task execution, guest profiles |
| **Housekeeping Lead** | `housekeeping@villamanagement.local` | `password123` | Task tracking and turnover cleans |

---

## Admin Bulk Data Import System

Located at `/admin/bulk-import` (restricted to `role:admin`), this hub allows operators to import thousands of records in seconds via standard CSV files.

### 1. Downloadable Sample CSV Templates
Operators can download sample CSV files directly from the UI or via routes:
- **Villas Template**: `GET /admin/bulk-import/template/villas`
- **Guests Template**: `GET /admin/bulk-import/template/guests`
- **Bookings Template**: `GET /admin/bulk-import/template/bookings`
- **Expenses Template**: `GET /admin/bulk-import/template/expenses`

### 2. Supported CSV Schemas

#### Villas (`villas.csv`)
```csv
code,name,category,bedrooms,bathrooms,max_guests,area_sqm,price_per_night,weekend_price_per_night,cleaning_fee,security_deposit,status,location,address,description,amenities,is_featured
VIL-101,Villa Azure Horizons,Beachfront,4,5,8,450,1250.00,1450.00,120.00,500.00,available,North Shore Beach,101 Ocean Drive,Stunning oceanfront villa,"Private Pool, Ocean View, Wi-Fi 6, Chef Service",true
```

#### VIP Guests (`guests.csv`)
```csv
first_name,last_name,email,phone,nationality,id_or_passport,vip_level,special_requests
Alexander,Sterling,sterling.alexander@luxurytravel.com,+1 555-234-5678,United States,US987654321,vvip,Requires early check-in and sparkling mineral water
```

#### Bookings & Reservations (`bookings.csv`)
```csv
booking_code,villa_code,guest_email,guest_name,check_in,check_out,adults,children,nightly_rate,cleaning_fee,service_fee,tax_amount,total_amount,paid_amount,status,payment_method,special_notes
BK-2026-101,VIL-101,sterling.alexander@luxurytravel.com,Alexander Sterling,2026-11-01,2026-11-06,4,2,1250.00,120.00,150.00,200.00,6720.00,6720.00,confirmed,credit_card,VIP helicopter arrival
```

#### Operational Expenses (`expenses.csv`)
```csv
title,category,villa_code,amount,expense_date,vendor,payment_method,invoice_number,notes
Monthly Pool Maintenance,maintenance,VIL-101,450.00,2026-10-01,Crystal Waters,Bank Transfer,INV-8891,Weekly pool filtration checks
```

### 3. Error Auditing & Telemetry
Every batch execution is permanently stored in the `import_logs` table. If errors occur on specific lines (e.g. invalid date order, missing required fields, nonexistent villa codes), valid rows are committed while rejected rows are logged with exact line numbers and explanations for audit review.

---

## Module Overview

### 1. Executive Operations Command
- Instant overview of resort status.
- Live Villa Status Grid with one-click status transitions.
- Interactive Chart.js graphs displaying financial trends and portfolio breakdown.
- Turnover schedule tabs listing today's expected arrivals and departures.

### 2. Villa Fleet Management
- Filter by category (`Beachfront`, `Cliffside`, `Garden Estate`, `Overwater`, `Mountain View`), status, and bedroom count.
- Detailed specs: bed/bath counts, living square meterage, standard/weekend tariffs, turnover cleaning fees, and security deposit requirements.
- Full amenity tag checklist (Helipad, Private Beach, Butler Service, Wine Cellar, etc.).

### 3. Reservations & Invoicing
- Conflict-checking date reservation wizard.
- One-click **Check-In** and **Check-Out** workflows.
- Automatic turnover cleaning task dispatch on guest departure.
- Printable Luxury Guest Folio (`/bookings/{booking}/invoice`) suitable for physical printing or PDF export.
- In-place transaction modal to record partial deposits or complete settlement.

### 4. VIP Guest Directory
- Complete guest profiles tracking contact info, nationality, and travel documents.
- VIP classification badges (`Standard`, `VIP`, `VVIP`).
- History of all stays with aggregate lifetime value (LTV).

### 5. Housekeeping & Maintenance Board
- Work order tracking by priority (`Urgent`, `High`, `Medium`, `Low`) and type (`Cleaning`, `Pool Service`, `Plumbing`, `Electrical`, `HVAC`, `Landscaping`, `Inspection`).
- Assignment to specific staff members with scheduled execution timestamps.
- Fast status completion action that automatically marks cleaned villas as `Available`.

### 6. Operational Expenditure Ledger
- Real-time logging of resort expenditures across categories (`Maintenance`, `Utilities`, `Supplies`, `Staff Payroll`, `Food & Beverage`, `Marketing`, `Overhead`).
- Allocatable to specific villas or general resort operations.
- Date, category, and villa filtering with dynamic total calculation.

---

## Automated Testing Suite

The application includes an automated test suite executed with PHPUnit using an in-memory database:

```bash
php artisan test
```

### Test Coverage Highlights:
- `AuthFeatureTest`: Verifies authentication, validation, registration, and logout flows.
- `VillaFeatureTest`: Tests fleet index views, creation validation, and live status mutation.
- `BookingFeatureTest`: Tests date conflict prevention, ledger calculations, check-in, check-out automation, and invoice generation.
- `BulkImportFeatureTest`: Verifies RBAC restrictions, sample template streaming, CSV parser execution, and database persistence.

---

## Code Quality & Standards

All PHP code adheres to modern Laravel guidelines:
- Explicit parameter and return type hints on all methods.
- Single Responsibility principles across controllers, FormRequests, and services.
- Eager-loading of Eloquent relationships to eliminate N+1 query bottlenecks.
- Formatted with **Laravel Pint** (`vendor/bin/pint --format agent`).

---

## License

This software is proprietary and designed for exclusive resort operations. Distributed under the MIT License.
