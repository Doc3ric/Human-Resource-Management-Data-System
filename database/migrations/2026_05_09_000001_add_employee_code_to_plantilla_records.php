<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->string('employee_code', 20)->nullable()->unique()->after('umid');
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropUnique(['employee_code']);
            $table->dropColumn('employee_code');
        });
    }
};
