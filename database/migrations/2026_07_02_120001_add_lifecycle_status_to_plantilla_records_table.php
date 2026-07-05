<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 1B.1 — lifecycle status, distinct from (and broader than) the
     * is_renewed gate. Defaults to ACTIVE for all existing rows — this is
     * additive tracking, not a re-classification of the current workforce;
     * `nature_of_separation`/`retired_at` (already on this table) remain the
     * legacy fields other code reads, so nothing existing breaks.
     */
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->string('lifecycle_status', 30)->default('ACTIVE')->after('is_renewed');
            $table->date('lifecycle_effective_date')->nullable()->after('lifecycle_status');
            $table->string('lifecycle_basis')->nullable()->after('lifecycle_effective_date'); // legal/administrative basis
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn(['lifecycle_status', 'lifecycle_effective_date', 'lifecycle_basis']);
        });
    }
};
