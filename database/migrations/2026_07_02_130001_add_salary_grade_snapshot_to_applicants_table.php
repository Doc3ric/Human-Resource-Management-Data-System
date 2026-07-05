<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 3.2 — a snapshot of the Master Plantilla's salary grade at the
     * time this applicant record was tied to a position, so later actions
     * can detect drift (item_no/office already existed on this table).
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('salary_grade_snapshot')->nullable()->after('item_no');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('salary_grade_snapshot');
        });
    }
};
