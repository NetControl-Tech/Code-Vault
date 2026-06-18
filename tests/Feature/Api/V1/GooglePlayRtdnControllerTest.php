<?php

namespace Tests\Feature\Api\V1;

use App\Jobs\ProcessRtdnNotification;
use App\Models\Subscription;
use App\Services\GooglePlayRtdnService;
use Google\Service\AndroidPublisher\ExternalAccountIdentifiers;
use Google\Service\AndroidPublisher\SubscriptionPurchaseLineItem;
use Google\Service\AndroidPublisher\SubscriptionPurchaseV2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GooglePlayRtdnControllerTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-rtdn-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['googleplay.rtdn_secret' => self::SECRET]);
    }

    /** Build a base64 Pub/Sub envelope around a subscription notification. */
    private function envelope(array $payload): array
    {
        return [
            'message' => [
                'data' => base64_encode(json_encode($payload)),
            ],
            'subscription' => 'projects/p/subscriptions/s',
        ];
    }

    public function test_webhook_rejects_missing_secret_with_envelope(): void
    {
        $response = $this->postJson('/api/webhooks/google-play/rtdn', $this->envelope([
            'subscriptionNotification' => ['purchaseToken' => 'tok', 'notificationType' => 4],
        ]));

        $response->assertStatus(401)
            ->assertExactJson(['status' => false, 'message' => 'Unauthorized.']);
    }

    public function test_webhook_dispatches_job_for_subscription_notification(): void
    {
        Queue::fake();

        $payload = [
            'subscriptionNotification' => [
                'purchaseToken' => 'purchase-token-123',
                'subscriptionId' => 'premium_monthly',
                'notificationType' => 4,
            ],
        ];

        $response = $this->postJson(
            '/api/webhooks/google-play/rtdn?token=' . self::SECRET,
            $this->envelope($payload)
        );

        $response->assertStatus(200)->assertJson(['status' => true]);

        Queue::assertPushed(ProcessRtdnNotification::class, function ($job) {
            return $job->purchaseToken === 'purchase-token-123'
                && $job->productId === 'premium_monthly'
                && $job->notificationType === 4;
        });
    }

    public function test_webhook_acks_test_notification_without_dispatch(): void
    {
        Queue::fake();

        $response = $this->postJson(
            '/api/webhooks/google-play/rtdn?token=' . self::SECRET,
            $this->envelope(['testNotification' => ['version' => '1.0']])
        );

        $response->assertStatus(200)->assertJson(['status' => true]);
        Queue::assertNothingPushed();
    }

    public function test_ingest_upserts_active_subscription_from_verified_purchase(): void
    {
        // Note: Google client model setters return void, so set fields individually.
        $purchase = new SubscriptionPurchaseV2();
        $purchase->setSubscriptionState('SUBSCRIPTION_STATE_ACTIVE');
        $purchase->setStartTime(now()->toRfc3339String());

        $externalIds = new ExternalAccountIdentifiers();
        $externalIds->setObfuscatedExternalAccountId('device-abc');
        $purchase->setExternalAccountIdentifiers($externalIds);

        $lineItem = new SubscriptionPurchaseLineItem();
        $lineItem->setExpiryTime(now()->addDays(30)->toRfc3339String());
        $purchase->setLineItems([$lineItem]);

        // Stub the Google Play verify() call; exercise the real upsert logic.
        $service = $this->partialMock(GooglePlayRtdnService::class, function ($mock) use ($purchase) {
            $mock->shouldReceive('verify')->once()->with('tok-xyz')->andReturn($purchase);
        });

        $service->ingest('tok-xyz', 'premium_monthly', 4, ['raw' => true]);

        $this->assertDatabaseHas('subscriptions', [
            'purchase_token' => 'tok-xyz',
            'device_id' => 'device-abc',
            'product_id' => 'premium_monthly',
            'status' => 'active',
        ]);
    }

    public function test_get_subscription_info_returns_active_for_iap_subscription(): void
    {
        $device = \App\Models\Device::factory()->create(['device_id' => 'device-abc']);

        Subscription::create([
            'device_id' => 'device-abc',
            'purchase_token' => 'tok-1',
            'product_id' => 'premium_monthly',
            'status' => 'active',
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($device, 'sanctum')
            ->postJson('/api/get-subscription-info', ['device_id' => 'device-abc']);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'is_active' => true,
            ]);
    }

    public function test_get_subscription_info_inactive_when_no_subscription(): void
    {
        $device = \App\Models\Device::factory()->create(['device_id' => 'device-none']);

        $response = $this->actingAs($device, 'sanctum')
            ->postJson('/api/get-subscription-info', ['device_id' => 'device-none']);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'is_active' => false,
                'expiry_date' => null,
            ]);
    }
}
