<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LicenseCodeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RevokeTokenRequest;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class AdminDeviceController extends Controller
{
    /**
     * Revoke tokens for a specific device.
     */
    public function revokeToken(RevokeTokenRequest $request, Device $device)
    {
        $device->licenseCode()->update([
            'status' => LicenseCodeStatus::Active,
            'device_id' => null,
            'redeemed_at' => null,
            'expires_at' => null,
        ]);
        $device->tokens()->delete();

        Log::info('Device tokens revoked by admin', [
            'admin_id' => $request->user()->id,
            'device_id' => $device->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tokens revoked successfully'
        ]);
    }
}
