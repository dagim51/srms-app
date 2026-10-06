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
        Schema::create('amenities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category')->nullable(); // e.g. indoor, outdoor, security
            $table->string('code')->unique();
            $table->string('name_en');
            $table->statusColumn(); // active, inactive
            $table->timestamps();
        });

        Schema::create('property_amenities', function (Blueprint $table) {
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('amenity_id')->constrained('amenities')->cascadeOnDelete();
            $table->string('value')->nullable(); // For cases where amenity has a value (e.g. '2' parking spots)
            $table->primary(['property_id', 'amenity_id']);
        });

        Schema::create('property_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->statusColumn();
            $table->timestamps();
        });

        Schema::create('property_feature_links', function (Blueprint $table) {
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignUuid('feature_id')->constrained('property_features')->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->primary(['property_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_feature_links');
        Schema::dropIfExists('property_features');
        Schema::dropIfExists('property_amenities');
        Schema::dropIfExists('amenities');
    }
};
