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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic relation to what is being paid for (e.g. AccommodationBooking, Reservation)
            $table->uuidMorphs('payable');

            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('ETB');
            $table->string('payment_method', 50)->default('bank_transfer'); // chapa, telebirr, card, bank_transfer

            $table->string('transaction_reference')->unique()->nullable();
            $table->string('status', 50)->default('pending'); // pending, completed, failed, refunded

            $table->json('metadata')->nullable(); // Gateway responses, etc.

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
