<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ComplaintAdminController;
use App\Http\Controllers\Admin\PriceAdminController;
use App\Http\Controllers\Admin\NotificationAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\PriceUpdateLogAdminController;
use App\Http\Controllers\Admin\TrashController;

Route::middleware('guest')->group(function () {
    Route::get('/admin/login',      [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login',     [AdminAuthController::class, 'login'])->name('admin.login.post');
});

Route::middleware(['auth', 'admin.web'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/change-password',  [AdminAuthController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [AdminAuthController::class, 'updatePassword'])->name('change-password.post');

    Route::get('/',                  [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard',         [DashboardController::class, 'index']);

    Route::prefix('complaints')->name('complaints.')->group(function () {
        Route::get('/',             [ComplaintAdminController::class, 'index'])->name('index');
        Route::get('/export',       [ComplaintAdminController::class, 'export'])->name('export');
        Route::get('/{id}',         [ComplaintAdminController::class, 'show'])->name('show');
        Route::put('/{id}/status',  [ComplaintAdminController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('prices')->name('prices.')->group(function () {
        Route::get('/categories',           [PriceAdminController::class, 'categories'])->name('categories');
        Route::post('/categories',          [PriceAdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{id}',      [PriceAdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{id}',   [PriceAdminController::class, 'destroyCategory'])->name('categories.destroy');

        Route::get('/items',                [PriceAdminController::class, 'items'])->name('items');
        Route::post('/items',               [PriceAdminController::class, 'storeItem'])->name('items.store');
        Route::put('/items/{id}',           [PriceAdminController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{id}',        [PriceAdminController::class, 'destroyItem'])->name('items.destroy');

        Route::get('/bulk',                 [PriceAdminController::class, 'bulkView'])->name('bulk');
        Route::post('/bulk-update',         [PriceAdminController::class, 'bulkUpdate'])->name('bulk.update');

        Route::post('/import/preview',      [PriceAdminController::class, 'importPreview'])->name('import.preview');
        Route::post('/import/process',      [PriceAdminController::class, 'importProcess'])->name('import.process');
        Route::get('/export',               [PriceAdminController::class, 'exportPrices'])->name('export');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                 [NotificationAdminController::class, 'index'])->name('index');
        Route::post('/broadcast',       [NotificationAdminController::class, 'broadcast'])->name('broadcast');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',         [UserAdminController::class, 'index'])->name('index');
        Route::get('/{id}',     [UserAdminController::class, 'show'])->name('show');
    });

    // Price update logs
    Route::prefix('price-update-logs')->name('price-update-logs.')->group(function () {
        Route::get('/',             [PriceUpdateLogAdminController::class, 'index'])->name('index');
        Route::get('/export',       [PriceUpdateLogAdminController::class, 'exportCsv'])->name('export');
    });

    // Recycle Bin
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/',                     [TrashController::class, 'index'])->name('index');
        Route::post('/restore/{type}/{id}', [TrashController::class, 'restore'])->name('restore');
        Route::post('/force-delete/{type}/{id}', [TrashController::class, 'forceDelete'])->name('force-delete');
    });
});

Route::get('/', fn() => redirect('/admin'));
