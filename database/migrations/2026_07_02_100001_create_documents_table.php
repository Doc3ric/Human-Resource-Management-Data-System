<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 9 — IDCC core document metadata table (tbl_doc_metadata).
     * Every captured payload, regardless of source, lands here (Stage 1)
     * before flowing through OCR/classification/privacy triage.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Stage 1 — Universal Ingestion
            $table->string('sha256_hash', 64)->unique();
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('storage_path');
            $table->string('capture_source', 20)->default('UPLOAD'); // UPLOAD|WEBCAM|SMARTPHONE|BLUETOOTH|SCANNER
            $table->string('attachment_field')->nullable(); // e.g. employee_201, applicant_letter, training_certificate

            // Stage 2 — OCR
            $table->string('extraction_tier', 10)->nullable(); // TIER_1|TIER_2|TIER_3
            $table->longText('ocr_text')->nullable();
            $table->decimal('ocr_confidence', 5, 2)->nullable();
            $table->string('ocr_status', 20)->default('pending'); // pending|completed|failed|unavailable

            // Stage 3 — Classification
            $table->string('doc_type_code')->nullable(); // APPT-33A, PDS-212, LEAVE-CSC6, DISC-RACCS, LGU-EO, COE-REQ...
            $table->string('importance_class', 20)->nullable(); // Vital|Important|Useful|Routine
            $table->unsignedTinyInteger('privacy_tier')->nullable(); // 1 Restricted / 2 Protected / 3 Internal
            $table->text('abstract_description')->nullable();

            // Stage 4/5 — Privacy triage & RACCS wall
            $table->boolean('is_spi')->default(false);
            $table->boolean('is_raccs')->default(false);
            $table->boolean('encrypted')->default(false);

            // Stage 6 — relationship mapping anchor
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('personnel_type')->nullable();

            // Pipeline status
            $table->string('status', 20)->default('intake'); // intake|processing|manual_review|classified|filed|disposed
            $table->string('review_reason')->nullable();

            $table->foreignId('ingested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // standard users cannot hard-delete (Stage 7)

            $table->index(['doc_type_code', 'privacy_tier', 'importance_class']);
            $table->index(['personnel_id', 'personnel_type']);
        });

        // FULLTEXT index for Stage 2 search (RACCS docs are excluded from the
        // *global* search scope at the query layer in Stage 5, not by
        // omitting them from this index — see DocumentSearchService).
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE documents ADD FULLTEXT fulltext_ocr_text (ocr_text)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
