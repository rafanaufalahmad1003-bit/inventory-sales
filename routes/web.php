<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - LensRent Camera Rental & Inventory Management System
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (Tersedia untuk semua role: Owner, Admin, Staff, Pelanggan)
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Katalog & Detail Produk (Dapat dilihat oleh semua role terotentikasi)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Pemesanan Mandiri Rental Kamera oleh Pelanggan (Online Booking)
    Route::get('/booking', [SaleController::class, 'bookingCreate'])->name('booking.create');
    Route::post('/booking', [SaleController::class, 'bookingStore'])->name('booking.store');

    // Rute Operasional Staff, Admin, dan Owner (Master Data & Transaksi)
    Route::middleware('role:staff,admin,owner')->group(function () {
        // Produk (Create, Edit, Update)
        Route::get('/products/create/new', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

        // Kategori (Master Data)
        Route::resource('categories', CategoryController::class)->except(['destroy']);

        // Pemasok / Supplier (Master Data)
        Route::resource('suppliers', SupplierController::class)->except(['destroy']);

        // Pelanggan (Customer Rental Kamera)
        Route::resource('customers', CustomerController::class)->except(['destroy']);

        // Transaksi Pembelian / Stock-In
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

        // Transaksi Penjualan / Rental Kamera
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    });

    // Detail Transaksi Penjualan/Rental (Staff, Admin, Owner, Pelanggan pemilik transaksi)
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    // Rute Khusus Admin & Owner (Wewenang Penuh yang Sama)
    Route::middleware('role:admin,owner')->group(function () {
        // Hapus Data Master Sensitif
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Pembatalan Transaksi dengan Rollback/Pemulihan Stok
        Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

        // Manajemen Pengguna & Hak Akses
        Route::resource('users', UserController::class);

        // Laporan Bisnis & Keuangan Terintegrasi
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
});
