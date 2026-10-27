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
        Schema::create('construction_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('development_projects')->cascadeOnDelete();
            $table->string('milestone_name');
            $table->decimal('completion_percentage', 5, 2);
            $table->timestamp('verified_at')->nullable();
            $table->foreignUuid('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('media_urls')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'completion_percentage']);
        });

        Schema::create('handover_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('unit_id')->constrained('development_units')->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, inspected, signed, completed
            $table->timestamp('keys_released_at')->nullable();
            $table->json('inspection_checklist')->nullable();
            $table->string('signed_document_url')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'buyer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handover_records');
        Schema::dropIfExists('construction_progress');
    }
};
