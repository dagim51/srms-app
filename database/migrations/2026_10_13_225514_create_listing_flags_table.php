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
        Schema::create('listing_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('assigned_moderator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('reason'); // fraud, misrepresentation, prohibited_content, spam, duplicate, scam, safety_concern, other
            $table->text('description')->nullable();
            $table->string('severity', 20)->default('medium'); // low, medium, high, critical

            $table->string('status', 50)->default('pending'); // pending, under_review, dismissed, validated, escalated_to_dispute
            $table->text('moderator_notes')->nullable();

            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['listing_id', 'status']);
            $table->index(['reporter_id', 'created_at']);
            $table->index(['assigned_moderator_id', 'status']);
            $table->index(['reason', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_flags');
    }
};
