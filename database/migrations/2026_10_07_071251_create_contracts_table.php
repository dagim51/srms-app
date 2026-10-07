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
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('body_content');
            $table->uuid('contractable_id')->nullable();
            $table->string('contractable_type')->nullable();
            $table->string('status')->default('draft'); // draft, sent, partial_signed, fully_signed, expired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
