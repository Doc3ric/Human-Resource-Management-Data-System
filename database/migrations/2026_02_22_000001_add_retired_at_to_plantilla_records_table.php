<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            // Date this record was automatically retired
            $table->date('retired_at')->nullable()->after('is_vacant');
            // Index date_of_birth for fast age queries
            $table->index('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropIndex(['date_of_birth']);
            $table->dropColumn('retired_at');
        });
    }
};
