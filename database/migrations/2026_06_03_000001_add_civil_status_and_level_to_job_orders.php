<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            // Civil status (STATUS column): SINGLE, MARRIED, WIDOW, etc.
            $table->string('civil_status')->nullable()->after('birthdate');
            
            // Level (M1, F1, M2, F2 etc.) — replaces first_level / second_level checkboxes
            $table->string('level', 20)->nullable()->after('gender');
            
            // Nature of work detail (2nd NATURE OF WORK column: CLERICAL SERVICES, JANITORIAL SERVICES, etc.)
            $table->string('nature_of_work_detail')->nullable()->after('nature_of_work');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['civil_status', 'level', 'nature_of_work_detail']);
        });
    }
};
