<?php

namespace Tests\Feature\Api\V1;

use App\Enums\BlocklistCategory;
use App\Models\BlocklistDomain;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->device = Device::factory()->create();
    }

    public function test_can_check_url()
    {
        BlocklistDomain::create(['domain' => 'baddomain.com', 'category' => BlocklistCategory::Ads->value]);

        $response = $this->actingAs($this->device, 'sanctum')->postJson('/api/v1/tools/check-url', [
            'url' => 'https://www.baddomain.com/path'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'blocked',
                'category' => BlocklistCategory::Ads->value,
            ]);
    }

    public function test_can_report_url()
    {
        $response = $this->actingAs($this->device, 'sanctum')->postJson('/api/v1/tools/report-url', [
            'url' => 'https://newbaddomain.com/something'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'URL reported successfully. It will be reviewed soon.',
            ]);

        $this->assertDatabaseHas('reported_urls', [
            'domain' => 'newbaddomain.com',
            'device_id' => $this->device->id
        ]);
    }
}
