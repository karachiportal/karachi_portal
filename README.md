# Commissioner Karachi Portal — Full Project

## Flutter App Structure
```
flutter/
├── lib/
│   ├── main.dart                      ← Entry point + routing
│   ├── pages/
│   │   ├── login_page.dart            ← Login + SignUp
│   │   ├── home_page.dart             ← Dashboard
│   │   ├── price_list_page.dart       ← Price List
│   │   ├── price_search_page.dart     ← Price Search + Trend Chart
│   │   ├── file_complaint_page.dart   ← File a Complaint
│   │   ├── my_complaints_page.dart    ← My Complaints + Detail
│   │   └── notifications_page.dart   ← Notifications + Profile
│   ├── widgets/
│   │   └── shared_widgets.dart        ← AppBar, BottomNav, StatusBadge
│   ├── services/
│   │   ├── api_service.dart           ← Base HTTP client
│   │   ├── auth_service.dart          ← Login/Register/Logout
│   │   ├── complaint_service.dart     ← Complaints API
│   │   ├── price_service.dart         ← Price List API
│   │   └── notification_service.dart  ← Notifications API
│   └── models/
│       ├── complaint_model.dart
│       └── price_model.dart
└── pubspec.yaml
```

## Laravel Backend Structure
```
laravel/
├── routes/api.php                           ← All API routes
├── app/Http/
│   ├── Controllers/API/
│   │   ├── AuthController.php               ← Register/Login/Logout
│   │   ├── ComplaintController.php          ← Complaints CRUD
│   │   ├── PriceController.php              ← Price List + Categories CRUD
│   │   ├── NotificationController.php       ← Notifications
│   │   └── AnnouncementController.php       ← Announcements
│   └── Middleware/
│       └── AdminMiddleware.php
├── app/Models/
│   ├── User.php
│   ├── Complaint.php                        ← includes ComplaintPicture
│   └── PriceModels.php                      ← Category/Item/History/Notification/Announcement
├── database/
│   ├── migrations/all_migrations.php        ← All 8 table migrations
│   └── seeders/DatabaseSeeder.php           ← Admin user + 45 price items
└── bootstrap_app.php                        ← Middleware + Exception handling
```

## Setup Steps

### Laravel Setup
```bash
composer create-project laravel/laravel karachi-portal-api
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Copy all files from laravel/ folder
# Then run:
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

### Flutter Setup
```bash
flutter create karachi_portal
# Copy all files from flutter/ folder
# Update lib/services/api_service.dart → change baseUrl
flutter pub get
flutter run
```

## API Endpoints Summary

### Auth (Public)
- POST /api/auth/register
- POST /api/auth/login

### Prices (Public)
- GET /api/prices
- GET /api/prices/categories
- GET /api/prices/search?q=rice
- GET /api/prices/{id}/trend?period=7days

### Complaints (Auth Required)
- GET  /api/complaints
- POST /api/complaints        ← multipart/form-data with pictures
- GET  /api/complaints/stats
- GET  /api/complaints/{id}

### Notifications (Auth Required)
- GET  /api/notifications
- GET  /api/notifications/unread-count
- POST /api/notifications/{id}/read
- POST /api/notifications/read-all

### Admin Routes (/api/admin/...)
- All price CRUD (categories + items + bulk update)
- All complaint management + status update
- Broadcast notifications
- Dashboard stats

## Demo Credentials
- Admin:   username: admin     | password: Admin@12345
- Citizen: username: demo_citizen | password: Demo@1234

## Key Features
- Bilingual (English + Urdu) throughout
- 45 pre-seeded price items across 9 categories
- Price history tracking + trend charts
- GPS-based complaint filing with photo upload
- Real-time complaint status tracking
- Admin panel ready (web admin next step)
