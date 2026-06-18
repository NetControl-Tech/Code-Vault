<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores Google Play IAP subscriptions ingested from Real-Time Developer
     * Notifications (RTDN). Keyed by `purchase_token` so renewals and duplicate
     * or out-of-order notifications upsert idempotently.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Matches Device.device_id by VALUE, not by foreign key: an RTDN can
            // arrive before any devices row exists. Nullable until the obfuscated
            // account id is resolved from the Google Play Developer API.
            $table->string('device_id')->nullable()->index();

            $table->string('purchase_token')->unique();
            $table->string('product_id')->nullable();

            // Normalized state: active, canceled, expired, on_hold,
            // in_grace_period, paused, revoked, pending.
            $table->string('status')->default('pending');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();

            $table->unsignedTinyInteger('latest_notification_type')->nullable();
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            // Supports the active-lookup query in getSubscriptionInfo().
            $table->index(['device_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
