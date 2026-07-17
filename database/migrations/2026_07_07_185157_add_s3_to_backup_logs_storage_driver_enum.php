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
        DB::statement("ALTER TABLE backup_logs MODIFY COLUMN storage_driver ENUM('local', 'google_drive', 's3') DEFAULT 'local'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE backup_logs MODIFY COLUMN storage_driver ENUM('local', 'google_drive') DEFAULT 'local'");
    }
};
