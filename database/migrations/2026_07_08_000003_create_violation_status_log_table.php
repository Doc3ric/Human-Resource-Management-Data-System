<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhancement Spec Sec. 4 — append-only, same shape as
     * raccs_access_log/spi_access_log (no updated_at; rows are never edited).
     */
    public function up(): void
    {
        Schema::create('violation_status_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('violation_id')->constrained('violations', 'violation_id')->cascadeOnDelete();
            $table->string('status', 30); // Recorded | Under Investigation | Escalated | Resolved | Dismissed
            $table->timestamp('date_logged')->useCurrent();
            $table->text('remarks')->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_status_log');
    }
};
