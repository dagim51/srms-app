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
        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Downpayment", "Handover"
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->integer('due_days_after_reservation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_plan_installments');
    }
};
