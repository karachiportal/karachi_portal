<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ComplaintController;
use App\Http\Controllers\API\PriceController;
use App\Http\Controllers\API\NotificationController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Public complaint tracking (no login needed)
Route::get('/complaints/track/{complaintNumber}', [ComplaintController::class, 'track']);

// Public price list (no login needed to browse prices)
Route::get('/prices',              [PriceController::class, 'index']);
Route::get('/prices/categories',   [PriceController::class, 'categories']);
Route::get('/prices/search',       [PriceController::class, 'search']);
Route::get('/prices/{id}',         [PriceController::class, 'show']);
Route::get('/prices/{id}/trend',   [PriceController::class, 'trend']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum auth required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);

    // Complaints
    Route::get('/complaints',           [ComplaintController::class, 'index']);
    Route::post('/complaints',          [ComplaintController::class, 'store']);
    Route::get('/complaints/stats',     [ComplaintController::class, 'stats']);
    Route::get('/complaints/{id}',      [ComplaintController::class, 'show']);

    // Notifications
    Route::get('/notifications',                    [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count',       [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read',         [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all',          [NotificationController::class, 'markAllRead']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Admin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // User management
    Route::get('/users',             [AuthController::class, 'adminListUsers']);
    Route::get('/users/{id}',        [AuthController::class, 'adminShowUser']);

    // Complaint management
    Route::get('/complaints',                        [ComplaintController::class, 'adminIndex']);
    Route::put('/complaints/{id}/status',            [ComplaintController::class, 'updateStatus']);

    // Price management (per category)
    Route::get('/price-categories',        [PriceController::class, 'adminCategories']);
    Route::post('/price-categories',       [PriceController::class, 'storeCategory']);
    Route::put('/price-categories/{id}',   [PriceController::class, 'updateCategory']);
    Route::delete('/price-categories/{id}',[PriceController::class, 'destroyCategory']);

    Route::get('/price-items',             [PriceController::class, 'adminItems']);
    Route::post('/price-items',            [PriceController::class, 'storeItem']);
    Route::put('/price-items/{id}',        [PriceController::class, 'updateItem']);
    Route::delete('/price-items/{id}',     [PriceController::class, 'destroyItem']);

    // Bulk update prices
    Route::post('/prices/bulk-update',     [PriceController::class, 'bulkUpdate']);

    // Send notifications
    Route::post('/notifications/broadcast', [NotificationController::class, 'broadcast']);

    // Dashboard stats (admin)
    Route::get('/dashboard/stats',         [ComplaintController::class, 'adminStats']);
});
