<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\Masters\LocationController;
use App\Http\Controllers\Masters\AssetStatusController;
use App\Http\Controllers\Masters\AssetConditionController;
use App\Http\Controllers\Masters\VendorController;
use App\Http\Controllers\Masters\BrandController;
use App\Http\Controllers\Masters\AssetTypeController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\DinamicLookupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will be
| assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    

    // User Management routes
    Route::middleware('permission:manage_users')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Role Management routes
    Route::middleware('permission:manage_roles')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Permission Management routes
    Route::middleware('permission:manage_permissions')->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    // Fixed Assets Management routes
    Route::middleware('permission:manage_fixed_assets')->group(function () {
        Route::resource('fixed-assets', FixedAssetController::class);

        // Masters (Lokasi, Status, Kondisi, Vendor, Brand, Tipe)
        Route::prefix('masters')->as('masters.')->group(function () {
            Route::resource('locations', LocationController::class)->except(['show']);
            Route::resource('statuses', AssetStatusController::class)->except(['show']);
            Route::resource('conditions', AssetConditionController::class)->except(['show']);
            Route::resource('vendors', VendorController::class)->except(['show']);
            Route::resource('brands', BrandController::class)->except(['show']);
            Route::resource('types', AssetTypeController::class)->except(['show']);

            // JSON lookup endpoints (search and create)
            Route::get('lookup/{entity}', [DinamicLookupController::class, 'search'])->name('lookup.search');
            Route::post('lookup/{entity}', [DinamicLookupController::class, 'store'])->name('lookup.store');
        });
    });

    

});

// Public routes (no auth required) - placed at the end to avoid conflicts
// Public view for asset (QR code scanning) - separate from admin routes
Route::get('/asset/{fixedAsset}', [FixedAssetController::class, 'showPublic'])->name('asset.public.show');

// QR Code routes (public)
Route::prefix('qr')->name('qr.')->group(function () {
    // General QR code generation
    Route::get('generate', [QRCodeController::class, 'generate'])->name('generate');
    
    // Fixed Asset specific QR codes
    Route::get('asset/{fixedAsset}', [QRCodeController::class, 'fixedAsset'])->name('asset');
    Route::get('asset/{fixedAsset}/print', [QRCodeController::class, 'printableAsset'])->name('asset.print');
    Route::get('asset/{fixedAsset}/download', [QRCodeController::class, 'download'])->name('asset.download');
});