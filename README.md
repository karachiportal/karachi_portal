# Commissioner Karachi Portal — Full Project

## Flutter App Structure
```
flutter/
├── lib/
│   ├── main.dart                          ← Entry point + routing + splash screen
│   ├── pages/
│   │   ├── login_page.dart                ← Login + SignUp (bilingual)
│   │   ├── home_page.dart                 ← Dashboard with stats & quick access
│   │   ├── price_list_page.dart           ← Price List with categories + search
│   │   ├── price_search_page.dart         ← Price Search + custom trend chart
│   │   ├── file_complaint_page.dart       ← File Complaint (GPS + photos)
│   │   ├── my_complaints_page.dart        ← My Complaints + detail view
│   │   ├── track_complaint_page.dart      ← Track Complaint by number (login required)
│   │   └── notifications_page.dart        ← Notifications + Profile
│   ├── widgets/
│   │   ├── shared_widgets.dart            ← AppBar, BottomNav, StatusBadge, SectionHeader
│   │   └── offline_banner.dart            ← Network status indicator
│   ├── services/
│   │   ├── api_service.dart               ← Base HTTP client (GET/POST/PUT/multipart)
│   │   ├── auth_service.dart              ← Login/Register/Logout/User session
│   │   ├── complaint_service.dart         ← Complaints CRUD + trackComplaint()
│   │   ├── price_service.dart             ← Price list/search/trend API
│   │   ├── notification_service.dart      ← Notifications API
│   │   └── cache_service.dart             ← SharedPreferences wrapper (offline caching)
│   └── models/
│       ├── complaint_model.dart
│       └── price_model.dart               ← PriceCategory + PriceItem
└── pubspec.yaml
```

## Laravel Backend Structure
```
laravel-api/
├── routes/
│   ├── api.php                            ← All API routes (public, protected, admin)
│   └── web.php                            ← Web admin panel routes
├── app/Http/
│   ├── Controllers/
│   │   ├── API/
│   │   │   ├── AuthController.php         ← Register/Login/Logout/Profile/Admin users
│   │   │   ├── ComplaintController.php    ← Complaints CRUD + track (auth) + admin
│   │   │   ├── PriceController.php        ← Price list/categories/search/trend + admin CRUD
│   │   │   └── NotificationController.php ← Notifications + admin broadcast
│   │   └── Admin/                         ← Web admin panel controllers
│   │       ├── AdminAuthController.php
│   │       ├── DashboardController.php
│   │       ├── ComplaintAdminController.php
│   │       ├── PriceAdminController.php
│   │       ├── NotificationAdminController.php
│   │       ├── UserAdminController.php
│   │       ├── PriceUpdateLogAdminController.php
│   │       └── TrashController.php
│   └── Middleware/
│       ├── AdminMiddleware.php             ← API admin guard
│       └── AdminWebMiddleware.php          ← Web admin guard
├── app/Models/
│   ├── User.php                           ← citizen/admin/staff roles
│   ├── Complaint.php
│   ├── ComplaintPicture.php
│   ├── ComplaintStatusLog.php             ← Status change audit trail
│   ├── PriceCategory.php
│   ├── PriceItem.php
│   ├── PriceHistory.php
│   ├── PriceUpdateLog.php                 ← Price change audit log
│   └── UserNotification.php
├── app/Exports/
│   └── PriceItemExport.php                ← Excel export (all/per-category)
├── app/Imports/
│   └── PriceItemImport.php                ← Excel import with validation
├── database/
│   ├── migrations/                        ← 17 total migrations
│   └── seeders/
│       └── DatabaseSeeder.php             ← 3 users + 9 categories + 45 items
├── resources/views/                       ← Blade templates (web admin panel)
│   ├── layouts/app.blade.php
│   ├── auth/, dashboard/, complaints/
│   ├── prices/, notifications/, users/
│   └── trash/
└── bootstrap/app.php                      ← Middleware aliases + exception handling
```

## Setup Steps

### Laravel Setup
```bash
cd laravel-api
composer install
cp .env.example .env
# Edit .env: set DB credentials, APP_URL, etc.
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

### Flutter Setup
```bash
cd flutter
flutter pub get
# Update API base URL in lib/services/api_service.dart if needed
flutter run
```

### Build APK
```bash
cd flutter
flutter build apk --release
# Output: build/app/outputs/flutter-apk/app-release.apk
```

## API Endpoints Summary

### Auth (Public)
- `POST /api/auth/register`
- `POST /api/auth/login`

### Prices (Public)
- `GET  /api/prices`
- `GET  /api/prices/categories`
- `GET  /api/prices/search?q=rice`
- `GET  /api/prices/{id}`
- `GET  /api/prices/{id}/trend?period=7days|30days|3months`

### Auth (Sanctum Token Required)
- `POST /api/auth/logout`
- `GET  /api/auth/me`
- `PUT  /api/auth/profile`
- `PUT  /api/auth/change-password`

### Complaints (Auth Required)
- `GET  /api/complaints`
- `POST /api/complaints`               ← multipart/form-data with up to 5 pictures
- `GET  /api/complaints/stats`
- `GET  /api/complaints/track/{complaintNumber}` ← login required; own complaints only
- `GET  /api/complaints/{id}`

### Notifications (Auth Required)
- `GET  /api/notifications`
- `GET  /api/notifications/unread-count`
- `POST /api/notifications/{id}/read`
- `POST /api/notifications/read-all`

### Admin Routes (/api/admin/...)
- Price categories + items CRUD
- Bulk price update (transactional)
- Complaint management + status update
- Broadcast notifications to all citizens
- Dashboard stats
- User list/detail

## Demo Credentials
| Role    | Username       | Password       |
|---------|----------------|----------------|
| Admin   | `admin`        | `Admin@12345`  |
| Viewer  | `viewer`       | `Viewer@12345` |
| Citizen | `demo_citizen` | `Demo@1234`    |

## Key Features
- Bilingual (English + Urdu) throughout with Nastaliq Urdu font
- 45 pre-seeded price items across 9 categories
- Price history tracking + custom trend charts (7/30/90 days)
- GPS-based complaint filing with up to 5 photo uploads
- Authenticated complaint tracking by number (own complaints only)
- Offline caching with cached dashboard and price data
- Status workflow: Pending → In Progress → Resolved/Rejected
- Web admin panel with two roles: Admin (full CRUD) + Viewer (read-only)
- Excel import/export for price management
- Soft deletes + recycle bin for data recovery
- Price update audit log
- In-app notifications with admin broadcast
