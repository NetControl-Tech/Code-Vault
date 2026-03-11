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
        Schema::create('blocklist_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->string('category')->index();
            $table->timestamps();

            $table->unique(['domain', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocklist_domains');
    }
};
