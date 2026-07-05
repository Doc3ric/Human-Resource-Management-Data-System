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
        // Module 2.2/2.3 — the three-stage recruitment-record lifecycle
        // (Active 0-1yr / Archived 1-2yr / Valueless Holding Queue 2yr+
        // after the vacancy was filled). Nullable/additive only; is_filled
        // defaults false so existing rows read as "not yet filled" rather
        // than silently becoming disposal-eligible.
        Schema::table('applicants', function (Blueprint $table) {
            $table->boolean('is_filled')->default(false)->after('item_no');
            $table->date('date_filled')->nullable()->after('is_filled');
            $table->string('lifecycle_stage', 20)->default('ACTIVE')->after('date_filled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['is_filled', 'date_filled', 'lifecycle_stage']);
        });
    }
};
