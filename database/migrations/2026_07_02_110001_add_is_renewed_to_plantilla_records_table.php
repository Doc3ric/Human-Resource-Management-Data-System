<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 1.1 — the active-status gate for casual/JO/contractual
     * personnel. Defaults true for ALL existing rows (a grandfather clause)
     * so the ~2,300 already-live casual/JO records don't suddenly vanish
     * from every dashboard the moment this migration runs; the flag only
     * really starts mattering from the next renewal cycle onward, once
     * Module 1A's commit service is the only thing that can flip it.
     */
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->boolean('is_renewed')->default(true)->after('employment_status');
            $table->string('renewal_period')->nullable()->after('is_renewed'); // e.g. 2026_1st_semester
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn(['is_renewed', 'renewal_period']);
        });
    }
};
