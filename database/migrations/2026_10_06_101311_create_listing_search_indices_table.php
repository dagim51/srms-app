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
        Schema::create('listing_search_indices', function (Blueprint $table) {
            $table->uuid('listing_id')->primary();
            
            // Searchable fields
            $table->string('title');
            $table->text('description')->nullable();
            
            // Filters
            $table->string('purpose');
            $table->decimal('price', 15, 2);
            $table->string('currency', 3);
            
            $table->string('property_category_code')->nullable();
            $table->string('property_type_code')->nullable();
            
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            
            // Location
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->json('amenities')->nullable();
            
            $table->timestamp('published_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_search_indices');
    }
};
