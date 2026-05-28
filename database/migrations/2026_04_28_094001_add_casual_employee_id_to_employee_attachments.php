<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_attachments', function (Blueprint $table) {
            $table->foreignId('casual_employee_id')
                  ->nullable()
                  ->after('job_order_id')
                  ->constrained('casual_employees')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_attachments', function (Blueprint $table) {
            $table->dropForeign(['casual_employee_id']);
            $table->dropColumn('casual_employee_id');
        });
    }
};
