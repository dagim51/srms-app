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
        Schema::create('external_calendar_syncs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('accommodation_id')->constrained('accommodations')->cascadeOnDelete();

            $table->string('name', 100);
            $table->text('url');
            $table->integer('sync_interval_minutes')->default(60);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('status', 30)->default('active'); // active, synced, failed, paused
            $table->text('last_error')->nullable();
            $table->integer('events_count')->default(0);

            $table->timestamps();

            $table->index(['accommodation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_calendar_syncs');
    }
};
