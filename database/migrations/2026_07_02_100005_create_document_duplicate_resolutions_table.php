<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Module 9A.10 — audit trail for duplicate-file resolution decisions. */
    public function up(): void
    {
        Schema::create('document_duplicate_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete(); // the existing matched doc
            $table->foreignId('new_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('resolved_by')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // use_existing|overwrite|keep_both
            $table->text('justification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_duplicate_resolutions');
    }
};
