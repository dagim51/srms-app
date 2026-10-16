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
        Schema::create('accommodation_feature_links', function (Blueprint $table) {
            $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('property_features')->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->primary(['accommodation_id', 'feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_feature_links');
    }
};
