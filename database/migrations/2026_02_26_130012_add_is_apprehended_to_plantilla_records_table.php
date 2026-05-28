<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->boolean('is_apprehended')->default(false)->after('is_vacant')
                ->comment('True if employee is under administrative charge / apprehended; excludes them from NOSI/NOLP');
        });
    }

    public function down(): void
    {
        Schema::table('plantilla_records', function (Blueprint $table) {
            $table->dropColumn('is_apprehended');
        });
    }
};
