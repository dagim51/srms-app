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
        Schema::create('accommodation_pricing_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('accommodation_id')->constrained('accommodations')->cascadeOnDelete();

            $table->string('type', 50); // seasonal, weekend, special

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('price_modifier_type', 20)->default('fixed'); // fixed, percentage
            $table->decimal('price_modifier_value', 12, 2);

            $table->integer('priority')->default(0);

            $table->timestamps();

            $table->index(['accommodation_id', 'start_date', 'end_date'], 'acc_price_dates_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_pricing_rules');
    }
};
