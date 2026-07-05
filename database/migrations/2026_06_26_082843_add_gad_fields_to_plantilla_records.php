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
            // SPMS performance rating (0.00 – 5.00 scale, per CSC MC No. 6 s. 2012)
            $table->decimal('spms_rating', 3, 2)->nullable()->after('solo_parent');
            // Short office/unit code for GAD grouping (e.g. "PHRMO", "OPROV")
            $table->string('office_code', 30)->nullable()->after('office_department');
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn(['spms_rating', 'office_code']);
        });
    }
};
