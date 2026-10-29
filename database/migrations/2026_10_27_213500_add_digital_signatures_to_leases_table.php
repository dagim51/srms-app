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
        Schema::table('leases', function (Blueprint $table) {
            $table->string('agreement_digest')->nullable()->after('status');
            $table->timestamp('signed_by_tenant_at')->nullable()->after('agreement_digest');
            $table->timestamp('signed_by_landlord_at')->nullable()->after('signed_by_tenant_at');
            $table->json('signatures')->nullable()->after('signed_by_landlord_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn(['agreement_digest', 'signed_by_tenant_at', 'signed_by_landlord_at', 'signatures']);
        });
    }
};
