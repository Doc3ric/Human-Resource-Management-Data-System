<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {

            // ── Submission meta ───────────────────────────────────────────
            $table->dateTime('applied_at')->nullable()->after('ain');     // Google Form timestamp
            $table->text('photo_url')->nullable()->after('applied_at');   // PHOTO (Google Drive link)
            $table->text('jaf_url')->nullable()->after('photo_url');      // JAF - Job Application Form link

            // ── Personal info additions ───────────────────────────────────
            $table->string('religion')->nullable()->after('email_address');
            $table->string('indigenous_people')->nullable()->after('religion'); // IP field

            // ── Current PGB employment (if PGB employee) ──────────────────
            $table->string('pgb_status')->nullable()->after('is_pgb_employee');          // STATUS (Permanent/Casual/JO)
            $table->string('length_of_service')->nullable()->after('pgb_status');         // LOS
            $table->string('current_position')->nullable()->after('length_of_service');   // POSITION (Current)
            $table->string('years_in_present_position')->nullable()->after('current_position');
            $table->string('years_permanent')->nullable()->after('years_in_present_position');
            $table->string('years_coterminous')->nullable()->after('years_permanent');
            $table->string('years_casual')->nullable()->after('years_coterminous');
            $table->string('years_job_order')->nullable()->after('years_casual');

            // ── Non-PGB work experience ───────────────────────────────────
            $table->boolean('has_non_pgb_employment')->default(false)->after('years_job_order');
            $table->string('np_employment_status')->nullable()->after('has_non_pgb_employment'); // STATUS-NP
            $table->string('np_employer')->nullable()->after('np_employment_status');
            $table->string('np_designation')->nullable()->after('np_employer');
            $table->string('np_period')->nullable()->after('np_designation');

            // ── Education additions ───────────────────────────────────────
            $table->text('tor_url')->nullable()->after('degree');  // TOR - Transcript of Records link

            // ── Eligibility ───────────────────────────────────────────────
            // existing: eligibility (text name) — repurposed: TYPE column
            $table->text('eligibility_url')->nullable()->after('eligibility'); // ELIGIBILITY (document link)

            // ── Training ─────────────────────────────────────────────────
            $table->text('training_url')->nullable()->after('eligibility_url');
            $table->string('training_hours')->nullable()->after('training_url');

            // ── Work experience ───────────────────────────────────────────
            $table->text('experience_url')->nullable()->after('training_hours');
            $table->text('responsibilities')->nullable()->after('experience_url');

            // ── Performance ───────────────────────────────────────────────
            $table->string('performance_rating')->nullable()->after('responsibilities');
            $table->text('performance_form_url')->nullable()->after('performance_rating');

            // ── Awards ────────────────────────────────────────────────────
            $table->text('award')->nullable()->after('performance_form_url');
            $table->text('award_certificate_url')->nullable()->after('award');

            // ── Competencies & acknowledgement ────────────────────────────
            $table->text('competencies')->nullable()->after('award_certificate_url');
            $table->boolean('acknowledged')->default(false)->after('competencies');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn([
                'applied_at', 'photo_url', 'jaf_url',
                'religion', 'indigenous_people',
                'pgb_status', 'length_of_service', 'current_position',
                'years_in_present_position', 'years_permanent', 'years_coterminous',
                'years_casual', 'years_job_order',
                'has_non_pgb_employment', 'np_employment_status', 'np_employer',
                'np_designation', 'np_period',
                'tor_url', 'eligibility_url',
                'training_url', 'training_hours',
                'experience_url', 'responsibilities',
                'performance_rating', 'performance_form_url',
                'award', 'award_certificate_url',
                'competencies', 'acknowledged',
            ]);
        });
    }
};
