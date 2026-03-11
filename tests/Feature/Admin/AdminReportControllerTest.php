<?php

namespace Tests\Feature\Admin;

use App\Enums\BlocklistCategory;
use App\Enums\ReportStatus;
use App\Models\Device;
use App\Models\ReportedUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminReportControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;
    protected Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');

        $this->device = Device::factory()->create();
    }

    public function test_admin_can_get_pending_reports()
    {
        ReportedUrl::create([
            'url' => 'https://baddomain.com/test',
            'domain' => 'baddomain.com',
            'device_id' => $this->device->id,
            'status' => ReportStatus::Pending->value
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/reports');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.domain', 'baddomain.com');
    }

    public function test_admin_can_approve_report()
    {
        $report = ReportedUrl::create([
            'url' => 'https://baddomain.com/test',
            'domain' => 'baddomain.com',
            'device_id' => $this->device->id,
            'status' => ReportStatus::Pending->value
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/reports/' . $report->id . '/approve', [
                'category' => BlocklistCategory::Social->value
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Report approved and domain added to blocklist.',
            ]);

        $this->assertDatabaseHas('reported_urls', [
            'id' => $report->id,
            'status' => ReportStatus::Approved->value,
            'approved_category' => BlocklistCategory::Social->value
        ]);

        $this->assertDatabaseHas('blocklist_domains', [
            'domain' => 'baddomain.com',
            'category' => BlocklistCategory::Social->value
        ]);
    }

    public function test_admin_can_reject_report()
    {
        $report = ReportedUrl::create([
            'url' => 'https://baddomain.com/test',
            'domain' => 'baddomain.com',
            'device_id' => $this->device->id,
            'status' => ReportStatus::Pending->value
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/reports/' . $report->id . '/reject');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Report rejected.',
            ]);

        $this->assertDatabaseHas('reported_urls', [
            'id' => $report->id,
            'status' => ReportStatus::Rejected->value,
        ]);

        $this->assertDatabaseMissing('blocklist_domains', [
            'domain' => 'baddomain.com',
        ]);
    }
}
