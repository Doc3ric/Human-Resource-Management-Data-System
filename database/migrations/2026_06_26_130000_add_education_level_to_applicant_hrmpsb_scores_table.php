<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_hrmpsb_scores', function (Blueprint $table) {
            $table->string('education_level')->nullable()->default('first_level')->after('education_score');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_hrmpsb_scores', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};
