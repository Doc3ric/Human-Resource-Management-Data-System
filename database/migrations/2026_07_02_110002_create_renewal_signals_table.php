<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 1A.2 — tbl_renewal_signal. Detection is distributed (any module
     * may insert a signal); the source of truth stays single (only
     * RenewalCommitService, driven by an AUTHORITATIVE signal or explicit
     * authority, may ever set plantilla_records.is_renewed = true).
     */
    public function up(): void
    {
        Schema::create('renewal_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->cascadeOnDelete();
            $table->string('rating_period'); // e.g. 2026_1st_semester
            $table->string('source_module', 20); // PERFORMANCE|RECORDS|LEAVE|RECRUITMENT|PAYROLL|OTHER
            $table->string('signal_type'); // e.g. IPCR_TARGET_SUBMITTED, CONTRACT_UPLOADED, LEAVE_FILED
            $table->string('signal_strength', 15); // AUTHORITATIVE|CORROBORATING|WEAK
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['plantilla_record_id', 'rating_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_signals');
    }
};
