<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_schedules', function (Blueprint $table) {
            $table->string('lbc_number')->nullable()->after('law_name'); // e.g. "LBC 165"
        });
    }

    public function down(): void
    {
        Schema::table('salary_schedules', function (Blueprint $table) {
            $table->dropColumn('lbc_number');
        });
    }
};
