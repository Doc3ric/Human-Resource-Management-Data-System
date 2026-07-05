<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 10-M3 — RACCS-Confidential case registry. The formal
     * disciplinary process, distinct from IDCC's document-level RACCS wall
     * (which stores/classifies files) — this is the CASE entity itself.
     */
    public function up(): void
    {
        Schema::create('disciplinary_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_no')->unique();
            $table->unsignedBigInteger('personnel_id');
            $table->foreignId('incident_report_id')->nullable()->constrained('incident_reports')->nullOnDelete();
            $table->text('formal_charge');
            $table->string('offense_classification', 20); // light|less_grave|grave
            $table->string('status', 20)->default('registered'); // registered|under_investigation|hearing|decided|appealed|closed
            $table->text('decision')->nullable();
            $table->text('penalty')->nullable();
            $table->date('decided_at')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_cases');
    }
};
