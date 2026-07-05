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
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->enum('type', ['manual', 'scheduled', 'restore'])->default('manual');
            $table->bigInteger('size_bytes')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'restored'])->default('pending');
            $table->string('storage_path')->nullable();      // server-local path or Drive file ID
            $table->enum('storage_driver', ['local', 'google_drive'])->default('local');
            $table->boolean('encrypted')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            // created_at only — no updated_at; app-level DB user has INSERT only, no UPDATE/DELETE
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
