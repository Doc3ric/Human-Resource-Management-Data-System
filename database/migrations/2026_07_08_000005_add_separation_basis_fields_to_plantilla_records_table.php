<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhancement Spec Sec. 6 — plantilla_records already IS the real
     * separation data model (nature_of_separation/date_separated); this only
     * adds the two fields the spec's report needs that aren't there yet.
     */
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->string('basis_reference')->nullable()->after('date_separated'); // e.g. "R.A. 8291" for retirement, "RACCS" for termination
            $table->foreignId('originating_admin_case_id')->nullable()->after('basis_reference')
                ->constrained('disciplinary_cases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('originating_admin_case_id');
            $table->dropColumn('basis_reference');
        });
    }
};
