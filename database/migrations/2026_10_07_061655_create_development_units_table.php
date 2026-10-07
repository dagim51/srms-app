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
        Schema::create('development_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('development_building_id')->constrained()->cascadeOnDelete();
            $table->string('floor_name')->nullable();
            $table->string('unit_number');
            $table->string('status')->default('available'); // available, reserved, sold
            $table->decimal('price', 15, 2)->nullable();
            
            // Optional tie to the generic Property core table if needed
            $table->foreignUuid('property_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('development_units');
    }
};
