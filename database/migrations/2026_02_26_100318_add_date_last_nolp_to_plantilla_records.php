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
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->date('date_last_nolp')->nullable()->after('date_last_promotion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn('date_last_nolp');
        });
    }
};
