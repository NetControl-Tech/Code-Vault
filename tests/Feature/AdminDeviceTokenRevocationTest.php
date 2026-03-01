<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDeviceTokenRevocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);
    }

    public function test_authorized_admin_can_revoke_device_tokens()
    {
        // 1. Arrange: Create a super-admin
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        // 2. Arrange: Create a device and issue tokens
        $device = Device::factory()->create();
        $device->createToken('Test Token A');
        $device->createToken('Test Token B');

        $this->assertEquals(2, $device->tokens()->count());

        // 3. Act: Admin attempts to revoke all tokens
        $response = $this->actingAs($admin)
            ->postJson(route('admin.devices.revoke-token', ['device' => $device->id]));

        // 4. Assert: Success & tokens deleted
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tokens revoked successfully'
            ]);

        $this->assertEquals(0, $device->tokens()->count());
    }

    public function test_unauthorized_user_gets_403()
    {
        // 1. Arrange: Create a normal user without roles
        $user = User::factory()->create();

        // 2. Arrange: Create a device
        $device = Device::factory()->create();

        // 3. Act: User attempts to revoke tokens
        $response = $this->actingAs($user)
            ->postJson(route('admin.devices.revoke-token', ['device' => $device->id]));

        // 4. Assert: Forbidden
        $response->assertStatus(403);
    }

    public function test_device_not_found_returns_404()
    {
        // 1. Arrange: Create a super-admin
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        // 2. Act: Admin queries a non-existent device
        $response = $this->actingAs($admin)
            ->postJson(route('admin.devices.revoke-token', ['device' => 99999]));

        // 3. Assert: Not found
        $response->assertStatus(404);
    }
}
