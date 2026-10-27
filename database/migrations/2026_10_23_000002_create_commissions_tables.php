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
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('rule_type', 50)->default('percentage'); // percentage, fixed, tiered
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('fixed_amount', 15, 2)->nullable();
            $table->decimal('min_deal_amount', 15, 2)->nullable();
            $table->decimal('max_deal_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agent_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('deal_id')->nullable();
            $table->string('deal_type', 50)->nullable(); // offer, lease, sale
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('rate_percentage', 5, 2)->nullable();
            $table->decimal('commission_amount', 15, 2);
            $table->string('currency', 10)->default('ETB');
            $table->string('status', 30)->default('pending'); // pending, approved, paid, cancelled
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commission_rules');
    }
};
