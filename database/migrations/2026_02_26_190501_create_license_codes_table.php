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
        Schema::create('license_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('serial')->unique(); // 'int, unique, indexed' unique implies indexed
            $table->string('pin_hash')->index();
            $table->enum('status', ['inactive', 'active', 'redeemed'])->default('inactive');
            $table->dateTime('activated_at')->nullable();
            $table->dateTime('redeemed_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->integer('duration_days')->default(30);
            $table->index(['status', 'serial']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_codes');
    }
};
