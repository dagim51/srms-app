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
        Schema::create('damage_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('accommodation_bookings')->cascadeOnDelete();
            $table->foreignUuid('host_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('guest_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount_claimed', 12, 2);
            $table->string('currency', 10)->default('ETB');
            $table->text('description');
            $table->json('evidence_photos')->nullable();
            $table->string('status', 30)->default('pending'); // pending, under_review, approved, rejected, settled
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('host_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_claims');
    }
};
