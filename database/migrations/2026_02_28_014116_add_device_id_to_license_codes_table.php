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
        Schema::table('license_codes', function (Blueprint $table) {
            if (Schema::hasColumn('license_codes', 'device_hardware_id')) {
                $table->dropColumn('device_hardware_id');
            }
        });

        Schema::table('license_codes', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_codes', function (Blueprint $table) {
            $table->dropColumn('device_id');
        });
    }
};
