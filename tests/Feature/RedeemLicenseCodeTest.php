<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\LicenseCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RedeemLicenseCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_redemption_with_active_code()
    {
        $pin = 'ABC123DEF456';
        $code = LicenseCode::create([
            'serial' => 1,
            'pin_hash' => \hash('sha256', $pin),
            'status' => 'active',
            'duration_days' => 30,
        ]);

        $response = $this->postJson(route('api.redeem'), [
            'pin_code' => $pin,
            'device_id' => 'DEVICE_A_123456789'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure(['token']);

        // The device is now created via firstOrCreate, so device_id in license_codes is the DB id
        $device = Device::where('device_id', 'DEVICE_A_123456789')->first();
        $this->assertNotNull($device);

        $this->assertDatabaseHas('license_codes', [
            'id' => $code->id,
            'status' => 'redeemed',
            'device_id' => $device->id,
        ]);
    }

    public function test_successful_redemption_with_redeemed_code_same_device()
    {
        $pin = 'ABC123DEF456';

        // Create the device first
        $device = Device::create([
            'device_id' => 'DEVICE_A_123456789',
            'is_active' => true,
        ]);

        $code = LicenseCode::create([
            'serial' => 1,
            'pin_hash' => \hash('sha256', $pin),
            'status' => 'redeemed',
            'device_id' => $device->id,
            'duration_days' => 30,
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->postJson(route('api.redeem'), [
            'pin_code' => $pin,
            'device_id' => 'DEVICE_A_123456789'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
    }

    public function test_failed_redemption_device_mismatch()
    {
        $pin = 'ABC123DEF456';

        // Create the device that originally redeemed
        $device = Device::create([
            'device_id' => 'DEVICE_A_123456789',
            'is_active' => true,
        ]);

        $code = LicenseCode::create([
            'serial' => 1,
            'pin_hash' => \hash('sha256', $pin),
            'status' => 'redeemed',
            'device_id' => $device->id,
        ]);

        $response = $this->postJson(route('api.redeem'), [
            'pin_code' => $pin,
            'device_id' => 'DEVICE_B_987654321'
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Device Mismatch');
    }

    public function test_brute_force_rate_limits_requests()
    {
        RateLimiter::clear('redeem_ip:127.0.0.1');
        RateLimiter::clear('redeem_device:DEVICE_TEST_123');

        // Hit 3 times (the max is 3)
        for ($i = 0; $i < 3; $i++) {
            $this->postJson(route('api.redeem'), [
                'pin_code' => 'INVALIDPIN12',
                'device_id' => 'DEVICE_TEST_123'
            ])->assertStatus(400); // Because code doesn't exist, we get 400
        }

        // 4th time should be blocked
        $response = $this->postJson(route('api.redeem'), [
            'pin_code' => 'INVALIDPIN12',
            'device_id' => 'DEVICE_TEST_123'
        ]);

        $response->assertStatus(429);
        $response->assertJsonPath('status', 'error');
    }

    public function test_invalid_pin_format()
    {
        $response = $this->postJson(route('api.redeem'), [
            'pin_code' => 'SHORT', // Less than 12
            'device_id' => 'DEVICE_TEST_123'
        ]);

        $response->assertStatus(422); // Validation error
    }
}
