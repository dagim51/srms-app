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
        Schema::create('dispute_evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dispute_id')->constrained('disputes')->cascadeOnDelete();
            $table->foreignUuid('submitted_by')->constrained('users')->cascadeOnDelete();

            $table->string('type'); // document, screenshot, communication, transaction_record, contract, other
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // Local storage path
            $table->string('file_url')->nullable(); // Public URL if hosted externally
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->json('metadata')->nullable(); // Additional context (e.g., page numbers, timestamps)

            $table->boolean('is_verified')->default(false);
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['dispute_id', 'type']);
            $table->index(['submitted_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispute_evidence');
    }
};
