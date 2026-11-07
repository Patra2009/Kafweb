<?php

use App\Http\Controllers\Web\AdminViewController;
use App\Http\Controllers\Web\AuthViewController;
use App\Http\Controllers\Web\KasirViewController;
use Illuminate\Support\Facades\Route;

// Portal pelanggan: katalog, keranjang, selfie checkout, dan lacak status.
Route::get('/', function () {
    return view('customer');
})->name('home');

// Authentication routes
Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthViewController::class, 'login']);

Route::post('/logout', [AuthViewController::class, 'logout'])->name('logout')->middleware('auth');

// Kasir dashboard (requires auth + kasir/admin role)
Route::middleware(['auth', 'role:kasir,admin'])->prefix('kasir')->name('kasir.')->group(function (): void {
    Route::get('/dashboard', [KasirViewController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [KasirViewController::class, 'orders'])->name('orders');
    Route::get('/customers', [KasirViewController::class, 'customers'])->name('customers');
});

// Admin dashboard (requires auth + admin role only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [AdminViewController::class, 'dashboard'])->name('dashboard');
    Route::get('/categories', [AdminViewController::class, 'categories'])->name('categories');
    Route::get('/menus', [AdminViewController::class, 'menus'])->name('menus');
    Route::get('/staff', [AdminViewController::class, 'staff'])->name('staff');
    Route::get('/customers', [AdminViewController::class, 'customers'])->name('customers');
    Route::get('/reports', [AdminViewController::class, 'reports'])->name('reports');
});
