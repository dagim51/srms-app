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
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('placement')->default('home_header')->after('channel');
            $table->string('headline')->nullable()->after('placement');
            $table->text('subheadline')->nullable()->after('headline');
            $table->string('banner_image_url')->nullable()->after('subheadline');
            $table->string('target_url')->nullable()->after('banner_image_url');
            $table->string('cta_text')->default('Learn More')->after('target_url');
            $table->unsignedBigInteger('impressions_count')->default(0)->after('cta_text');
            $table->unsignedBigInteger('clicks_count')->default(0)->after('impressions_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'placement',
                'headline',
                'subheadline',
                'banner_image_url',
                'target_url',
                'cta_text',
                'impressions_count',
                'clicks_count',
            ]);
        });
    }
};
