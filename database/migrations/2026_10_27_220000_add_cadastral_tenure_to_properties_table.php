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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('cadastral_parcel_id', 100)->nullable()->after('property_code');
            $table->string('block_number', 50)->nullable()->after('cadastral_parcel_id');
            $table->string('woreda_cadastre_sheet', 50)->nullable()->after('block_number');
            $table->string('land_tenure_type', 50)->default('leasehold_99yr')->after('woreda_cadastre_sheet');
            $table->unsignedInteger('lease_years_remaining')->nullable()->after('land_tenure_type');
            $table->string('carta_number', 100)->nullable()->after('lease_years_remaining');
            $table->foreignUuid('deed_document_id')->nullable()->after('carta_number')->constrained('documents')->nullOnDelete();

            $table->index(['cadastral_parcel_id', 'land_tenure_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['deed_document_id']);
            $table->dropIndex(['cadastral_parcel_id', 'land_tenure_type']);
            $table->dropColumn([
                'cadastral_parcel_id',
                'block_number',
                'woreda_cadastre_sheet',
                'land_tenure_type',
                'lease_years_remaining',
                'carta_number',
                'deed_document_id',
            ]);
        });
    }
};
