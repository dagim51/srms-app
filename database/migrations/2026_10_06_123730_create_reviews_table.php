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
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('reviewer_id')->constrained('users')->cascadeOnDelete();

            $table->uuidMorphs('reviewable');

            $table->tinyInteger('rating')->unsigned(); // 1 to 5
            $table->text('comment')->nullable();

            $table->string('status', 50)->default('published'); // published, flagged, removed

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['reviewer_id', 'reviewable_type', 'reviewable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
