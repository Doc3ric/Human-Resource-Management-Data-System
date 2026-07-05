<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 9 Stage 5 — RACCS confidentiality wall security log.
     * Logically air-gapped (separate table from spi_access_log, never joined
     * into general reporting); records every attempt, granted or denied.
     * NOTE: true physical air-gapping is an infrastructure decision outside
     * this codebase's scope — this table is the logical equivalent.
     */
    public function up(): void
    {
        Schema::create('raccs_access_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // view_attempt|unmask_attempt|download_attempt
            $table->string('outcome'); // granted|denied
            $table->string('ip_address', 45)->nullable();
            $table->string('denial_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raccs_access_log');
    }
};
