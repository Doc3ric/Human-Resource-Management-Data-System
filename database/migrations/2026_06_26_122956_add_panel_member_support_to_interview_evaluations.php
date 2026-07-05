<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK and unique constraints that reference rater_id
        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->dropForeign(['rater_id']);
        });

        // Make rater_id nullable and add panel_member columns
        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('rater_id')->nullable()->change();
            $table->unsignedBigInteger('panel_member_id')->nullable()->after('rater_id');
            $table->string('rater_name')->nullable()->after('panel_member_id');

            $table->foreign('rater_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('panel_member_id')->references('id')->on('panel_members')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->dropForeign(['panel_member_id']);
            $table->dropForeign(['rater_id']);
            $table->dropColumn(['panel_member_id', 'rater_name']);
        });

        Schema::table('interview_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('rater_id')->nullable(false)->change();
            $table->foreign('rater_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
