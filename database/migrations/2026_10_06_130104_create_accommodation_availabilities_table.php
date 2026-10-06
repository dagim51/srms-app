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
        Schema::create('accommodation_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('accommodation_id')->constrained('accommodations')->cascadeOnDelete();
            
            $table->date('start_date');
            $table->date('end_date');
            
            $table->string('type', 50)->default('blocked'); // blocked, booked
            $table->string('reason')->nullable();
            
            $table->timestamps();
            
            // To prevent double booking logic we might need DB-level constraints for date overlaps,
            // but for simplicity we rely on app-level and normal indexing here, or a PostgreSQL exclusion constraint if enabled.
            // Since we use generic driver compatibility, we use regular index.
            $table->index(['accommodation_id', 'start_date', 'end_date'], 'acc_av_dates_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_availabilities');
    }
};
