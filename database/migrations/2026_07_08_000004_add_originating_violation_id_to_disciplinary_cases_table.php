<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhancement Spec Sec. 4 — disciplinary_cases already IS the spec's
     * "admin_cases" table; it only needs a reference-only pointer back to
     * the violation that triggered its auto-spawn (manually-registered
     * cases, the existing/original use of this table, simply leave it null).
     */
    public function up(): void
    {
        Schema::table('disciplinary_cases', function (Blueprint $table) {
            $table->foreignId('originating_violation_id')->nullable()->after('incident_report_id')
                ->constrained('violations', 'violation_id')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('disciplinary_cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('originating_violation_id');
        });
    }
};
