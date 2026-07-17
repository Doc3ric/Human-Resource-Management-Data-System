<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recorded-entries Delete action needs to be reversible/auditable rather
     * than a hard row removal — matches the soft-delete convention already
     * used on plantilla_records.
     */
    public function up(): void
    {
        Schema::table('leave_violations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('leave_violations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
