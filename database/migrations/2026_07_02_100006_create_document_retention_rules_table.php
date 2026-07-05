<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Module 9 Stage 8 — retention registry per doc_type_code (R.A. 9470 + NAP RDS). */
    public function up(): void
    {
        Schema::create('document_retention_rules', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type_code')->unique();
            $table->string('rule_label');
            $table->unsignedInteger('retention_years')->nullable(); // null = permanent
            $table->boolean('is_permanent')->default(false);
            $table->boolean('disposal_requires_ledger_verification')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_retention_rules');
    }
};
