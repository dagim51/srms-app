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
        Schema::create('user_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete(); // The flagged user
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete(); // Who reported
            $table->foreignUuid('assigned_moderator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('reason'); // fraud, harassment, spam, fake_identity, payment_fraud, terms_violation, other
            $table->text('description')->nullable();
            $table->string('severity', 20)->default('medium'); // low, medium, high, critical

            $table->string('status', 50)->default('pending'); // pending, under_review, dismissed, validated, escalated_to_dispute, user_suspended
            $table->text('moderator_notes')->nullable();

            // Related entities (optional polymorphic relation)
            $table->uuid('context_id')->nullable();
            $table->string('context_type')->nullable();

            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['reporter_id', 'created_at']);
            $table->index(['assigned_moderator_id', 'status']);
            $table->index(['reason', 'severity']);
            $table->index(['context_type', 'context_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_flags');
    }
};
