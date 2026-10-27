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
        if (Schema::hasTable('listings') && ! Schema::hasColumn('listings', 'quality_score')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->integer('quality_score')->default(0)->after('status');
            });
        }

        if (! Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('listing_id')->constrained('listings')->cascadeOnDelete();
                $table->string('tier')->default('basic'); // basic, featured, premium, urgent
                $table->timestamp('start_date')->useCurrent();
                $table->timestamp('end_date');
                $table->decimal('amount_paid', 12, 2)->default(0.00);
                $table->string('status')->default('active'); // active, pending, expired, cancelled
                $table->integer('impressions_count')->default(0);
                $table->integer('clicks_count')->default(0);
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['listing_id', 'status', 'end_date']);
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');

        if (Schema::hasTable('listings') && Schema::hasColumn('listings', 'quality_score')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn('quality_score');
            });
        }
    }
};
