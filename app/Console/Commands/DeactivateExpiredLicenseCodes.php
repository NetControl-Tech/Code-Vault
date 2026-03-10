<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LicenseCode;
use App\Enums\LicenseCodeStatus;

class DeactivateExpiredLicenseCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license-codes:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate license codes that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = LicenseCode::with('device')
            ->where('status', LicenseCodeStatus::Active->value)
            ->where('expires_at', '<=', now());

        $updatedCount = $query->count();

        $query->chunkById(100, function ($licenses) {
            foreach ($licenses as $license) {
                /** @var \App\Models\LicenseCode $license */
                if ($license->device) {
                    $license->device->tokens()->delete();
                }
                $license->update(['status' => LicenseCodeStatus::Inactive->value]);
            }
        });

        $this->info("Successfully deactivated {$updatedCount} expired license codes and revoked their devices.");
        logger()->info("DeactivateExpiredLicenseCodes: Deactivated {$updatedCount} expired license codes and revoked their devices.");
    }
}
