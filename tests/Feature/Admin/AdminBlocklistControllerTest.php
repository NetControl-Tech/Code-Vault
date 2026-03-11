<?php

namespace Tests\Feature\Admin;

use App\Enums\BlocklistCategory;
use App\Models\BlocklistDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminBlocklistControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');
    }

    public function test_admin_can_get_blocklist_domains()
    {
        BlocklistDomain::create(['domain' => 'baddomain.com', 'category' => BlocklistCategory::Family->value]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/blocklists?category=' . BlocklistCategory::Family->value);

        $response->assertStatus(200)
            ->assertJsonPath('data.0.domain', 'baddomain.com');
    }

    public function test_admin_can_add_domain()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/blocklists', [
                'domain' => 'Adsdomain.com',
                'category' => BlocklistCategory::Ads->value
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Domain added successfully.'
            ]);

        $this->assertDatabaseHas('blocklist_domains', [
            'domain' => 'adsdomain.com',
            'category' => BlocklistCategory::Ads->value
        ]);
    }

    public function test_admin_cannot_add_duplicate_domain_to_same_category()
    {
        BlocklistDomain::create(['domain' => 'baddomain.com', 'category' => BlocklistCategory::Family->value]);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/blocklists', [
                'domain' => 'baddomain.com',
                'category' => BlocklistCategory::Family->value
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['domain']);
    }

    public function test_admin_can_update_domain()
    {
        $domain = BlocklistDomain::create([
            'domain' => 'baddomain.com',
            'category' => BlocklistCategory::Family->value
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson('/api/admin/blocklists/' . $domain->id, [
                'domain' => 'newbaddomain.com',
                'category' => BlocklistCategory::Privacy->value
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Domain updated successfully.'
            ]);

        $this->assertDatabaseHas('blocklist_domains', [
            'id' => $domain->id,
            'domain' => 'newbaddomain.com',
            'category' => BlocklistCategory::Privacy->value
        ]);
    }

    public function test_admin_can_delete_domain()
    {
        $domain = BlocklistDomain::create([
            'domain' => 'baddomain.com',
            'category' => BlocklistCategory::Family->value
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/admin/blocklists/' . $domain->id);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Domain deleted successfully.'
            ]);

        $this->assertDatabaseMissing('blocklist_domains', [
            'id' => $domain->id
        ]);
    }

    public function test_admin_can_bulk_upload_domains()
    {
        $fileContent = "domain1.com\ndomain2.com\ndomain3.com\ndomain1.com";
        $file = UploadedFile::fake()->createWithContent('upload.txt', $fileContent);

        $response = $this->actingAs($this->admin)
            ->post('/api/admin/blocklists/bulk-upload', [
                'file' => $file,
                'category' => BlocklistCategory::Ads->value
            ], [
                'Accept' => 'application/json'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'inserted_count' => 3,
                'duplicate_count' => 0
            ]);

        $this->assertEquals(3, BlocklistDomain::where('category', BlocklistCategory::Ads->value)->count());
    }
}
