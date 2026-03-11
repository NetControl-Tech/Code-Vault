<?php

namespace App\Services;

use App\Models\Device;
use App\Models\LicenseCode;
use App\Enums\LicenseCodeStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceService
{
    // ──────────────────────────────────────────────
    //  Activation (PIN Redemption)
    // ──────────────────────────────────────────────

    /**
     * Activate a device by redeeming a license PIN code.
     *
     * @param string $pinCode The plain-text PIN code.
     * @param string $deviceId The unique hardware device identifier.
     * @return array{status: string, token?: string, expires_at?: string, device_id?: string, message?: string, code: int}
     */
    public function activate(string $pinCode, string $deviceId): array
    {
        $hashedPin = hash('sha256', $pinCode);

        $result = DB::transaction(function () use ($hashedPin, $deviceId) {
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

            // --- Active code: first-time redemption ---
            if ($code->status === LicenseCodeStatus::Active) {
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

            // --- Already redeemed code ---
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

                // Same device, not expired → issue new token
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

        // Unify response format for V1 API
        if ($result['status'] === 'success') {
            return [
                'status' => 'success',
                'token' => $result['token'],
                'expires_at' => $result['license_code']->expires_at,
                'device_id' => $result['device']->device_id,
                'code' => 200,
            ];
        }

        return $result;
    }

    // ──────────────────────────────────────────────
    //  Subscription Status
    // ──────────────────────────────────────────────

    /**
     * Check the current status of the device's subscription.
     *
     * @param Device $device The authenticated device.
     * @return array{status: string, subscription_status?: string, expires_at?: string, message?: string, code: int}
     */
    public function status(Device $device): array
    {
        $licenseCode = $device->licenseCode;

        if (!$licenseCode) {
            return [
                'status' => 'error',
                'message' => 'No license code linked to this device.',
                'code' => 404,
            ];
        }

        $isActive = false;
        /** @var LicenseCode $licenseCode */
        if ($licenseCode->status === LicenseCodeStatus::Redeemed) {
            if ($licenseCode->expires_at && now()->lessThanOrEqualTo($licenseCode->expires_at)) {
                $isActive = true;
            }
        }

        return [
            'status' => 'success',
            'subscription_status' => $isActive ? 'active' : 'expired',
            'expires_at' => $licenseCode->expires_at,
            'code' => 200,
        ];
    }

    // ──────────────────────────────────────────────
    //  Unlink
    // ──────────────────────────────────────────────

    /**
     * Unlink the device from its current license code.
     * Revokes all Sanctum tokens for that device.
     *
     * @param Device $device The authenticated device.
     * @return array{status: string, message: string, code: int}
     */
    public function unlink(Device $device): array
    {
        return DB::transaction(function () use ($device) {
            $licenseCode = $device->licenseCode()->lockForUpdate()->first();

            if (!$licenseCode) {
                return [
                    'status' => 'error',
                    'message' => 'No active license code found to unlink.',
                    'code' => 404,
                ];
            }

            $licenseCode->device_id = null;
            $licenseCode->save();

            $device->tokens()->delete();

            return [
                'status' => 'success',
                'message' => 'Device unlinked successfully.',
                'code' => 200,
            ];
        });
    }
}
