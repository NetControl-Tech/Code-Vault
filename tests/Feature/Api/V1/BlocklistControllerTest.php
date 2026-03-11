<?php

namespace Tests\Feature\Api\V1;

use App\Enums\BlocklistCategory;
use App\Models\BlocklistDomain;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BlocklistControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        // Need to create a device to authenticate to blocklist endpoints
        $this->device = Device::factory()->create();
    }

    public function test_device_can_get_family_blocklist()
    {
        BlocklistDomain::create(['domain' => 'baddomain.com', 'category' => BlocklistCategory::Family->value]);

        $response = $this->actingAs($this->device, 'sanctum')->getJson('/api/v1/blocklists/family');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.domain', 'baddomain.com');
    }

    public function test_device_can_get_social_blocklist()
    {
        BlocklistDomain::create(['domain' => 'facebook.com', 'category' => BlocklistCategory::Social->value]);

        $response = $this->actingAs($this->device, 'sanctum')->getJson('/api/v1/blocklists/social');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.domain', 'facebook.com');
    }

    public function test_unauthenticated_device_cannot_access_blocklists()
    {
        $response = $this->getJson('/api/v1/blocklists/family');

        $response->assertStatus(401);
    }
}
