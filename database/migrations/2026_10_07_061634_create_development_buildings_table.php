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
        Schema::create('development_buildings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('development_project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('total_floors')->default(1);
            $table->string('status')->default('planning');
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('development_buildings');
    }
};
