<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            // Nature of employee separation: Retired, Resigned, Dropped from Rolls, etc.
            $table->string('nature_of_separation', 100)->nullable()->after('retired_at')
                  ->comment('e.g. Retired, Resigned, Dropped from Rolls');

            // Date the employee was separated / terminated
            $table->date('date_separated')->nullable()->after('nature_of_separation')
                  ->comment('Effective date of separation');

            // Nature of last appointment change: Newly Hired, Promoted, Demoted, Transferred
            $table->string('nature_of_appointment', 100)->nullable()->after('date_separated')
                  ->comment('e.g. Newly Hired, Promoted, Demoted, Transferred');

            $table->index('nature_of_separation');
            $table->index('date_separated');
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropIndex(['nature_of_separation']);
            $table->dropIndex(['date_separated']);
            $table->dropColumn(['nature_of_separation', 'date_separated', 'nature_of_appointment']);
        });
    }
};
