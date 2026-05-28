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
        Schema::table('employee_attachments', function (Blueprint $table) {
            // Make plantilla_record_id nullable so JO attachments don't need it
            $table->unsignedBigInteger('plantilla_record_id')->nullable()->change();

            // Add the job_order_id FK for job order attachments
            $table->foreignId('job_order_id')
                  ->nullable()
                  ->after('plantilla_record_id')
                  ->constrained('job_orders')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attachments', function (Blueprint $table) {
            $table->dropForeign(['job_order_id']);
            $table->dropColumn('job_order_id');
            $table->unsignedBigInteger('plantilla_record_id')->nullable(false)->change();
        });
    }
};
