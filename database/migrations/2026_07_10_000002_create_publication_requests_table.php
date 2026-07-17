<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each CS Form No. 9 request is versioned; editing a field after
     * SUBMITTED_TO_CSC_FO creates a new row (supersedes_request_id) rather
     * than overwriting, per Sec. 26. Signature capture (Dept Head signs
     * CS Form 9) deliberately does NOT get bespoke signed_by_id/signed_date
     * columns — this app already has a generic polymorphic ESignature model
     * (e_signatures.signable_type/signable_id) built for exactly this kind
     * of "some entity gets signed" need; PublicationRequest uses that
     * instead, so submission is gated on signatures()->exists().
     */
    public function up(): void
    {
        Schema::create('publication_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacant_position_id')->constrained('vacant_positions')->restrictOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->foreignId('supersedes_request_id')->nullable()->constrained('publication_requests')->nullOnDelete();

            $table->string('agency_name')->default('Provincial Government of Bukidnon');
            $table->string('agency_contact_person'); // mandatory per Sec 26 2025 amendment
            $table->string('agency_contact_number');
            $table->string('agency_contact_email');

            $table->foreignId('prepared_by_id')->constrained('users')->restrictOnDelete();

            $table->enum('status', [
                'DRAFT', 'PENDING_SIGNATURE', 'SUBMITTED_TO_CSC_FO',
                'POSTED', 'PUBLICATION_ACTIVE', 'PUBLICATION_COMPLIANT',
                'PUBLICATION_DEFICIENT', 'VALID', 'NEAR_EXPIRY',
                'EXPIRED', 'FILLED', 'CANCELLED',
            ])->default('DRAFT');

            $table->enum('submission_mode', ['CSC_FO', 'Agency_Website', 'Newspaper', 'Job_Site', 'Multiple'])->default('CSC_FO');
            $table->date('submitted_to_csc_fo_date')->nullable();
            $table->string('csc_fo_receiving_copy_path')->nullable(); // scanned proof of receipt

            $table->date('posting_start_date')->nullable(); // reckoning date per Sec 26
            // NOT hardcoded fact — spec itself flags this figure as unverified against
            // the final 2025 ORAOHRA text; ships as a configurable default only.
            $table->unsignedInteger('posting_min_required_days')->default(15);
            $table->date('posting_actual_end_date')->nullable();

            $table->date('validity_start_date')->nullable();
            // Same caveat as posting_min_required_days — unverified base figure, configurable.
            $table->unsignedInteger('validity_months')->default(9);
            $table->string('validity_extended_reason')->nullable(); // Sec 30 calamity extension
            $table->date('validity_end_date')->nullable(); // computed: validity_start + validity_months (+ extension)

            $table->string('edit_reason')->nullable(); // required if version_no > 1
            // Sec 26's express carve-out: spelling/parenthetical-only corrections
            // don't force a republication cycle, but are still fully audit-logged
            // (via Spatie LogsActivity on the model) with this flag recorded.
            $table->boolean('is_section26_exempt_edit')->default(false);

            $table->timestamps();

            $table->index('status');
            $table->index('validity_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_requests');
    }
};
