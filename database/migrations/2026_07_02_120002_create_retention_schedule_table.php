<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 1B.5 — tbl_retention_schedule. The canonical, Records-Officer-
     * editable NAP General Records Disposition Schedule reference. IDCC's
     * `document_retention_rules` (Module 9 Stage 8, keyed by doc_type_code)
     * links to a series here via record_series_id so there's a single
     * authoritative reference, not two competing retention tables.
     */
    public function up(): void
    {
        Schema::create('retention_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('record_series_title');
            $table->text('description')->nullable();
            $table->string('record_category', 30); // 201|Leave|Appointment|Disciplinary|Financial|LGU
            $table->string('time_value', 15); // PERMANENT|TEMPORARY
            $table->string('active_period')->nullable(); // free-text, e.g. "While in service"
            $table->string('storage_period')->nullable(); // free-text, e.g. "10 years after separation"
            $table->string('total_retention')->nullable();
            $table->string('disposition_action', 25); // PERMANENT_PRESERVATION|DESTRUCTION|REVIEW
            $table->string('legal_basis')->nullable();
            $table->string('nap_grds_item_ref')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('document_retention_rules', function (Blueprint $table) {
            $table->foreignId('record_series_id')->nullable()->after('doc_type_code')->constrained('retention_schedule')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('document_retention_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('record_series_id');
        });
        Schema::dropIfExists('retention_schedule');
    }
};
