<?php

namespace App\Jobs;

use App\Services\GooglePlayRtdnService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRtdnNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted (covers transient Google-API
     * failures during verification).
     */
    public int $tries = 5;

    /**
     * Backoff between retries, in seconds.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(
        public string $purchaseToken,
        public ?string $productId,
        public ?int $notificationType,
        public array $rawMessage,
    ) {
    }

    /**
     * Verify the purchase with Google Play and upsert the subscription.
     */
    public function handle(GooglePlayRtdnService $service): void
    {
        $service->ingest(
            $this->purchaseToken,
            $this->productId,
            $this->notificationType,
            $this->rawMessage,
        );
    }
}
