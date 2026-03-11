<?php

namespace Tests\Feature\Api\V1;

use App\Enums\LicenseCodeStatus;
use App\Models\Device;
use App\Models\LicenseCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_can_activate_with_valid_pin()
    {
        $pin = 'TESTPIN12345';
        $license = LicenseCode::create([
            'serial' => 1,
            'pin_hash' => hash('sha256', $pin),
            'status' => 'active',
            'duration_days' => 30,
        ]);

        $response = $this->postJson('/api/v1/device/activate', [
            'pin_code' => $pin,
            'device_id' => 'device-1234',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'token', 'expires_at']);

        $this->assertDatabaseHas('devices', ['device_id' => 'device-1234']);
    }

    public function test_device_can_check_status()
    {
        $device = Device::factory()->create();
        $license = LicenseCode::create([
            'serial' => 2,
            'pin_hash' => hash('sha256', 'DUMMYPIN'),
            'device_id' => $device->id,
            'status' => 'redeemed',
            'duration_days' => 30,
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($device, 'sanctum')->getJson('/api/v1/device/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'subscription_status' => 'active',
            ]);
    }

    public function test_device_can_unlink()
    {
        $device = Device::factory()->create();
        $license = LicenseCode::create([
            'serial' => 3,
            'pin_hash' => hash('sha256', 'DUMMYPIN2'),
            'device_id' => $device->id,
            'status' => 'redeemed',
            'duration_days' => 30,
        ]);

        $response = $this->actingAs($device, 'sanctum')->postJson('/api/v1/device/unlink');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Device unlinked successfully.',
            ]);

        $this->assertDatabaseHas('license_codes', [
            'id' => $license->id,
            'device_id' => null
        ]);
    }
}
