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
            $table->foreignUuid('region_id')->nullable()->after('location_id')->constrained('regions')->nullOnDelete();
            $table->foreignUuid('zone_id')->nullable()->after('region_id')->constrained('zones')->nullOnDelete();
            $table->foreignUuid('sub_city_id')->nullable()->after('zone_id')->constrained('sub_cities')->nullOnDelete();
            $table->foreignUuid('woreda_id')->nullable()->after('sub_city_id')->constrained('woredas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['zone_id']);
            $table->dropForeign(['sub_city_id']);
            $table->dropForeign(['woreda_id']);
            $table->dropColumn(['region_id', 'zone_id', 'sub_city_id', 'woreda_id']);
        });
    }
};
