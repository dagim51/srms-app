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
        Schema::create('property_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->statusColumn();
            $table->timestamps();
        });

        Schema::create('property_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->nullable()->constrained('property_categories');
            $table->string('code')->unique();
            $table->string('name_en');
            $table->statusColumn();
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('property_code')->unique(); // Unique property identifier
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->foreignUuid('category_id')->nullable()->constrained('property_categories');
            $table->foreignUuid('type_id')->nullable()->constrained('property_types');
            
            $table->foreignUuid('owner_user_id')->nullable()->constrained('users');
            $table->foreignUuid('organization_id')->nullable()->constrained('organizations');
            
            // location reference - optional link to location hierarchy
            $table->foreignUuid('location_id')->nullable()->constrained('neighborhoods');
            
            // physical attributes
            $table->decimal('land_area', 10, 2)->nullable();
            $table->decimal('building_area', 10, 2)->nullable();
            $table->integer('bedrooms')->nullable();
            $table->decimal('bathrooms', 4, 1)->nullable();
            $table->integer('floors')->nullable();
            $table->string('condition')->nullable();
            $table->integer('construction_year')->nullable();
            $table->integer('completion_pct')->default(100);

            $table->statusColumn(); // draft, pending_verification, verified, rejected, etc
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            
            $table->string('purpose'); // For Sale, For Rent, etc
            $table->decimal('price', 15, 2)->nullable();
            $table->string('currency', 3)->default('ETB');
            
            $table->statusColumn(); // draft, published, archived, etc
            $table->string('visibility')->default('public');
            
            $table->foreignUuid('owner_actor_id')->nullable()->constrained('users');
            
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->auditColumns();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('property_types');
        Schema::dropIfExists('property_categories');
    }
};
