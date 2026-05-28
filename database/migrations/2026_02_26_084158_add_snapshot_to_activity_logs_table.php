<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Stores a JSON snapshot of the employee record before vacating,
            // enabling the "Undo" feature to restore the employee's data.
            $table->json('snapshot')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('snapshot');
        });
    }
};
