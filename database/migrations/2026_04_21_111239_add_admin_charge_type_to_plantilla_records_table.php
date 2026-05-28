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
            $table->string('admin_charge_type')->nullable()->after('is_admin_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn('admin_charge_type');
        });
    }
};
