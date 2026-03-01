<?php

namespace Tests\Feature;

use App\Enums\LicenseCodeStatus;
use App\Exports\GeneratedLicenseCodesExport;
use App\Models\LicenseCode;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminLicenseCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable middleware to bypass 'auth:sanctum' and 'role:super-admin'
        // since we are testing the controller logic not the auth mechanism.
        $this->withoutMiddleware();
    }

    public function test_generate_codes_creates_db_rows_and_downloads_xlsx()
    {
        // Freeze time so filename is deterministic
        Carbon::setTestNow(Carbon::create(2026, 2, 28, 12, 0, 0));

        Excel::fake();

        $response = $this->post(route('admin.codes.generate'), [
            'count' => 10,
        ]);

        $response->assertOk();

        // Assert DB rows created
        $this->assertDatabaseCount('license_codes', 10);

        // Optional: check fields on one row
        $code = LicenseCode::first();
        $this->assertNotNull($code->serial);
        $this->assertSame(LicenseCodeStatus::Inactive, $code->status);
        $this->assertSame(30, (int) $code->duration_days);

        // Assert Excel was downloaded with exact expected name
        $expectedName = 'generated_license_codes_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        Excel::assertDownloaded($expectedName, function ($export) {
            return $export instanceof GeneratedLicenseCodesExport;
        });
    }

    public function test_activate_range_activates_only_inactive_codes()
    {
        LicenseCode::insert([
            ['serial' => 1, 'pin_hash' => 'hash1', 'status' => LicenseCodeStatus::Inactive],
            ['serial' => 2, 'pin_hash' => 'hash2', 'status' => LicenseCodeStatus::Inactive],
            ['serial' => 3, 'pin_hash' => 'hash3', 'status' => LicenseCodeStatus::Inactive],
            ['serial' => 4, 'pin_hash' => 'hash4', 'status' => LicenseCodeStatus::Active], // already active
            ['serial' => 5, 'pin_hash' => 'hash5', 'status' => LicenseCodeStatus::Inactive],
        ]);

        $response = $this->postJson(route('admin.codes.activate-range'), [
            'from_serial' => 1,
            'to_serial' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('activated_count', 3);

        $this->assertDatabaseHas('license_codes', ['serial' => 1, 'status' => LicenseCodeStatus::Active]);
        $this->assertDatabaseHas('license_codes', ['serial' => 4, 'status' => LicenseCodeStatus::Active]);
        $this->assertDatabaseHas('license_codes', ['serial' => 5, 'status' => LicenseCodeStatus::Inactive]); // outside range
    }
}
