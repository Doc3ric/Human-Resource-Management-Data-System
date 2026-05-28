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
        Schema::table('casual_employees', function (Blueprint $table) {
            $table->string('legislative_district')->nullable()->after('name_extension');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('casual_employees', function (Blueprint $table) {
            $table->dropColumn('legislative_district');
        });
    }
};
