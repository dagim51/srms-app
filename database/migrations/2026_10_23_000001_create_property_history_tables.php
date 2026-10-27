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
        if (Schema::hasTable('properties') && ! Schema::hasColumn('properties', 'price')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->decimal('price', 15, 2)->nullable();
                $table->string('currency', 10)->default('ETB');
            });
        }

        Schema::create('property_price_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->string('currency', 10)->default('ETB');
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['property_id', 'created_at']);
        });

        Schema::create('property_status_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['property_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_status_history');
        Schema::dropIfExists('property_price_history');

        if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'price')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn(['price', 'currency']);
            });
        }
    }
};
