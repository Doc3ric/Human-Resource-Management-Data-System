<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft deletes (deleted_at) to all core tables
     * so that "deleting" any record simply archives it instead.
     */
    public function up(): void
    {
        $tables = [
            'plantilla_records',
            'employees',
            'appointments',
            'positions',
            'organizational_units',
            'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Remove the soft deletes column.
     */
    public function down(): void
    {
        $tables = [
            'plantilla_records',
            'employees',
            'appointments',
            'positions',
            'organizational_units',
            'users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
