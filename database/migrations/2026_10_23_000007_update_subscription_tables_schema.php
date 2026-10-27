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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('name')->default('Free Plan');
            $table->string('slug')->nullable()->unique();
            $table->decimal('price', 12, 2)->default(0.00);
            $table->string('billing_cycle', 20)->default('monthly'); // monthly, yearly
            $table->integer('max_listings')->default(3);
            $table->integer('featured_listings_allowed')->default(0);
            $table->boolean('analytics_access')->default(false);
            $table->boolean('is_active')->default(true);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('status', 30)->default('active'); // active, expired, cancelled, past_due
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn([
                'user_id',
                'subscription_plan_id',
                'status',
                'current_period_start',
                'current_period_end',
                'trial_ends_at',
                'cancelled_at',
            ]);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'slug',
                'price',
                'billing_cycle',
                'max_listings',
                'featured_listings_allowed',
                'analytics_access',
                'is_active',
            ]);
        });
    }
};
