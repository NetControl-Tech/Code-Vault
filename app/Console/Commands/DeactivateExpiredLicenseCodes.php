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
        $updatedCount = LicenseCode::where('status', LicenseCodeStatus::Active->value)
            ->where('expires_at', '<=', now())
            ->update(['status' => LicenseCodeStatus::Inactive->value]);

        $this->info("Successfully deactivated {$updatedCount} expired license codes.");
        logger()->info("DeactivateExpiredLicenseCodes: Deactivated {$updatedCount} expired license codes.");
    }
}
