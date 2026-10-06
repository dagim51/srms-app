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
        $commonFields = function(Blueprint $table) {
            $table->string('name_en');
            $table->string('name_am')->nullable();
            $table->string('name_om')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->statusColumn(); // active, inactive
            $table->timestamps();
            $table->softDeletes();
            $table->auditColumns();
        };

        Schema::create('countries', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            $table->string('code', 2)->unique(); // e.g. ET
            $commonFields($table);
        });

        Schema::create('regions', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->constrained()->cascadeOnDelete();
            $commonFields($table);
        });

        Schema::create('zones', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            $table->foreignUuid('region_id')->constrained()->cascadeOnDelete();
            $commonFields($table);
        });

        Schema::create('cities', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            $table->foreignUuid('region_id')->constrained()->cascadeOnDelete();
            $commonFields($table);
        });

        Schema::create('sub_cities', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            $table->foreignUuid('city_id')->constrained()->cascadeOnDelete();
            $commonFields($table);
        });

        Schema::create('woredas', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            // A woreda can belong to a zone OR a sub_city
            $table->foreignUuid('zone_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('sub_city_id')->nullable()->constrained()->cascadeOnDelete();
            $commonFields($table);
        });

        Schema::create('kebeles', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            $table->foreignUuid('woreda_id')->constrained()->cascadeOnDelete();
            $commonFields($table);
        });

        Schema::create('neighborhoods', function (Blueprint $table) use ($commonFields) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kebele_id')->constrained()->cascadeOnDelete();
            $commonFields($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
        Schema::dropIfExists('kebeles');
        Schema::dropIfExists('woredas');
        Schema::dropIfExists('sub_cities');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('zones');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }
};
