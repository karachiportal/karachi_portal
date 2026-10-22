# Commissioner Karachi Portal — Complete Project Documentation

**Version:** 1.1.0  
**Last Updated:** 30 May 2026  
**Project Type:** Public Grievance & Price Monitoring System for Commissioner Karachi Office

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [System Architecture](#2-system-architecture)
3. [Tech Stack](#3-tech-stack)
4. [Project Structure](#4-project-structure)
5. [Mobile App (Flutter)](#5-mobile-app-flutter)
6. [Backend API (Laravel)](#6-backend-api-laravel)
7. [Web Admin Panel (Blade)](#7-web-admin-panel-blade)
8. [Database Schema](#8-database-schema)
9. [API Reference](#9-api-reference)
10. [Authentication & Authorization](#10-authentication--authorization)
11. [Demo Credentials](#11-demo-credentials)
12. [Setup & Installation](#12-setup--installation)
13. [Seeded Data](#13-seeded-data)
14. [Recent Changes (v1.1.0)](#14-recent-changes-v110)

---

## 1. Project Overview

The **Commissioner Karachi Portal** is a bilingual (English/Urdu) public service platform consisting of:

- **Mobile App (Flutter):** For citizens to browse daily price lists of essential commodities, file price-related complaints with GPS location and photos, track complaint status by complaint number (public), receive notifications, and view price trends.
- **Backend API (Laravel):** RESTful API serving data to the mobile app and admin panel. Handles authentication, complaint management, price CRUD, notifications, Excel import/export of prices, and complaint tracking.
- **Web Admin Panel (Laravel Blade):** Session-based admin dashboard with two roles — **Admin** (full CRUD) and **Viewer** (read-only). Manage complaints, price lists (categories & items), bulk update prices, import/export via Excel, broadcast notifications, view price update logs, recycle bin for soft-deleted records.

**Purpose:** Provide transparency in commodity pricing and enable citizens to report overpricing directly to the Commissioner's office.

---

## 2. System Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                   MOBILE APP (Flutter)                        │
│  ┌──────────┐  ┌──────────┐  ┌────────────┐  ┌────────────┐│
│  │ Dashboard │  │Complaints│  │ Price List │  │Notifications││
│  │   Page    │  │  Pages   │  │  + Track   │  │  + Profile  ││
│  └────┬─────┘  └────┬─────┘  └─────┬──────┘  └──────┬─────┘│
│       └──────────────┴─────────────┴───────────────┘        │
│                          │ HTTP (JSON)                       │
│                    ┌─────┴──────┐                             │
│                    │ API Service│ (Sanctum Token Auth)        │
│                    └─────┬──────┘                             │
└──────────────────────────┼───────────────────────────────────┘
                           │
                           ▼
┌──────────────────────────────────────────────────────────────┐
│               BACKEND API (Laravel 13.x)                      │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  routes/api.php  →  Controllers/API/*                  │  │
│  │                                                         │  │
│  │  Middleware:  auth:sanctum  |  admin  |  admin.web      │  │
│  └────────────────────────────────────────────────────────┘  │
│                           │                                    │
│                           ▼                                    │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Models  →  MySQL Database (karachi_portal)            │  │
│  │  10 application tables + 6 Laravel system tables       │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Web Admin Panel (resources/views/)                    │  │
│  │  Bootstrap 5 + DataTables + Chart.js + jQuery          │  │
│  │  Session-based auth (AdminWebMiddleware)               │  │
│  │  Read-only Viewer role (staff) supported               │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

---

## 3. Tech Stack

### Mobile App (Flutter)
| Technology | Version |
|---|---|
| Flutter SDK | `>=3.0.0 <4.0.0` |
| Dart | `>=3.0.0` |
| State Management | `setState` (no provider/bloc/riverpod) |
| HTTP Client | `http: ^1.2.0` |
| Local Storage | `shared_preferences: ^2.2.2` |
| Location | `geolocator: ^11.0.0` + `geocoding: ^3.0.0` |
| Image Picker | `image_picker: ^1.1.1` |
| Date/Time | `intl: ^0.19.0` |
| Islamic Calendar | `hijri: ^3.0.0` |
| Linting | `flutter_lints: ^3.0.0` |

### Backend API (Laravel)
| Technology | Version |
|---|---|
| PHP | `^8.3` |
| Framework | `laravel/framework: ^13.8` |
| API Auth | `laravel/sanctum: ^4.3` (token-based) |
| Web Auth | Session-based (built-in) |
| Spreadsheets | `phpoffice/phpspreadsheet` |
| Frontend Build | Vite `^8.0`, Tailwind CSS `^4.0` |
| Testing | PHPUnit `^12.5` |

### Web Admin Panel
| Technology | Purpose |
|---|---|
| Bootstrap 5.3.3 | UI framework |
| Bootstrap Icons | Icon library |
| DataTables 1.13.8 | Sortable/searchable tables |
| Toastr | Notifications |
| Chart.js 4.4.2 | Dashboard charts |
| jQuery 3.7.1 | DOM manipulation + AJAX |

### Database
| Technology | Version |
|---|---|
| MySQL | 8.x (configured on port 3307) |
| Fallback | SQLite (for local dev) |
| Queue Driver | Database-driven |
| Cache Driver | Database-driven |
| Session Driver | Database-driven |

---

## 4. Project Structure

```
karachi_portal/
│
├── flutter/                          # Mobile App (Flutter)
│   ├── android/                      # Android native platform
│   │   ├── app/
│   │   │   ├── build.gradle.kts      # Android app build config
│   │   │   └── src/                  # Android source
│   │   ├── build.gradle.kts          # Root build config
│   │   ├── gradle.properties         # Gradle properties
│   │   ├── settings.gradle.kts       # Gradle settings
│   │   └── gradle/                   # Gradle wrapper
│   ├── ios/                          # iOS native platform
│   │   ├── Runner/                   # iOS app source
│   │   │   ├── AppDelegate.swift
│   │   │   ├── Assets.xcassets/
│   │   │   └── Info.plist
│   │   ├── Runner.xcodeproj/
│   │   └── Runner.xcworkspace/
│   ├── assets/                       # Static assets
│   │   ├── emblem.png                # Government emblem
│   │   ├── logo.png                  # App logo
│   │   ├── logo1.png                 # Alternate logo
│   │   └── fonts/
│   │       └── NotoNastaliqUrdu-Regular.ttf  # Urdu Nastaliq font
│   ├── lib/                          # MAIN DART SOURCE CODE
│   │   ├── main.dart                 # Entry point, routing, theme, splash
│   │   ├── models/                   # Data models
│   │   │   ├── complaint_model.dart  # Complaint data class
│   │   │   └── price_model.dart      # PriceCategory & PriceItem classes
│   │   ├── pages/                    # Screen pages
│   │   │   ├── login_page.dart       # Login + SignUp (bilingual)
│   │   │   ├── home_page.dart        # Dashboard with stats & quick access
│   │   │   ├── price_list_page.dart  # Price list with categories + search
│   │   │   ├── price_search_page.dart# Price search + trend chart + stats
│   │   │   ├── file_complaint_page.dart # Complaint form (GPS + photos)
│   │   │   ├── my_complaints_page.dart  # User's complaints + detail view
│   │   │   ├── track_complaint_page.dart# Track complaint by number (public)
│   │   │   └── notifications_page.dart  # Notifications + Profile page
│   │   ├── services/                 # API service layer
│   │   │   ├── api_service.dart      # Base HTTP client (GET/POST/PUT/multipart)
│   │   │   ├── auth_service.dart     # Login/Register/Logout/User session
│   │   │   ├── complaint_service.dart# Complaint CRUD + trackComplaint()
│   │   │   ├── price_service.dart    # Price list, search, trend API
│   │   │   └── notification_service.dart  # Notifications (no AnnouncementService)
│   │   └── widgets/
│   │       └── shared_widgets.dart   # AppBar, BottomNav, StatusBadge, SectionHeader
│   ├── test/                         # Unit/widget tests
│   │   └── widget_test.dart
│   ├── web/                          # Web platform
│   ├── windows/                      # Windows platform
│   ├── linux/                        # Linux platform
│   ├── macos/                        # macOS platform
│   ├── pubspec.yaml                  # Project config + dependencies
│   └── analysis_options.yaml         # Dart linter settings
│
├── laravel-api/                      # Backend API + Admin Panel
│   ├── app/
│   │   ├── Exports/
│   │   │   └── PriceItemExport.php   # Excel export of price items (all/per-category)
│   │   ├── Imports/
│   │   │   └── PriceItemImport.php   # Excel import with validation + auto-create
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Controller.php    # Base controller with denyIfViewer()
│   │   │   │   └── API/
│   │   │   │       ├── AuthController.php          # Register/Login/Logout/Profile/Admin users
│   │   │   │       ├── ComplaintController.php     # List/Store/Show/Stats/Track/Admin CRUD
│   │   │   │       ├── PriceController.php         # List/Categories/Search/Trend/Admin CRUD
│   │   │   │       └── NotificationController.php  # List/MarkRead/Broadcast
│   │   │   └── Middleware/
│   │   │       ├── AdminMiddleware.php     # API admin guard (checks role admin/staff)
│   │   │       └── AdminWebMiddleware.php  # Web admin guard (checks role admin/staff)
│   │   ├── Models/
│   │   │   ├── User.php               # User with isAdmin() and isViewer() helpers
│   │   │   ├── Complaint.php          # Complaint with status scopes
│   │   │   ├── ComplaintPicture.php   # Complaint photo attachments
│   │   │   ├── PriceCategory.php      # Price categories with items relation
│   │   │   ├── PriceItem.php          # Price items with category + history
│   │   │   ├── PriceHistory.php       # Historical price records
│   │   │   ├── PriceUpdateLog.php     # Price change audit log
│   │   │   └── UserNotification.php   # In-app user notifications
│   │   └── Providers/
│   │       └── AppServiceProvider.php
│   ├── bootstrap/
│   │   └── app.php                    # App bootstrap (middleware aliases, exception handling)
│   ├── config/
│   │   ├── app.php                    # App name, locale, timezone, debug
│   │   ├── auth.php                   # Auth guards (web session)
│   │   ├── cache.php                  # Cache config (database)
│   │   ├── database.php               # MySQL, SQLite, PostgreSQL, Redis connections
│   │   ├── filesystems.php            # Local, public, S3 disks
│   │   ├── logging.php                # Log channels
│   │   ├── mail.php                   # Mail config (log driver)
│   │   ├── queue.php                  # Database queue driver
│   │   ├── sanctum.php                # Sanctum stateful domains, expiration
│   │   ├── services.php               # Third-party services
│   │   └── session.php                # Database session driver
│   ├── database/
│   │   ├── database.sqlite            # SQLite dev database
│   │   ├── factories/
│   │   │   └── UserFactory.php
│   │   ├── migrations/
│   │   │   ├── 0001_01_01_000000_create_users_table.php
│   │   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   │   ├── 2024_01_01_000002_create_complaints_table.php
│   │   │   ├── 2024_01_01_000003_create_complaint_pictures_table.php
│   │   │   ├── 2024_01_01_000004_create_price_categories_table.php
│   │   │   ├── 2024_01_01_000005_create_price_items_table.php
│   │   │   ├── 2024_01_01_000006_create_price_histories_table.php
│   │   │   ├── 2024_01_01_000007_create_user_notifications_table.php
│   │   │   ├── 2026_05_13_212350_create_personal_access_tokens_table.php
│   │   │   ├── 2026_05_29_000001_create_price_update_logs_table.php
│   │   │   └── 2026_05_29_000007_drop_announcements_table.php
│   │   └── seeders/
│   │       └── DatabaseSeeder.php     # Admin + Demo users, 9 categories, 45 items
│   ├── resources/
│   │   ├── css/app.css
│   │   ├── js/app.js
│   │   └── views/                     # BLADE TEMPLATES
│   │       ├── layouts/
│   │       │   └── app.blade.php      # Admin layout (sidebar with viewer-aware links)
│   │       ├── welcome.blade.php
│   │       ├── auth/
│   │       │   └── login.blade.php    # Admin login page
│   │       ├── admin/
│   │       │   └── change-password.blade.php
│   │       ├── dashboard/
│   │       │   └── index.blade.php    # Dashboard with charts + stats
│   │       ├── complaints/
│   │       │   ├── index.blade.php    # Complaints list with filters
│   │       │   └── show.blade.php     # Complaint detail + status update (admin-only)
│   │       ├── prices/
│   │       │   ├── categories.blade.php  # Category CRUD (viewer-safe)
│   │       │   ├── items.blade.php       # Items CRUD with inline edit (viewer-safe)
│   │       │   ├── bulk.blade.php        # Bulk update + import (viewer-safe)
│   │       │   └── update-logs.blade.php # Price update log viewer
│   │       ├── notifications/
│   │       │   └── index.blade.php    # Broadcast notification (admin-only)
│   │       ├── trash/
│   │       │   └── index.blade.php    # Recycle bin (admin-only)
│   │       └── users/
│   │           ├── index.blade.php    # Citizens list
│   │           └── show.blade.php     # Citizen detail
│   ├── routes/
│   │   ├── api.php                    # ALL API routes (public, protected, admin)
│   │   ├── web.php                    # Web admin panel routes
│   │   └── console.php                # Artisan commands
│   ├── storage/                       # Laravel storage (sessions, views, cache, logs)
│   ├── tests/                         # PHPUnit tests
│   │   ├── TestCase.php
│   │   ├── Feature/ExampleTest.php
│   │   └── Unit/ExampleTest.php
│   ├── public/                        # Web root
│   │   ├── index.php                  # Front controller
│   │   ├── .htaccess
│   │   ├── storage/                   # Symlink to storage/app/public
│   │   └── logo.png / logo1.png
│   ├── .env.example                   # Environment template
│   ├── composer.json                  # PHP dependencies
│   ├── package.json                   # Node dependencies (Vite, Tailwind)
│   ├── vite.config.js                 # Vite build config
│   └── artisan                        # Laravel CLI
│
├── README.md                          # Quick-start guide
├── DOCUMENTATION.md                   # This file
├── icon.txt                           # Flutter app icon snippet
├── link.txt                           # Google Sheets link for pricing
└── overflow.txt                       # Debug render overflow log
```

---

## 5. Mobile App (Flutter)

### 5.1 Entry Point — `lib/main.dart`

Sets up `MaterialApp` with:
- **Theme:** Dark green (`#1A5C38`) primary, light green (`#F0F4F1`) background
- **Routes:** Named routes for all pages + dynamic route for complaint detail
- **Splash Screen:** 2-second branded screen with logo, auto-login check via `AuthService.isLoggedIn()`

### 5.2 Pages

#### `login_page.dart` — Login + SignUp
- **Login form:** Username/CNIC/Mobile + password, "Remember Me" checkbox
- **SignUp form:** Full Name, CNIC (13-digit validation), Mobile, Password, Confirm Password
- Custom painters for Karachi skyline and Mazar-e-Quaid silhouette
- Bilingual labels throughout (English + Urdu Nastaliq)

#### `home_page.dart` — Dashboard
- Welcome card with user name + Gregorian/Hijri date
- 6 quick-access buttons: My Complaints, New Complaint, Price List, Notifications, **Track Complaint**, Help
- Stats row: Total, Pending, In Progress, Resolved counts
- Recent complaints list (last 3, tap to view detail)
- Bottom navigation with 5 tabs + notification badge
- Pull-to-refresh data loading

#### `price_list_page.dart` — Price List
- Category chips (horizontal scroll): All Items, Grains, Pulses, Cooking Oil, etc.
- Search bar with filter button
- Update banner showing last updated timestamp
- Sortable table: Item name (with Urdu), Unit, Price (Rs.), Change (%)
- Color-coded price changes (red = up, green = down)
- Tap row to view price search detail page
- Disclaimer card (indicative pricing)

#### `price_search_page.dart` — Price Search
- Search bar with submit button
- Popular searches (Rice, Wheat Flour, Sugar, Cooking Oil, Onion)
- Result card with image, name, current price, change indicator
- **Trend chart:** Custom-painted line chart with fill area, dots, price labels
- Period dropdown: Last 7 Days / 30 Days / 3 Months
- Price stats: Average, Highest (with date), Lowest (with date)
- Info note about indicative pricing

#### `file_complaint_page.dart` — File Complaint
- Pre-filled user info (name, CNIC, mobile) from auth
- Fields: Full Name, CNIC, Mobile, Item Name, Shop Name
- **GPS Location Capture:** Uses `geolocator` + `geocoding` for reverse geocode
- **Photo Upload:** Up to 5 pictures via `image_picker`, removable thumbnails
- Complaint details textarea (max 500 chars with counter)
- Submit button with loading state, success dialog with complaint number
- Security disclaimer card

#### `my_complaints_page.dart` — My Complaints
- Filter tabs: All, Pending, In Process, Resolved
- Complaint cards with: complaint number, item name, shop name, location, status badge, date
- Infinite scroll pagination (10 per page)
- Empty state with "File a Complaint" button
- Floating action button for new complaint

#### `track_complaint_page.dart` — Track Complaint (Public)
- Search card with complaint number input field
- **No login required** — public API endpoint
- Result card showing: complaint number, status badge, name, CNIC, mobile, item, shop, location, description
- Error state if complaint number not found

#### `notifications_page.dart` — Notifications + Profile
- **Notifications tab:** List with type-based icons (complaint_update, price_alert, announcement, system), read/unread state, "Mark all read" button
- **Profile tab:** User info display (name, username, CNIC, mobile, email), menu items (Change Password, Track Complaint, Help, About), Logout button with confirmation dialog

### 5.3 Services

#### `api_service.dart` — Base HTTP Client
- Auto-detection: Android emulator → `10.0.2.2:8000`, else → `127.0.0.1:8000`
- Token management via `SharedPreferences`
- Methods: `get()`, `post()`, `put()`, `postMultipart()`
- Generic error handling (SocketException, 401 auto-logout, server errors)
- All responses normalized to `{'success': bool, ...}`

#### `auth_service.dart` — Authentication
- `login(username, password)` → saves token + user data
- `register(data)` → saves token + user data
- `logout()` → clears token + server-side token revoke
- `getCurrentUser()` → cached user data from SharedPreferences
- `isLoggedIn()` → checks if token exists

#### `complaint_service.dart` — Complaints API
- `getMyComplaints()` with optional status filter + pagination
- `getComplaintDetail(id)`
- `fileComplaint()` with multipart fields + pictures
- `getDashboardStats()`
- `trackComplaint(number)` — public API for tracking by complaint number

#### `price_service.dart` — Prices API
- `getCategories()`
- `getPriceList()` with optional category + search + pagination
- `getPriceDetail(id)`
- `getPriceTrend(id, period)` — 7days/30days/3months
- `searchItem(query)`

#### `notification_service.dart` — Notifications
- `getNotifications()`
- `markAsRead(id)`, `markAllAsRead()`
- `getUnreadCount()`

*(AnnouncementService removed — Announcement feature fully decommissioned)*

### 5.4 Widgets

#### `shared_widgets.dart` — Reusable Components
- **`KarachiAppBar`:** Government emblem + title + notification bell with badge + profile icon
- **`KarachiBottomNav`:** 5-tab bottom nav (Dashboard, Complaint, Price List, Notifications, Profile) with bilingual labels, notification badge
- **`SectionHeader`:** Section title (English + Urdu) with optional action link
- **`StatusBadge`:** Color-coded status pill (Pending=yellow, In Progress=blue, Resolved=green, Rejected=red) with English + Urdu text

### 5.5 Models

#### `complaint_model.dart`
```dart
ComplaintModel {
  int id, String complaintNumber, fullName, cnic, mobile, itemName,
  shopName, locationAddress, details, status, adminRemarks?,
  double? latitude, longitude, List<String> pictures,
  String createdDate, submittedAt
}
```

#### `price_model.dart`
```dart
PriceCategory { String slug, name, urdu, icon, int count }
PriceItem {
  int id, String name, nameUrdu?, unit, unitUrdu?, categorySlug?, imageUrl?,
  double price, previousPrice, priceChange, changePercent
}
```

---

## 6. Backend API (Laravel)

### 6.1 Controllers

#### `AuthController.php`
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `register()` | `POST /api/auth/register` | Public | Register new citizen |
| `login()` | `POST /api/auth/login` | Public | Login by username/CNIC/mobile |
| `logout()` | `POST /api/auth/logout` | Sanctum | Revoke current token |
| `me()` | `GET /api/auth/me` | Sanctum | Current user info |
| `updateProfile()` | `PUT /api/auth/profile` | Sanctum | Update name/mobile/email |
| `changePassword()` | `PUT /api/auth/change-password` | Sanctum | Change password (revokes old tokens) |
| `adminListUsers()` | `GET /api/admin/users` | Admin | List users (searchable by name/CNIC) |
| `adminShowUser()` | `GET /api/admin/users/{id}` | Admin | Single user detail |

#### `ComplaintController.php`
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `index()` | `GET /api/complaints` | Sanctum | User's complaints (paginated, filterable) |
| `store()` | `POST /api/complaints` | Sanctum | File complaint (multipart with up to 5 pics) |
| `show()` | `GET /api/complaints/{id}` | Sanctum | Complaint detail |
| `stats()` | `GET /api/complaints/stats` | Sanctum | User's complaint stats |
| `track()` | `GET /api/complaints/track/{complaintNumber}` | Public | Track complaint by number |
| `adminIndex()` | `GET /api/admin/complaints` | Admin | All complaints (filterable by status, search, date) |
| `updateStatus()` | `PUT /api/admin/complaints/{id}/status` | Admin | Update status + admin remarks |
| `adminStats()` | `GET /api/admin/dashboard/stats` | Admin | Dashboard statistics |

Complaint number format: `CMPL-YYYY-XXXXX` (year + sequential increment)

#### `PriceController.php`
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `categories()` | `GET /api/prices/categories` | Public | Active categories with item count |
| `index()` | `GET /api/prices` | Public | Price list (filterable by category + search) |
| `search()` | `GET /api/prices/search?q=` | Public | Single item search |
| `show()` | `GET /api/prices/{id}` | Public | Single item detail |
| `trend()` | `GET /api/prices/{id}/trend?period=` | Public | Price history trend (7/30/90 days) |
| `adminCategories()` | `GET /api/admin/price-categories` | Admin | All categories (with item count) |
| `storeCategory()` | `POST /api/admin/price-categories` | Admin | Create category |
| `updateCategory()` | `PUT /api/admin/price-categories/{id}` | Admin | Update category |
| `destroyCategory()` | `DELETE /api/admin/price-categories/{id}` | Admin | Delete category (must have no items) |
| `adminItems()` | `GET /api/admin/price-items` | Admin | All items (filterable by category) |
| `storeItem()` | `POST /api/admin/price-items` | Admin | Create item + log initial price history |
| `updateItem()` | `PUT /api/admin/price-items/{id}` | Admin | Update item + auto-log price change |
| `destroyItem()` | `DELETE /api/admin/price-items/{id}` | Admin | Delete item |
| `bulkUpdate()` | `POST /api/admin/prices/bulk-update` | Admin | Bulk update prices (transactional) |

#### `NotificationController.php`
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `index()` | `GET /api/notifications` | Sanctum | User's notifications |
| `unreadCount()` | `GET /api/notifications/unread-count` | Sanctum | Unread notification count |
| `markRead()` | `POST /api/notifications/{id}/read` | Sanctum | Mark single as read |
| `markAllRead()` | `POST /api/notifications/read-all` | Sanctum | Mark all as read |
| `broadcast()` | `POST /api/admin/notifications/broadcast` | Admin | Broadcast to all citizens (chunked insert) |

*(AnnouncementController removed — Announcement feature fully decommissioned)*

### 6.2 Middleware

#### `AdminMiddleware.php` (API)
- Checks `$request->user()->role` is `admin` or `staff`
- Returns 403 JSON response if unauthorized
- Applied via `auth:sanctum` + `admin` middleware alias

#### `AdminWebMiddleware.php` (Web)
- Checks `Auth::check()` and `Auth::user()->role` is `admin` or `staff`
- Redirects to admin login page if unauthorized
- Logs out users with invalid roles

### 6.3 Bootstrap (`bootstrap/app.php`)
- Registers middleware aliases: `admin`, `admin.web`
- Enables stateful Sanctum API
- Redirects guests to admin login
- Custom exception handling for:
  - Authentication exception → 401 JSON
  - Model not found → 404 JSON
  - Validation exception → 422 JSON

### 6.4 Web Admin Controllers (in `app/Http/Controllers/Admin/`)

| Controller | Purpose |
|---|---|
| `AdminAuthController` | Login, logout, change password |
| `DashboardController` | Dashboard stats with charts |
| `ComplaintAdminController` | List, show, update status, export CSV |
| `PriceAdminController` | Categories, items, bulk update, import/export Excel |
| `NotificationAdminController` | List, broadcast |
| `PriceUpdateLogAdminController` | View price update logs |
| `UserAdminController` | List, show |
| `TrashController` | Restore/forceDelete soft-deleted records |

### 6.5 Web Routes (`routes/web.php`)

All admin routes are under `/admin/` prefix and protected by `auth` + `admin.web` middleware.

| URL | Method | Controller Method | Name |
|---|---|---|---|
| `/admin/login` | GET/POST | `AdminAuthController` | `admin.login` |
| `/admin/logout` | POST | `AdminAuthController::logout` | `admin.logout` |
| `/admin/change-password` | GET/POST | `AdminAuthController` | `admin.change-password` |
| `/admin/dashboard` | GET | `DashboardController::index` | `admin.dashboard` |
| `/admin/complaints` | GET | `ComplaintAdminController::index` | `admin.complaints.index` |
| `/admin/complaints/export` | GET | `ComplaintAdminController::export` | `admin.complaints.export` |
| `/admin/complaints/{id}` | GET | `ComplaintAdminController::show` | `admin.complaints.show` |
| `/admin/complaints/{id}/status` | PUT | `ComplaintAdminController::updateStatus` | `admin.complaints.update-status` |
| `/admin/prices/categories` | GET/POST | `PriceAdminController` | `admin.prices.categories.*` |
| `/admin/prices/categories/{id}` | PUT/DELETE | `PriceAdminController` | `admin.prices.categories.*` |
| `/admin/prices/items` | GET/POST | `PriceAdminController` | `admin.prices.items.*` |
| `/admin/prices/items/{id}` | PUT/DELETE | `PriceAdminController` | `admin.prices.items.*` |
| `/admin/prices/bulk` | GET | `PriceAdminController::bulkView` | `admin.prices.bulk` |
| `/admin/prices/bulk-update` | POST | `PriceAdminController::bulkUpdate` | `admin.prices.bulk.update` |
| `/admin/prices/export` | GET | `PriceAdminController::exportPrices` | `admin.prices.export` |
| `/admin/prices/import/preview` | POST | `PriceAdminController::importPreview` | `admin.prices.import.preview` |
| `/admin/prices/import/process` | POST | `PriceAdminController::importProcess` | `admin.prices.import.process` |
| `/admin/notifications` | GET | `NotificationAdminController::index` | `admin.notifications.index` |
| `/admin/notifications/broadcast` | POST | `NotificationAdminController::broadcast` | `admin.notifications.broadcast` |
| `/admin/users` | GET | `UserAdminController::index` | `admin.users.index` |
| `/admin/users/{id}` | GET | `UserAdminController::show` | `admin.users.show` |
| `/admin/price-update-logs` | GET | `PriceUpdateLogAdminController::index` | `admin.price-update-logs.index` |
| `/admin/price-update-logs/export` | GET | `PriceUpdateLogAdminController::exportCsv` | `admin.price-update-logs.export` |
| `/admin/trash` | GET | `TrashController::index` | `admin.trash.index` |
| `/admin/trash/restore/{type}/{id}` | POST | `TrashController::restore` | `admin.trash.restore` |
| `/admin/trash/force-delete/{type}/{id}` | POST | `TrashController::forceDelete` | `admin.trash.force-delete` |

---

## 7. Web Admin Panel (Blade)

### 7.1 Layout (`layouts/app.blade.php`)
- **Sidebar:** Fixed left sidebar (260px) with dark green background
  - Brand header with logo
  - Navigation sections: Main, Complaints, Price Management, Price Update Logs, Communication (admin only), Users, System (admin only)
  - Viewer role sees only Dashboard, Complaints, Categories, Price Items, Price Update Logs, Citizens
  - Active link highlighting, pending complaint count badge
  - Footer: Admin name, logout button
- **Topbar:** Sticky top bar with page title, pending complaints bell, current date
- **Flash messages:** Success/error alerts (auto-dismiss)
- **CDN Scripts:** Bootstrap 5, jQuery, DataTables, Toastr, Chart.js
- **Responsive:** Sidebar collapses on mobile with toggle button

### 7.2 Login Page (`auth/login.blade.php`)
- Full-screen gradient green background
- Centered login card with logo
- Username + password fields with icons + password toggle
- CSRF protection

### 7.3 Dashboard (`dashboard/index.blade.php`)
- **Stat Cards:** Total, Pending (orange), In Progress (blue), Resolved (green) with gradient backgrounds
- **Secondary Stats:** Today's complaints, This month, Registered citizens, Price items listed
- **Monthly Chart:** Bar chart (Chart.js) — last 6 months
- **Status Donut:** Doughnut chart — Pending/In Progress/Resolved/Rejected breakdown with legend badges
- **Recent Price Updates:** Table with item, category, old/new price, change %, source, updater, time
- **Recent Complaints Table:** Complaint #, Item, Shop, Status badge, Date — clickable rows
- **Most Complained Items:** Horizontal bar list with progress bars

### 7.4 Complaints List (`complaints/index.blade.php`)
- **Filter bar:** Status dropdown, Date From/To, Search text input (name, CNIC, item, shop, complaint#)
- **Status pills:** All, Pending, In Progress, Resolved, Rejected with counts
- **Data table:** Complaint #, Citizen (name + mobile), Item/Shop, Location, Status badge, Date, Action (view button)
- **Export CSV:** Download filtered results
- **Pagination:** Bootstrap 5 pagination with showing X-Y of Z

### 7.5 Complaint Detail (`complaints/show.blade.php`)
- **Left column:** Complaint header (number, date, status), Complainant details (name, CNIC, mobile, linked registered user), Complaint details (item, shop, location with Google Maps link, description)
- **Pictures:** Thumbnail gallery (click to open in new tab)
- **Right column:** Status update form (admin-only — hidden for viewer), embedded Google Maps iframe, timeline (Complaint Filed → Status Updated → Awaiting Resolution), Back button

### 7.6 Price Items (`prices/items.blade.php`)
- **Category filter:** Pills with emoji icons + item counts
- **Data table:** #, Item Name, Urdu Name, Category badge, Unit, Current Price (view-only for viewer), Change % with arrows, Status badge (Active/Inactive), Actions (hidden for viewer)
- **Inline price edit (admin only):** `startEdit()` / `cancelEdit()` / `savePrice()` via AJAX + toastr
- **Add Item / Bulk Update buttons (admin only):** Hidden for viewer
- **Delete:** Confirmation dialog + AJAX row removal

### 7.7 Other Views
- `prices/categories.blade.php` — Category CRUD (Add/Edit/Delete hidden for viewer)
- `prices/bulk.blade.php` — Tabular bulk update + Excel import section (Save/Import hidden for viewer)
- `prices/update-logs.blade.php` — Price update log table with export CSV
- `notifications/index.blade.php` — Broadcast notification form (admin only)
- `trash/index.blade.php` — Recycle bin with Restore/Delete Forever buttons (admin only)
- `users/index.blade.php` — Citizens list with search
- `users/show.blade.php` — Citizen detail with their complaints
- `admin/change-password.blade.php` — Admin password change form (both roles)

---

## 8. Database Schema

### 8.1 Application Tables

#### `users`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| name | VARCHAR(255) | NOT NULL |
| username | VARCHAR(255) | UNIQUE, NOT NULL |
| cnic | VARCHAR(15) | UNIQUE, NOT NULL |
| mobile | VARCHAR(15) | UNIQUE, NOT NULL |
| email | VARCHAR(255) | NULLABLE, UNIQUE |
| email_verified_at | TIMESTAMP | NULLABLE |
| password | VARCHAR(255) | NOT NULL |
| role | ENUM('citizen','admin','staff') | DEFAULT 'citizen' |
| remember_token | VARCHAR(100) | NULLABLE |
| created_at / updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP | NULLABLE (soft deletes) |

#### `complaints`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| user_id | BIGINT UNSIGNED | FK → users.id, CASCADE DELETE |
| complaint_number | VARCHAR(20) | UNIQUE |
| full_name | VARCHAR(255) | |
| cnic | VARCHAR(15) | |
| mobile | VARCHAR(15) | |
| item_name | VARCHAR(255) | |
| shop_name | VARCHAR(255) | |
| latitude | DECIMAL(10,7) | |
| longitude | DECIMAL(10,7) | |
| location_address | VARCHAR(255) | |
| details | TEXT | |
| status | ENUM('pending','in_progress','resolved','rejected') | DEFAULT 'pending' |
| admin_remarks | TEXT | NULLABLE |
| created_at / updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP | NULLABLE (soft deletes) |
| Indexes: | (user_id, status), (created_at) | |

#### `complaint_pictures`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| complaint_id | BIGINT UNSIGNED | FK → complaints.id, CASCADE DELETE |
| path | VARCHAR(255) | |
| url | VARCHAR(255) | |
| created_at / updated_at | TIMESTAMP | |

#### `price_categories`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| name | VARCHAR(100) | |
| name_urdu | VARCHAR(100) | NULLABLE |
| slug | VARCHAR(50) | UNIQUE |
| icon | VARCHAR(255) | DEFAULT '📦', NULLABLE |
| sort_order | INTEGER | DEFAULT 0 |
| is_active | BOOLEAN | DEFAULT TRUE |
| created_at / updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP | NULLABLE (soft deletes) |

#### `price_items`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| price_category_id | BIGINT UNSIGNED | FK → price_categories.id, CASCADE DELETE |
| name | VARCHAR(100) | |
| name_urdu | VARCHAR(100) | NULLABLE |
| unit | VARCHAR(20) | DEFAULT '1 Kg' |
| unit_urdu | VARCHAR(20) | NULLABLE |
| price | DECIMAL(10,2) | DEFAULT 0 |
| previous_price | DECIMAL(10,2) | DEFAULT 0 |
| price_change | DECIMAL(10,2) | DEFAULT 0 |
| change_percent | DECIMAL(8,2) | DEFAULT 0 |
| image_url | VARCHAR(255) | NULLABLE |
| sort_order | INTEGER | DEFAULT 0 |
| is_active | BOOLEAN | DEFAULT TRUE |
| created_at / updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP | NULLABLE (soft deletes) |
| Index: | (price_category_id, is_active) | |

#### `price_histories`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| price_item_id | BIGINT UNSIGNED | FK → price_items.id, CASCADE DELETE |
| price | DECIMAL(10,2) | |
| recorded_at | TIMESTAMP | |
| Index: | (price_item_id, recorded_at) | |
| Note: | No timestamps (uses `recorded_at`) | |

#### `price_update_logs`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| price_item_id | BIGINT UNSIGNED | FK → price_items.id |
| updated_by | BIGINT UNSIGNED | FK → users.id |
| old_price | DECIMAL(10,2) | |
| new_price | DECIMAL(10,2) | |
| change | DECIMAL(10,2) | |
| change_percent | DECIMAL(8,2) | |
| source | VARCHAR(20) | 'single' or 'bulk' |
| created_at / updated_at | TIMESTAMP | |

#### `user_notifications`
| Column | Type | Constraints |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| user_id | BIGINT UNSIGNED | FK → users.id |
| type | VARCHAR(50) | e.g., complaint_update, price_alert, announcement, system |
| title | VARCHAR(255) | |
| body | TEXT | NULLABLE |
| data | JSON | NULLABLE |
| read_at | TIMESTAMP | NULLABLE |
| created_at / updated_at | TIMESTAMP | |

*(`announcements` table removed in v1.1.0)*

### 8.2 Laravel System Tables

| Table | Purpose |
|---|---|
| `personal_access_tokens` | Sanctum API token storage |
| `cache` / `cache_locks` | Database cache driver |
| `jobs` / `job_batches` / `failed_jobs` | Queue system |
| `sessions` | Database session driver |
| `password_reset_tokens` | Password reset |

### 8.3 Entity Relationship Diagram (Text)

```
users ──┬── complaints ──┬── complaint_pictures
        │                │
        │                ├── user_notifications
        │                │
        │                └── price_update_logs (updated_by)
        │
        └── (creator of notifications)

price_categories ──┬── price_items ──┬── price_histories
                   │                 │
                   └── (category_id) │
                                     └── price_update_logs (price_item_id)
```

---

## 9. API Reference

### Base URL
```
http://localhost:8000/api
```
For Android emulator: `http://10.0.2.2:8000/api`

### Response Format
```json
// Success
{ "success": true, "data": {...}, "message": "..." }

// Error
{ "success": false, "message": "...", "errors": {...} }

// Unauthenticated
{ "success": false, "message": "Unauthenticated. Please login." }

// Unauthorized (admin)
{ "success": false, "message": "Unauthorized. Admin access required." }

// Viewer forbidden
{ "message": "Viewers cannot modify data." } (HTTP 403)
```

### Public Endpoints

#### Authentication
- **`POST /api/auth/register`**
  - Body: `{ name, cnic (42101-1234567-1), mobile, password, password_confirmation, username? }`
  - Response: `{ token, user: { id, name, username, cnic, mobile, email, role } }`

- **`POST /api/auth/login`**
  - Body: `{ username, password }` (username can be username, CNIC, or mobile)
  - Response: `{ token, user }`

#### Prices
- **`GET /api/prices`** — List with optional `?category=grains&search=rice&page=1`
- **`GET /api/prices/categories`** — All active categories with item counts
- **`GET /api/prices/search?q=rice`** — Single item search
- **`GET /api/prices/{id}`** — Single item detail
- **`GET /api/prices/{id}/trend?period=7days|30days|3months`** — Price history + stats

#### Track Complaint
- **`GET /api/complaints/track/{complaintNumber}`** — Public complaint tracking by number (e.g., CMPL-2026-00001)
  - Response: `{ success: true, data: { complaint_number, full_name, cnic, mobile, item_name, shop_name, location_address, details, status, created_at } }`

### Protected Endpoints (Sanctum Token)

**Header:** `Authorization: Bearer {token}`

#### Auth
- **`POST /api/auth/logout`** — Revoke current token
- **`GET /api/auth/me`** — Current user info
- **`PUT /api/auth/profile`** — Update `name`, `mobile`, `email`
- **`PUT /api/auth/change-password`** — `{ current_password, password, password_confirmation }`

#### Complaints
- **`GET /api/complaints?status=pending&page=1`** — User's complaints
- **`POST /api/complaints`** — Multipart: fields + `pictures[0..4]` (max 5 images)
- **`GET /api/complaints/stats`** — `{ total, pending, in_progress, resolved }`
- **`GET /api/complaints/{id}`** — Complaint detail with pictures

#### Notifications
- **`GET /api/notifications`** — User's notifications (paginated)
- **`GET /api/notifications/unread-count`** — `{ count }`
- **`POST /api/notifications/{id}/read`** — Mark single as read
- **`POST /api/notifications/read-all`** — Mark all as read

### Admin Endpoints (Sanctum + Admin Middleware)

**Headers:** `Authorization: Bearer {admin_token}`

#### Users
- **`GET /api/admin/users?search=&role=`** — List all users
- **`GET /api/admin/users/{id}`** — Single user detail

#### Complaints
- **`GET /api/admin/complaints?status=&search=&date_from=&date_to=`** — All complaints
- **`PUT /api/admin/complaints/{id}/status`** — `{ status, admin_remarks? }`
- **`GET /api/admin/dashboard/stats`** — Dashboard statistics

#### Price Management
- **`GET|POST /api/admin/price-categories`** — List/Create
- **`PUT|DELETE /api/admin/price-categories/{id}`** — Update/Delete
- **`GET|POST /api/admin/price-items`** — List/Create
- **`PUT|DELETE /api/admin/price-items/{id}`** — Update/Delete
- **`POST /api/admin/prices/bulk-update`** — `{ items: [{ id, price }] }`

#### Notifications
- **`POST /api/admin/notifications/broadcast`** — `{ title, body, type }` (sent to all citizens)

---

## 10. Authentication & Authorization

### Mobile App (Sanctum Token)
1. User registers or logs in → receives `plainTextToken`
2. Token stored in `SharedPreferences` as `auth_token`
3. Token sent as `Authorization: Bearer {token}` header
4. On login: old tokens are revoked → single-session enforcement
5. On 401 response: token auto-cleared, user redirected to login
6. On password change: old tokens revoked, new token issued

### Web Admin Panel (Session)
1. Admin/Viewer logs in via `/admin/login` → session created
2. Protected by `auth` (Laravel session guard) + `admin.web` middleware
3. Session stored in database (`sessions` table)
4. Any role other than `admin` or `staff` rejected at login
5. Logout → session cleared

### Role System
- `citizen`: Default role, access to user endpoints only
- `admin`: Full access — all admin endpoints + web panel CRUD
- `staff` **(Viewer):** Read-only access to admin panel
  - Can view Dashboard, Complaints, Categories, Price Items, Price Update Logs, Citizens
  - All Add/Edit/Delete/Save/Import/Broadcast/Restore buttons hidden in UI
  - Server-side `denyIfViewer()` returns HTTP 403 on any POST/PUT/DELETE mutation
  - Login allowed via `AdminWebMiddleware` and `AdminMiddleware` (both allow admin + staff)
  - `AuditLog` and `PriceUpdateLog` visible for reference

---

## 11. Demo Credentials

| Role | Username | Password | CNIC | Mobile |
|---|---|---|---|---|
| Admin (Full Access) | `admin` | `Admin@12345` | 42201-0000000-1 | 03000000000 |
| Viewer (Read-Only) | `viewer` | `Viewer@12345` | 42201-9999999-1 | 03009999999 |
| Citizen (App) | `demo_citizen` | `Demo@1234` | 42201-1234567-1 | 03001234567 |

---

## 12. Setup & Installation

### Backend (Laravel)

```bash
cd laravel-api

# Install PHP dependencies
composer install

# Environment setup
cp .env.example .env
# Edit .env: set DB credentials, APP_URL, etc.

# Generate app key
php artisan key:generate

# Create database & run migrations
php artisan migrate

# Seed demo data
php artisan db:seed

# Create storage link
php artisan storage:link

# Install npm dependencies
npm install

# Build assets
npm run build

# Start development server
php artisan serve
```

### Mobile App (Flutter)

```bash
cd flutter

# Install dependencies
flutter pub get

# Update API base URL in lib/services/api_service.dart
# (Android emulator auto-detects 10.0.2.2:8000)

# Run on connected device/emulator
flutter run
```

### Build APK
```bash
cd flutter
flutter build apk --debug
# Output: build/app/outputs/flutter-apk/app-debug.apk
```

### Environment Variables (.env)

```
APP_NAME="Commissioner Karachi Portal"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=karachi_portal
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

---

## 13. Seeded Data

### 9 Price Categories
1. Grains (🌾) — 5 items
2. Pulses (🫘) — 4 items
3. Cooking Oil (🫙) — 5 items
4. Sugar & Salt (🧂) — 4 items
5. Vegetables (🥦) — 9 items
6. Fruits (🍎) — 5 items
7. Dairy (🥛) — 4 items
8. Meat & Poultry (🍗) — 5 items
9. Spices (🌶️) — 5 items

### Sample Items (45 total)
Includes Rice, Wheat Flour, Onion, Tomato, Potato, Chicken, Beef, Mutton, Eggs, Milk, Cooking Oil, Sugar, Salt, etc. Each has English + Urdu name, unit, current price, previous price, and calculated change/percent.

### Price History
Each seeded item gets 7 days of price history with randomized variance for realistic trend charts.

### 2 System Users
- **Admin User** (`admin` / `Admin@12345`) — Full admin access
- **Demo Citizen** (`demo_citizen` / `Demo@1234`) — Standard citizen account with sample complaints

---

## 14. Recent Changes (v1.1.0)

### Removed
- **Announcement feature fully decommissioned:**
  - `app/Models/Announcement.php` deleted
  - `app/Http/Controllers/API/AnnouncementController.php` deleted
  - `app/Http/Controllers/Admin/AnnouncementAdminController.php` deleted
  - `resources/views/announcements/` directory deleted
  - `AnnouncementSeeder` removed from `DatabaseSeeder.php`
  - Migration `2026_05_29_000007_drop_announcements_table.php` drops the table
  - All announcement routes removed from `web.php` and `api.php`
  - `AnnouncementService` removed from Flutter `notification_service.dart`
  - Announcements links removed from Flutter home page and notifications page

### Added
- **Track Complaint (Public API):**
  - `GET /api/complaints/track/{complaintNumber}` — No auth required
  - `Flutter` → `pages/track_complaint_page.dart` with input + result card
  - `services/complaint_service.dart` → `trackComplaint()` method
  - Quick access button replaces Announcements in home page

- **Excel Import/Export for Prices:**
  - `app/Exports/PriceItemExport.php` — Download all or per-category price list (.xlsx)
  - `app/Imports/PriceItemImport.php` — Parse, validate, preview, and process Excel files
  - Supports updating existing items and auto-creating new items (when `category_slug` provided)
  - Blank price → 0 (no validation error)
  - Import UI merged into `bulk.blade.php` (Preview + Confirm flow)

- **Read-Only Viewer Role (`staff`):**
  - `User::isAdmin()` and `User::isViewer()` helper methods
  - `AdminWebMiddleware` and `AdminMiddleware` now accept both `admin` and `staff`
  - `denyIfViewer()` on `Controller.php` — called in all mutating controller methods
  - All Blade views hide action buttons for viewer (Add/Edit/Delete/Save/Import/Broadcast/Restore)
  - Sidebar hides Bulk Update/Import, Notifications, Recycle Bin for viewer

- **Soft Deletes & Recycle Bin:**
  - `TrashController` with `index()` / `restore()` / `forceDelete()`
  - Supports user, complaint, price_category, price_item soft deletes
  - Routes under `/admin/trash/`

- **Price Update Logs:**
  - `PriceUpdateLog` model + migration
  - Logged on both single and bulk price updates
  - `PriceUpdateLogAdminController` with listing + CSV export
  - Dashboard shows recent price updates

### Changed
- `AdminAuthController::login()` — now accepts `staff` role for viewer login
- `ComplaintAdminController::updateStatus()` — protected by `denyIfViewer()`
- `NotificationAdminController::broadcast()` — protected by `denyIfViewer()`
- `PriceAdminController/TrashController` — all mutating methods protected
- `layouts/app.blade.php` sidebar — conditional links based on `isAdmin()`
- `bulk.blade.php` — Import section merged, viewer-safe buttons
- `complaints/show.blade.php` — Status update form hidden for viewer
- `api.php` — Announcement routes removed
- `web.php` — Announcement routes removed, import/export/trash added
