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
        Schema::create('disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Dispute participants
            $table->foreignUuid('complainant_id')->constrained('users')->cascadeOnDelete(); // User who filed the dispute
            $table->foreignUuid('respondent_id')->nullable()->constrained('users')->nullOnDelete(); // User being complained against
            $table->foreignUuid('assigned_moderator_id')->nullable()->constrained('users')->nullOnDelete(); // Moderator handling the case
            
            // Disputable entity (polymorphic)
            $table->uuidMorphs('disputable'); // Can be Listing, User, AccommodationBooking, etc.
            
            // Dispute details
            $table->string('type'); // fraud, misrepresentation, payment_issue, service_quality, contract_dispute, other
            $table->string('subject');
            $table->text('description');
            $table->decimal('amount_in_dispute', 15, 2)->nullable(); // Monetary value if applicable
            $table->string('currency', 3)->default('ETB');
            
            // Status workflow
            $table->string('status', 50)->default('open'); // open, under_review, awaiting_evidence, awaiting_response, mediated, resolved, closed, escalated
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->string('resolution', 50)->nullable(); // refund, partial_refund, replacement, compensation, dismissed, mutual_agreement
            $table->text('resolution_notes')->nullable();
            
            // Timeline
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // SLA tracking
            $table->integer('sla_hours')->nullable(); // Expected resolution time in hours
            $table->boolean('sla_breached')->default(false);
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes (uuidMorphs already creates disputable_type_disputable_id index)
            $table->index(['status', 'priority']);
            $table->index(['complainant_id', 'status']);
            $table->index(['assigned_moderator_id', 'status']);
            $table->index(['opened_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};