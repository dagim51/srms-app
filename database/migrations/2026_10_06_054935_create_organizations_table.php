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
        Schema::create('organizations', function (Blueprint $table) {
            // 1. UUID Convention
            $table->uuid('id')->primary();

            // 2. Core Columns
            $table->string('name');
            $table->string('slug')->unique(); // Unique index

            // 3. Status/Enum Convention
            $table->statusColumn(); // Uses macro from AppServiceProvider

            // 4. JSON metadata convention
            $table->json('settings')->nullable();

            // 5. Timestamps and Soft Deletion
            $table->timestamps();
            $table->softDeletes();

            // 6. Audit Metadata
            $table->auditColumns(); // Uses macro from AppServiceProvider
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
