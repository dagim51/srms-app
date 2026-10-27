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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 50)->default('Identity Document'); // Identity Document, Contract, Business Document, Financial Document
            $table->string('status', 30)->default('pending'); // pending, verified, active, rejected
            $table->string('file_path');
            $table->string('mime_type', 100)->default('application/pdf');
            $table->bigInteger('file_size')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('version_number', 20)->default('v1.0');
            $table->string('file_path');
            $table->string('checksum_sha256', 64)->nullable();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_summary')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'version_number']);
        });

        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('action', 30)->default('view'); // view, download, update
            $table->timestamps();

            $table->index(['document_id', 'action']);
        });

        Schema::create('verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->default('Identity Verification'); // Identity Verification, Income Verification, Business KYC
            $table->string('status', 30)->default('pending_review'); // pending_review, approved, rejected
            $table->string('risk_score', 20)->default('low'); // low, medium, high
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
        Schema::dropIfExists('document_access_logs');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
