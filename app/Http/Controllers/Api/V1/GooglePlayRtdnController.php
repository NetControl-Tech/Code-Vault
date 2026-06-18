<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessRtdnNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GooglePlayRtdnController extends Controller
{
    /**
     * Receive a Google Play Real-Time Developer Notification (RTDN) pushed by
     * Google Pub/Sub. Authenticated by the `rtdn` shared-secret middleware.
     *
     * Ack semantics: return 2xx on success AND on permanently-unprocessable
     * messages so Pub/Sub stops redelivering. The real work (verify + upsert)
     * is queued, so transient Google-API failures are retried by the job, not
     * by Pub/Sub.
     */
    public function handle(Request $request)
    {
        $envelope = $request->json('message');
        $payload = $this->decodeMessage($envelope);

        if ($payload === null) {
            // Malformed / undecodable envelope — ack and drop.
            Log::warning('RTDN: undecodable Pub/Sub message.', ['envelope' => $envelope]);
            return response()->json(['status' => true], 200);
        }

        // Google sends a one-off test notification when the topic is configured.
        if (isset($payload['testNotification'])) {
            Log::info('RTDN: test notification received.', $payload['testNotification']);
            return response()->json(['status' => true], 200);
        }

        $notification = $payload['subscriptionNotification'] ?? null;
        $purchaseToken = $notification['purchaseToken'] ?? null;

        // Only subscription notifications carry a purchase token we can verify;
        // anything else (voided purchases, one-time products) is acked & dropped.
        if (!$purchaseToken) {
            return response()->json(['status' => true], 200);
        }

        ProcessRtdnNotification::dispatch(
            $purchaseToken,
            $notification['subscriptionId'] ?? null,
            isset($notification['notificationType']) ? (int) $notification['notificationType'] : null,
            $payload,
        );

        return response()->json(['status' => true], 200);
    }

    /**
     * Decode the base64-encoded `message.data` field of a Pub/Sub envelope.
     *
     * @return array<string, mixed>|null
     */
    private function decodeMessage(mixed $message): ?array
    {
        if (!is_array($message) || !isset($message['data']) || !is_string($message['data'])) {
            return null;
        }

        $decoded = base64_decode($message['data'], true);
        if ($decoded === false) {
            return null;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) ? $payload : null;
    }
}
