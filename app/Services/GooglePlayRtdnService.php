<?php

namespace App\Services;

use App\Models\Subscription;
use Google\Client as GoogleClient;
use Google\Service\AndroidPublisher;
use Google\Service\AndroidPublisher\SubscriptionPurchaseV2;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class GooglePlayRtdnService
{
    /**
     * Google Play subscription notification types.
     * @see https://developer.android.com/google/play/billing/rtdn-reference#sub
     */
    private const NOTIFICATION_PURCHASED = 4;
    private const NOTIFICATION_RENEWED = 2;
    private const NOTIFICATION_RECOVERED = 1;
    private const NOTIFICATION_RESTARTED = 7;

    /**
     * Verify a purchase token against the Google Play Developer API and upsert
     * the resulting subscription state. Idempotent on `purchase_token`, so
     * renewals and duplicate/out-of-order notifications converge to the
     * authoritative state returned by Google.
     */
    public function ingest(string $purchaseToken, ?string $productId, ?int $notificationType, array $raw): void
    {
        $purchase = $this->verify($purchaseToken);

        if (!$purchase) {
            // Credentials missing or the verify call failed permanently; nothing
            // to persist. verify() already logged the reason.
            return;
        }

        $deviceId = $purchase->getExternalAccountIdentifiers()?->getObfuscatedExternalAccountId();
        $status = $this->resolveStatus($purchase->getSubscriptionState(), $notificationType);
        $expiresAt = $this->resolveExpiry($purchase);

        Subscription::updateOrCreate(
            ['purchase_token' => $purchaseToken],
            [
                'device_id' => $deviceId,
                'product_id' => $productId,
                'status' => $status,
                'starts_at' => $this->parseTime($purchase->getStartTime()),
                'expires_at' => $expiresAt,
                'latest_notification_type' => $notificationType,
                'raw_payload' => $raw,
            ]
        );
    }

    /**
     * Fetch the authoritative subscription resource from Google Play.
     * Returns null (and logs) when credentials are absent or the call fails.
     */
    public function verify(string $purchaseToken): ?SubscriptionPurchaseV2
    {
        $packageName = config('googleplay.package_name');
        $credentials = config('googleplay.credentials');

        if (!$packageName || !$credentials) {
            Log::warning('RTDN verify skipped: Google Play credentials not configured.', [
                'has_package' => (bool) $packageName,
                'has_credentials' => (bool) $credentials,
            ]);
            return null;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($credentials);
            $client->addScope(AndroidPublisher::ANDROIDPUBLISHER);

            $publisher = new AndroidPublisher($client);

            return $publisher->purchases_subscriptionsv2->get($packageName, $purchaseToken);
        } catch (Throwable $e) {
            Log::error('RTDN verify failed (purchases.subscriptionsv2.get).', [
                'purchase_token' => $purchaseToken,
                'error' => $e->getMessage(),
            ]);
            // Re-throw so the queued job retries on transient Google-API errors.
            throw $e;
        }
    }

    /**
     * Map Google's subscriptionState (preferred) — falling back to the RTDN
     * notificationType — onto our normalized status string.
     */
    private function resolveStatus(?string $subscriptionState, ?int $notificationType): string
    {
        return match ($subscriptionState) {
            'SUBSCRIPTION_STATE_ACTIVE' => 'active',
            'SUBSCRIPTION_STATE_CANCELED' => 'canceled',
            'SUBSCRIPTION_STATE_EXPIRED' => 'expired',
            'SUBSCRIPTION_STATE_ON_HOLD' => 'on_hold',
            'SUBSCRIPTION_STATE_IN_GRACE_PERIOD' => 'in_grace_period',
            'SUBSCRIPTION_STATE_PAUSED' => 'paused',
            'SUBSCRIPTION_STATE_PENDING', 'SUBSCRIPTION_STATE_PENDING_PURCHASE_CANCELED' => 'pending',
            default => $this->statusFromNotificationType($notificationType),
        };
    }

    private function statusFromNotificationType(?int $notificationType): string
    {
        return match ($notificationType) {
            self::NOTIFICATION_PURCHASED,
            self::NOTIFICATION_RENEWED,
            self::NOTIFICATION_RECOVERED,
            self::NOTIFICATION_RESTARTED => 'active',
            default => 'pending',
        };
    }

    /**
     * The furthest expiry across the subscription's line items.
     */
    private function resolveExpiry(SubscriptionPurchaseV2 $purchase): ?Carbon
    {
        $latest = null;

        foreach ($purchase->getLineItems() ?? [] as $lineItem) {
            $expiry = $this->parseTime($lineItem->getExpiryTime());
            if ($expiry && (!$latest || $expiry->greaterThan($latest))) {
                $latest = $expiry;
            }
        }

        return $latest;
    }

    private function parseTime(?string $time): ?Carbon
    {
        return $time ? Carbon::parse($time) : null;
    }
}
