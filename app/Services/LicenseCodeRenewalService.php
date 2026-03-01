<?php

namespace App\Services;

use App\Models\LicenseCode;
use App\Enums\LicenseCodeStatus;
use Illuminate\Support\Facades\DB;

class LicenseCodeRenewalService
{
    /**
     * Attempts to renew a license code.
     *
     * @param int|string $codeId The ID of the LicenseCode.
     * @param int $durationDays The duration to add or set.
     * @return array Result containing status and data or error message.
     */
    public function renew($codeId, int $durationDays): array
    {
        return DB::transaction(function () use ($codeId, $durationDays) {
            $code = LicenseCode::where('id', $codeId)->lockForUpdate()->first();

            if (!$code) {
                return [
                    'status' => 'error',
                    'message' => 'License code not found.',
                    'code' => 404
                ];
            }

            if ($code->status === LicenseCodeStatus::Inactive) {
                $code->status = LicenseCodeStatus::Active;
                $code->activated_at = now();
                $code->expires_at = now()->addDays($durationDays);
                $code->duration_days = $durationDays;
            } elseif ($code->status === LicenseCodeStatus::Active || $code->status === LicenseCodeStatus::Redeemed) {
                if ($code->expires_at) {
                    $code->expires_at = $code->expires_at->addDays($durationDays);
                } else {
                    $code->expires_at = now()->addDays($durationDays);
                }
                $code->duration_days += $durationDays;
            }

            $code->save();

            return [
                'status' => 'success',
                'license_code' => $code,
                'code' => 200
            ];
        });
    }
}
