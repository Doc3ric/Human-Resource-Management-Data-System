<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `personnel_id` already links an incident to a PGB employee record, but
     * nothing captured the involved party's name/position/office as a
     * snapshot at filing time — needed both for non-PGB individuals (who
     * have no plantilla_records row) and for the Copy-Furnished block on the
     * generated report. Additive/nullable only.
     */
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->string('involved_person_name')->nullable()->after('personnel_id');
            $table->string('involved_position')->nullable()->after('involved_person_name');
            $table->string('involved_office')->nullable()->after('involved_position');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn(['involved_person_name', 'involved_position', 'involved_office']);
        });
    }
};
