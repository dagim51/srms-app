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
        if (! Schema::hasTable('cancellation_policies')) {
            Schema::create('cancellation_policies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name'); // flexible, moderate, strict, non_refundable
                $table->integer('flexible_days')->default(1); // days before check-in for full/partial refund
                $table->decimal('refund_percentage', 5, 2)->default(100.00); // 100% refund
                $table->decimal('fee_percentage', 5, 2)->default(0.00); // cancellation fee percentage
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cancellation_records')) {
            Schema::create('cancellation_records', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('booking_id')->constrained('accommodation_bookings')->cascadeOnDelete();
                $table->foreignUuid('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('policy_id')->nullable()->constrained('cancellation_policies')->nullOnDelete();
                $table->decimal('original_amount', 15, 2);
                $table->decimal('refund_amount', 15, 2);
                $table->decimal('fee_amount', 15, 2);
                $table->string('reason')->nullable();
                $table->string('status')->default('processed'); // processed, pending, disputed
                $table->timestamps();

                $table->index('booking_id');
            });
        }

        if (! Schema::hasTable('booking_guests')) {
            Schema::create('booking_guests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('booking_id')->constrained('accommodation_bookings')->cascadeOnDelete();
                $table->string('full_name');
                $table->string('id_passport_number')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->string('nationality')->nullable();
                $table->string('phone')->nullable();
                $table->timestamps();

                $table->index('booking_id');
            });
        }

        if (! Schema::hasTable('check_in_outs')) {
            Schema::create('check_in_outs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('booking_id')->constrained('accommodation_bookings')->cascadeOnDelete();
                $table->string('direction', 20); // check_in, check_out
                $table->timestamp('action_time');
                $table->foreignUuid('staff_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('room_condition_notes')->nullable();
                $table->boolean('key_handed_over')->default(false);
                $table->timestamps();

                $table->index(['booking_id', 'direction']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_in_outs');
        Schema::dropIfExists('booking_guests');
        Schema::dropIfExists('cancellation_records');
        Schema::dropIfExists('cancellation_policies');
    }
};
