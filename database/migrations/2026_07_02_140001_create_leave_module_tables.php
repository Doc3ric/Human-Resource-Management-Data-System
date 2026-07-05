<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 10-M2 — Leave (Omnibus Rules on Leave, CSC MC No. 41 s.1998).
     * A new domain — nothing like this existed in the app before.
     */
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // VL, SL, SPL, ...
            $table->string('name');
            $table->unsignedInteger('max_days_per_year')->nullable();
            $table->boolean('requires_medical_cert_over_days')->nullable(); // e.g. 5
            $table->unsignedInteger('advance_notice_days')->nullable(); // e.g. 5 for VL
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('earned_days', 6, 3)->default(0);
            $table->decimal('used_days', 6, 3)->default(0);
            $table->timestamps();

            $table->unique(['plantilla_record_id', 'leave_type_id', 'year']);
        });

        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('days_requested', 6, 3);
            $table->date('filed_at');
            $table->string('status', 20)->default('pending'); // pending|approved|disapproved
            $table->text('reason')->nullable();
            $table->text('disapproval_reason')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete(); // supporting doc, via IDCC
            $table->foreignId('filed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
