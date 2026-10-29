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
        Schema::create('snagging_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('unit_id')->constrained('development_units')->cascadeOnDelete();
            $table->foreignUuid('handover_record_id')->nullable()->constrained('handover_records')->nullOnDelete();
            $table->foreignUuid('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('room_area', 100);
            $table->text('description');
            $table->json('photo_urls')->nullable();
            $table->string('severity', 30)->default('cosmetic'); // cosmetic, major, structural
            $table->string('status', 30)->default('open'); // open, rectified, verified
            $table->timestamp('rectified_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'status']);
            $table->index(['buyer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snagging_items');
    }
};
