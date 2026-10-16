<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->integer('minimum_stay')->default(1)->after('rules');
            $table->integer('maximum_stay')->default(30)->after('minimum_stay');
            $table->string('cancellation_policy', 50)->default('flexible')->after('maximum_stay');
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn(['minimum_stay', 'maximum_stay', 'cancellation_policy']);
        });
    }
};
