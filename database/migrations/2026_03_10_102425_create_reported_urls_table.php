<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reported_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->string('domain');
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('approved_category')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reported_urls');
    }
};
