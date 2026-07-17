<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User-resizable table columns, applied globally alongside the existing
 * column-visibility feature — same user_table_preferences row (user_id,
 * tab_key), sibling JSON column keyed by column index/key -> pixel width.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_table_preferences', function (Blueprint $table) {
            $table->json('column_widths')->nullable()->after('hidden_columns');
        });
    }

    public function down(): void
    {
        Schema::table('user_table_preferences', function (Blueprint $table) {
            $table->dropColumn('column_widths');
        });
    }
};
