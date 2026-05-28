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
            $table->integer('lwop')->nullable()->after('religion');
            $table->boolean('is_admin_charge')->default(false)->after('is_apprehended');
            $table->date('apprehended_from')->nullable()->after('is_admin_charge');
            $table->date('admin_charge_from')->nullable()->after('apprehended_from');
            $table->date('admin_charge_to')->nullable()->after('admin_charge_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn([
                'lwop',
                'is_admin_charge',
                'apprehended_from',
                'admin_charge_from',
                'admin_charge_to'
            ]);
        });
    }
};
