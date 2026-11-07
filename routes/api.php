<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Api\V1\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\StaffController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerOrderController;
use App\Http\Controllers\Api\V1\Kasir\CustomerController as KasirCustomerController;
use App\Http\Controllers\Api\V1\Kasir\OrderController as KasirOrderController;
use App\Http\Controllers\Api\V1\MenuController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // ------------------------------------------------------------------
    // Pelanggan (public): menu, checkout, tracking status
    // ------------------------------------------------------------------
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::get('/menus/{menu}', [MenuController::class, 'show'])->name('menus.show');

    Route::post('/orders', [CustomerOrderController::class, 'store'])
        ->middleware('throttle:checkout')
        ->name('orders.store');
    Route::get('/orders/{trackingCode}/status', [CustomerOrderController::class, 'track'])
        ->middleware('throttle:tracking')
        ->whereAlphaNumeric('trackingCode')
        ->name('orders.track');

    // ------------------------------------------------------------------
    // Auth staf (admin/kasir): login, profil, logout
    // ------------------------------------------------------------------
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {

        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // ------------------------------------------------------------------
        // Kasir (dan admin): pesanan masuk, status, pembayaran, pelanggan
        // ------------------------------------------------------------------
        Route::middleware('role:kasir,admin')->prefix('kasir')->name('kasir.')->group(function (): void {
            Route::get('/orders', [KasirOrderController::class, 'index'])->name('orders.index');
            Route::post('/orders', [KasirOrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{order}', [KasirOrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}/status', [KasirOrderController::class, 'updateStatus'])->name('orders.status');
            Route::post('/orders/{order}/payment', [KasirOrderController::class, 'pay'])->name('orders.pay');

            Route::get('/customers', [KasirCustomerController::class, 'index'])->name('customers.index');
            Route::post('/customers', [KasirCustomerController::class, 'store'])->name('customers.store');
        });

        // ------------------------------------------------------------------
        // Admin: CRUD kategori, menu, staf, pelanggan + laporan
        // ------------------------------------------------------------------
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
            Route::apiResource('categories', AdminCategoryController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('menus', AdminMenuController::class);
            Route::apiResource('staff', StaffController::class);
            Route::apiResource('customers', AdminCustomerController::class);

            Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
            Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
            Route::get('/reports/top-items', [ReportController::class, 'topItems'])->name('reports.top-items');
        });
    });
});
