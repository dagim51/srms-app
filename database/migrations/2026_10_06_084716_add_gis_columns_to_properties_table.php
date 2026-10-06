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
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            // SQLite does not support native spatial index properly without SpatiaLite,
            // but Laravel abstracts geometry. If it fails, we will fall back.
            try {
                $table->geometry('property_point')->nullable();
                $table->geometry('property_boundary')->nullable();
                // $table->spatialIndex('property_point'); // Often causes issue on basic SQLite
            } catch (\Exception $e) {
                // Ignore if driver unsupported in testing
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'property_point', 'property_boundary']);
        });
    }
};
