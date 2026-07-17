<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhancement Spec Sec. 4 — immutable ledger feeding the 201-file view.
     * A thin pointer table: it references the real record in either
     * leave_violations or incident_reports rather than duplicating their
     * fields, since both already carry the substantive violation data.
     */
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id('violation_id');
            $table->foreignId('plantilla_record_id')->constrained('plantilla_records')->restrictOnDelete();
            $table->string('source_module', 20); // Leave | IncidentReport
            $table->unsignedBigInteger('source_record_id'); // leave_violations.id or incident_reports.id, per source_module
            $table->string('violation_type');
            $table->string('legal_basis');
            $table->string('severity_level', 10); // Minor | Grave
            $table->timestamp('date_created')->useCurrent();

            $table->index(['source_module', 'source_record_id']);
            $table->index(['plantilla_record_id', 'violation_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
