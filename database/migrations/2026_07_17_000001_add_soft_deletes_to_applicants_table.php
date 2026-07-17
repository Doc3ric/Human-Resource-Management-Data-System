<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * De-duplicating accidental double-submissions (same email + same item_no,
     * submitted seconds apart) needs to be reversible/auditable rather than a
     * hard row removal — matches the soft-delete convention already used on
     * plantilla_records and leave_violations.
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
