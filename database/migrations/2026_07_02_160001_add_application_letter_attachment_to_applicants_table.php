<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 4.2 — Appointment Encoder cannot save without an application
     * letter, unless overridden with a mandatory reason. The attachment
     * itself is a Module 9A capture — referenced via documents.id, not a
     * raw path, so it goes through the same IDCC intake as every other
     * attachment in the system (9A.6).
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->foreignId('application_letter_document_id')->nullable()->after('jaf_url')->constrained('documents')->nullOnDelete();
            $table->boolean('application_letter_override')->default(false)->after('application_letter_document_id');
            $table->text('application_letter_override_reason')->nullable()->after('application_letter_override');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('application_letter_document_id');
            $table->dropColumn(['application_letter_override', 'application_letter_override_reason']);
        });
    }
};
