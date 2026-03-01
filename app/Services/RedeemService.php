<?php

namespace App\Services;

use App\Models\Device;
use App\Models\LicenseCode;
use App\Enums\LicenseCodeStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RedeemService
{
    /**
     * Attempts to redeem a license code for a given device.
     *
     * @param string $pinCode The PIN code string.
     * @param string $deviceId The device identifier string.
     * @return array Result containing status and data or error message.
     */
    public function redeem(string $pinCode, string $deviceId): array
    {
        $hashedPin = \hash('sha256', $pinCode);

        return DB::transaction(function () use ($hashedPin, $deviceId) {
            $code = LicenseCode::where('pin_hash', $hashedPin)->lockForUpdate()->first();

            if (!$code || $code->status === LicenseCodeStatus::Inactive) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or inactive PIN',
                    'code' => 400
                ];
            }

            // Get or Create Device
            $device = Device::firstOrCreate(
                ['device_id' => $deviceId],
                ['is_active' => true]
            );

            if (!$device->is_active) {
                return [
                    'status' => 'error',
                    'message' => 'Device is inactive/blocked.',
                    'code' => 403
                ];
            }

            // Success scenarios:
            if ($code->status === LicenseCodeStatus::Active) {
                // Determine if device ALREADY has another active code? 
                // The requirements say: "If device already has a license_code linked, block redemption with proper error."
                if ($device->licenseCode()->exists()) {
                    return [
                        'status' => 'error',
                        'message' => 'Device already has a linked license code.',
                        'code' => 403
                    ];
                }

                $code->status = LicenseCodeStatus::Redeemed;
                $code->device_id = $device->id;
                $code->redeemed_at = now();
                $code->expires_at = now()->addDays($code->duration_days);
                $code->save();

                $token = $device->createToken('device')->plainTextToken;

                return [
                    'status' => 'success',
                    'device' => $device,
                    'license_code' => $code,
                    'token' => $token,
                    'code' => 200
                ];
            }

            if ($code->status === LicenseCodeStatus::Redeemed) {
                if ($code->device_id !== $device->id) {
                    Log::warning('Code Already Redeemed', [
                        'serial' => $code->serial,
                        'received_device' => $deviceId,
                        'actual_device_id_db' => $code->device_id
                    ]);

                    return [
                        'status' => 'error',
                        'message' => 'Code Already Redeemed',
                        'code' => 403
                    ];
                }

                if ($code->expires_at && now()->greaterThan($code->expires_at)) {
                    return [
                        'status' => 'error',
                        'message' => 'Expired',
                        'code' => 403
                    ];
                }

                // If code is redeemed and belongs to this device and not expired, return a new token
                $token = $device->createToken('device')->plainTextToken;

                return [
                    'status' => 'success',
                    'device' => $device,
                    'license_code' => $code,
                    'token' => $token,
                    'code' => 200
                ];
            }

            return ['status' => 'error', 'message' => 'Unknown status', 'code' => 500];
        });
    }
}
