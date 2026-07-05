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
        Schema::table('ipcr_ratings', function (Blueprint $table) {
            $table->date('target_submission_date')->nullable()->after('target_submitted');
            $table->date('rating_submission_date')->nullable()->after('rating');
            $table->decimal('final_rating', 8, 2)->nullable()->after('rating_submission_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipcr_ratings', function (Blueprint $table) {
            $table->dropColumn(['target_submission_date', 'rating_submission_date', 'final_rating']);
        });
    }
};
