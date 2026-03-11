<?php

use App\Http\Controllers\Admin\AdminDeviceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminLicenseCodeController;

use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\BlocklistController;
use App\Http\Controllers\Api\V1\ToolsController;

use App\Http\Controllers\Admin\AdminBlocklistController;
use App\Http\Controllers\Admin\AdminReportController;

// =============================================
// Auth Routes (Public)
// =============================================
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login')->name('auth.login');
});

// Public Device Routes
Route::post('device/activate', [DeviceController::class, 'activate']);

// =============================================
// Authenticated Routes (Sanctum)
// =============================================
Route::middleware('auth:sanctum')->group(function () {

    // =============================================
    // User Only Routes
    // =============================================
    Route::middleware('is_user')->group(function () {
        // Auth routes (requires token)
        Route::controller(AuthController::class)->group(function () {
            Route::get('/me', 'me')->name('auth.me');
            Route::post('/verify-2fa', 'verify2FA')->name('auth.verify-2fa');
            Route::post('/resend-2fa', 'resend2FA')->middleware('throttle:1,1')->name('auth.resend-2fa');
            Route::post('/cancel-2fa', 'cancel2FA')->name('auth.cancel-2fa');
            Route::post('/logout', 'logout')->name('auth.logout');
        });

        // =============================================
        // Admin Routes (Super Admin Only)
        // =============================================
        Route::middleware('role:super-admin')->group(function () {
            // User Management
            Route::prefix('users')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('users.index');
                Route::post('/', [UserController::class, 'store'])->name('users.store');
                Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
                Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
                Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            });

            // Device Admin Management
            Route::post('admin/devices/{device}/revoke-token', [AdminDeviceController::class, 'revokeToken'])->name('admin.devices.revoke-token');
            Route::apiResource('devices', DeviceController::class)->only(['index', 'store', 'destroy']);

            // Blocklist Categories
            Route::get('blocklists/family', [BlocklistController::class, 'family']);
            Route::get('blocklists/social', [BlocklistController::class, 'social']);
            Route::get('blocklists/ads', [BlocklistController::class, 'ads']);
            Route::get('blocklists/privacy', [BlocklistController::class, 'privacy']);

            // RBAC
            Route::apiResource('roles', RoleController::class)->except(['show', 'destroy']);
            Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
            Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

            // License Codes Admin Management
            Route::post('admin/codes/generate', [AdminLicenseCodeController::class, 'generate'])->name('admin.codes.generate');
            Route::post('admin/codes/activate-range', [AdminLicenseCodeController::class, 'activateRange'])->name('admin.codes.activate-range');
            Route::post('admin/codes/destroy-range', [AdminLicenseCodeController::class, 'destroyRange'])->name('admin.codes.destroy-range');
            Route::post('admin/codes/{code}/renew', [AdminLicenseCodeController::class, 'renew'])->name('admin.codes.renew');
            Route::get('admin/codes/export', [AdminLicenseCodeController::class, 'export'])->name('admin.codes.export');
            Route::apiResource('admin/codes', AdminLicenseCodeController::class)->only(['index', 'show'])->names('admin.codes')->parameters([
                'codes' => 'code'
            ]);

            // Blocklist Management (CRUD + Bulk Upload)
            Route::post('admin/blocklists/bulk-upload', [AdminBlocklistController::class, 'bulkUpload']);
            Route::apiResource('admin/blocklists', AdminBlocklistController::class)->except(['show']);

            // Reported URLs Management (Review + Approve/Reject)
            Route::get('admin/reports', [AdminReportController::class, 'index']);
            Route::post('admin/reports/{report}/approve', [AdminReportController::class, 'approve']);
            Route::post('admin/reports/{report}/reject', [AdminReportController::class, 'reject']);
        });
    });

    // =============================================
    // Device Only Routes
    // =============================================
    Route::middleware('is_device')->group(function () {
        // Device routes
        Route::get('device/status', [DeviceController::class, 'status']);
        Route::post('device/unlink', [DeviceController::class, 'unlink']);

        // Tools
        Route::post('tools/check-url', [ToolsController::class, 'checkUrl']);
        Route::post('tools/report-url', [ToolsController::class, 'reportUrl']);
    });
});
