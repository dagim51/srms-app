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
        Schema::create('property_valuations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete(); // Who requested it
            $table->decimal('estimated_value', 15, 2); // Core estimate
            $table->decimal('confidence_score', 5, 2)->nullable(); // 0 to 100
            $table->decimal('projected_annual_rent', 15, 2)->nullable();
            $table->decimal('cap_rate', 8, 4)->nullable(); // e.g., 0.0550 for 5.5%
            $table->decimal('cash_on_cash_return', 8, 4)->nullable();
            $table->json('inputs'); // The variables used for calculation
            $table->boolean('is_authoritative')->default(false); // Distinguish estimate vs actual appraisal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_valuations');
    }
};
