<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 10-M4 — e-signature per CS Form No. 11 s.2025, with a PNPKI
     * (DICT) hook that does NOT block ingestion if missing/unavailable —
     * pnpki_status defaults to 'not_submitted' and nothing in this app
     * requires it to move past that state (no PNPKI integration exists;
     * this is the hook point for one to be added later).
     */
    public function up(): void
    {
        Schema::create('e_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('signable_type');
            $table->unsignedBigInteger('signable_id');
            $table->string('signatory_name');
            $table->string('signatory_position')->nullable();
            $table->longText('signature_image'); // base64 PNG from the signature pad
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('pnpki_status', 20)->default('not_submitted'); // not_submitted|pending|verified|failed
            $table->string('pnpki_reference')->nullable();
            $table->timestamps();

            $table->index(['signable_type', 'signable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_signatures');
    }
};
