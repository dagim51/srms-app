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
        Schema::create('accommodation_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('accommodation_id')->constrained('accommodations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->date('check_in_date');
            $table->date('check_out_date');
            
            $table->integer('guests')->default(1);
            $table->decimal('total_price', 12, 2);
            $table->string('status', 50)->default('pending'); // pending, confirmed, cancelled
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['accommodation_id', 'check_in_date', 'check_out_date'], 'acc_booking_dates_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_bookings');
    }
};
