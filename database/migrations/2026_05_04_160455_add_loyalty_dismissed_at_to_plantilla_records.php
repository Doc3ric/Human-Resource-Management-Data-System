<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->timestamp('loyalty_dismissed_at')->nullable()->after('date_last_nolp');
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn('loyalty_dismissed_at');
        });
    }
};
