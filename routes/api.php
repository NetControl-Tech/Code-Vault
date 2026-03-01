<?php

use App\Http\Controllers\Admin\AdminDeviceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RedeemController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\AdminLicenseCodeController;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/redeem', [RedeemController::class, 'redeem'])->name('api.redeem');
Route::apiResource('devices', DeviceController::class)->only(['index', 'store', 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/verify-2fa', [AuthController::class, 'verify2FA'])->name('auth.verify-2fa');
    Route::post('/resend-2fa', [AuthController::class, 'resend2FA'])->middleware('throttle:1,1')->name('auth.resend-2fa');
    Route::post('/cancel-2fa', [AuthController::class, 'cancel2FA'])->name('auth.cancel-2fa');

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');

    /*
|--------------------------------------------------------------------------
| User Management Routes (مسارات إدارة المستخدمين)
|--------------------------------------------------------------------------
*/
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    // RBAC Routes (Super Admin Only)
    Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
        Route::apiResource('roles', RoleController::class)->except(['show', 'destroy']);
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        // License Codes Admin management
        Route::post('admin/codes/generate', [AdminLicenseCodeController::class, 'generate'])->name('admin.codes.generate');
        Route::post('admin/codes/activate-range', [AdminLicenseCodeController::class, 'activateRange'])->name('admin.codes.activate-range');
        Route::post('admin/codes/destroy-range', [AdminLicenseCodeController::class, 'destroyRange'])->name('admin.codes.destroy-range');
        Route::post('admin/codes/{code}/renew', [AdminLicenseCodeController::class, 'renew'])->name('admin.codes.renew');
        Route::get('admin/codes/export', [AdminLicenseCodeController::class, 'export'])->name('admin.codes.export');
        Route::apiResource('admin/codes', AdminLicenseCodeController::class)->only(['index', 'show'])->names('admin.codes')->parameters([
            'codes' => 'code'
        ]);
        // Device Admin management
        Route::post('admin/devices/{device}/revoke-token', [AdminDeviceController::class, 'revokeToken'])->name('admin.devices.revoke-token');
    });
});
