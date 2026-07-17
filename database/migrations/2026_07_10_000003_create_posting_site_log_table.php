<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Proof of posting in the 3 conspicuous places, required before a
     * publication_request can advance to PUBLICATION_ACTIVE. Proof photos
     * are bulletin-board/public-posting evidence, not SPI, so they use the
     * plain Storage::disk('public') + path-column convention (as used for
     * applicant photos) rather than the encrypted IDCC document pipeline
     * reserved for SPI/personnel records.
     */
    public function up(): void
    {
        Schema::create('posting_site_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_request_id')->constrained('publication_requests')->restrictOnDelete();

            $table->string('site_label'); // e.g. "PHRMO Bulletin Board", "Provincial Capitol Lobby", "Agency Website"
            $table->date('posted_date');
            $table->date('removed_date')->nullable();
            $table->string('proof_photo_path')->nullable();
            $table->foreignId('posted_by_id')->constrained('users')->restrictOnDelete();

            $table->timestamps();

            $table->index('publication_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posting_site_log');
    }
};
