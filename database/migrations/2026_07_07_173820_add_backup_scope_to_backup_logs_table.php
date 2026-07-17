<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('backup_logs', function (Blueprint $table) {
            // Distinguishes a full database dump from a file-storage (IDCC
            // documents/reports) archive, so each can be retained/pruned
            // independently — file archives are far larger and change less often.
            $table->enum('backup_scope', ['database', 'files'])->default('database')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backup_logs', function (Blueprint $table) {
            $table->dropColumn('backup_scope');
        });
    }
};
