<?php

namespace App\Support\Leave;

use App\Models\LeaveViolation;
use App\Models\User;
use App\Support\Idcc\IdccPipeline;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;

/**
 * Module 2B.4/2B.5 — generates the correct notice letter per violation type,
 * then records the issued notice into the employee's record via IDCC
 * (Module 2B.5's "auto-recording"), same pattern as training certificates.
 */
class LeaveViolationLetterService
{
    public function issue(LeaveViolation $violation, User $issuedBy): void
    {
        $record = $violation->plantillaRecord;
        $letterTitle = $this->getLetterTitle($violation->violation_type);

        // AWOL / Habitual Absenteeism / LWOP violations are auto-detected
        // (LeaveViolationDetectionService) and never populate month/year/
        // occurrences — those keys only exist on the manually-entered
        // Tardiness/Undertime letters. getBodyText() doesn't use these two
        // for that group of types, so a safe fallback is enough.
        $monthYear = ($violation->details['month'] ?? date('F')) . ' ' . ($violation->details['year'] ?? date('Y'));
        $occurrences = $violation->details['occurrences'] ?? '0';

        $bodyText = $this->getBodyText($violation->violation_type, $monthYear, $occurrences, $violation->offense_tier);
        $legalBasis = $this->getLegalBasis($violation->violation_type);

        // Reuse the reference number already printed on this letter (if it was issued/edited
        // before) instead of minting a new one each time — a later reprimand cites this exact
        // number as the prior warning's reference, so it must stay stable across regenerations.
        $referenceNo = $violation->details['reference_no'] ?? ('LV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)));

        $prefix = $violation->details['prefix'] ?? '';
        $signatoryName = $violation->details['signatory_name'] ?? 'HR Management Officer';
        $signatoryPosition = $violation->details['signatory_position'] ?? 'PHRMO';

        $pdf = Pdf::loadView('exports.leave-violation-notice-pdf', [
            'letterTitle' => $letterTitle,
            'employeeName' => $record->first_name . ' ' . $record->last_name,
            'lastName' => $record->last_name,
            'employeePosition' => $record->position_title,
            'employeeOffice' => $record->office_department,
            'prefix' => $prefix,
            'bodyText' => $bodyText,
            'facts' => $this->flattenDetails($violation->details),
            'legalBasis' => $legalBasis,
            'duProcessNote' => 'This notice affords you the opportunity to explain before any penalty is recommended, consistent with civil-service due process.',
            'signatoryName' => $signatoryName,
            'signatoryPosition' => $signatoryPosition,
            'referenceNo' => $referenceNo,
            'ccRecipients' => null,
        ]);

        $tempPath = sys_get_temp_dir() . '/' . $referenceNo . '.pdf';
        file_put_contents($tempPath, $pdf->output());
        $uploadedFile = new UploadedFile($tempPath, $referenceNo . '.pdf', 'application/pdf', null, true);

        $document = app(IdccPipeline::class)->ingest($uploadedFile, [
            'attachment_field' => 'leave_violation_notice',
            'personnel_id' => $record->id,
            'personnel_type' => 'plantilla_record',
            'ingested_by' => $issuedBy->id,
        ]);
        @unlink($tempPath);

        $violation->update([
            'status' => 'notice_pending',
            'document_id' => $document->id,
            'issued_by' => $issuedBy->id,
            'issued_at' => now(),
            'details' => array_merge($violation->details, ['reference_no' => $referenceNo]),
        ]);
    }

    /**
     * Issue a formal reprimand letter (2nd offense) that references the prior warning.
     */
    public function issueReprimand(LeaveViolation $violation, User $issuedBy, $priorWarnings = null): void
    {
        $record = $violation->plantillaRecord;
        $baseType = str_replace('REPRIMAND_', '', $violation->violation_type);
        $violationLabel = $baseType === 'HABITUAL_TARDINESS' ? 'Habitual Tardiness' : 'Habitual Undertime';

        $letterTitle = ''; // Removed title for the endorsement format

        $monthYear = $violation->details['month'] . ' ' . $violation->details['year'];
        $occurrences = $violation->details['occurrences'];
        $prefix = $violation->details['prefix'] ?? '';
        $signatoryName = $violation->details['signatory_name'] ?? 'AIDA B. LOVERES';
        $signatoryPosition = $violation->details['signatory_position'] ?? 'P.G. Department Head/PHRM Officer';

        $pronounObj = ($record->gender === 'Female' ? 'her' : 'him');
        $pronoun = ($record->gender === 'Female' ? 'her' : 'his');
        $pronounSubj = ($record->gender === 'Female' ? 'she' : 'he');
        $pronounSubjTitle = ucfirst($pronounSubj);

        $priorWarningText = '';
        $hasOverrides = $this->hasPriorWarningOverrides($violation);
        
        // If priorWarnings is a single model, make it a collection/array
        if ($priorWarnings instanceof LeaveViolation) {
            $priorWarnings = [$priorWarnings];
        }

        if (!empty($priorWarnings) || $hasOverrides) {
            $warningsListHtml = '';
            
            if ($hasOverrides) {
                // If there are manual overrides, we just output the single override text
                $priorDate = $violation->details['prior_warning_date_override'] ?? null;
                $firstWarning = !empty($priorWarnings) ? collect($priorWarnings)->first() : null;
                
                $priorDate = $priorDate
                    ? \Illuminate\Support\Carbon::parse($priorDate)->format('F d, Y')
                    : ($firstWarning
                        ? ($firstWarning->issued_at ? $firstWarning->issued_at->format('F d, Y') : $firstWarning->created_at->format('F d, Y'))
                        : '');
                $priorMonth = $violation->details['prior_warning_month_override'] ?? $firstWarning?->details['month'] ?? '';
                $priorYear = $violation->details['prior_warning_year_override'] ?? $firstWarning?->details['year'] ?? '';
                $priorRefNo = $violation->details['prior_warning_ref_override']
                    ?? ($firstWarning ? $this->priorReferenceNo($firstWarning) : '');

                $priorWarningText = "<p>Records of this Office show that on <strong>{$priorDate}</strong>, a formal warning (Reference No. <strong>{$priorRefNo}</strong>) was issued to {$pronounObj} regarding {$violationLabel} for the month of {$priorMonth} {$priorYear}. Despite said warning, records further show that {$pronounSubj} has continued to incur the same violation.</p>";
            } else {
                // We have one or multiple prior warnings/reprimands from the DB
                if (count($priorWarnings) === 1) {
                    $w = collect($priorWarnings)->first();
                    $priorDate = $w->issued_at ? $w->issued_at->format('F d, Y') : $w->created_at->format('F d, Y');
                    $priorMonth = $w->details['month'] ?? '';
                    $priorYear = $w->details['year'] ?? '';
                    $priorRefNo = $this->priorReferenceNo($w);
                    $priorActionLabel = $this->priorActionLabel($w);
                    $priorWarningText = "<p>Records of this Office show that on <strong>{$priorDate}</strong>, a formal {$priorActionLabel} (Reference No. <strong>{$priorRefNo}</strong>) was issued to {$pronounObj} regarding {$violationLabel} for the month of {$priorMonth} {$priorYear}. Despite said {$priorActionLabel}, records further show that {$pronounSubj} has continued to incur the same violation.</p>";
                } else {
                    $warningsListHtml .= "<ul>";
                    foreach ($priorWarnings as $w) {
                        $priorDate = $w->issued_at ? $w->issued_at->format('F d, Y') : $w->created_at->format('F d, Y');
                        $priorMonth = $w->details['month'] ?? '';
                        $priorYear = $w->details['year'] ?? '';
                        $priorRefNo = $this->priorReferenceNo($w);
                        $priorActionTag = ucfirst($this->priorActionLabel($w));
                        $warningsListHtml .= "<li><strong>{$priorActionTag}</strong> — {$priorDate} (Reference No. <strong>{$priorRefNo}</strong>) for the month of {$priorMonth} {$priorYear}</li>";
                    }
                    $warningsListHtml .= "</ul>";

                    $priorWarningText = "<p>Records of this Office show that formal actions were issued to {$pronounObj} regarding {$violationLabel} on the following dates:</p>
                    {$warningsListHtml}
                    <p>Despite said actions, records further show that {$pronounSubj} has continued to incur the same violation.</p>";
                }
            }
        }

        // Build the body text for reprimand
        $overridePosition = $violation->details['position_title'] ?? $record->position_title;
        $overrideOffice = $violation->details['office_department'] ?? $record->office_department;
        
        if ($baseType === 'HABITUAL_TARDINESS') {
            $bodyText = "
<p>This is to inform the committee regarding the habitual tardiness of <strong>" . ($prefix ? "$prefix " : "") . "{$record->first_name} {$record->last_name}</strong>, <strong>{$overridePosition}</strong> of the {$overrideOffice}.</p>
{$priorWarningText}
<p>As evidenced by {$pronoun} Daily Time Record for the month of {$monthYear}, {$pronounSubj} has again incurred <strong>{$occurrences}</strong> times of tardiness.</p>
<p>{$pronounSubjTitle} is hereby reminded that pursuant to Rule XVII, Section 8 on Government Office Hours of the Omnibus Rules Implementing Book V of Executive Order No. 292, as amended by CSC Memorandum Circular No. 34, s. 1998:</p>
<div class=\"quote\">\"Officers and employees who have incurred tardiness and undertime, regardless of the number of minutes per day, ten (10) times a month for at least two (2) consecutive months during the year or for at least two (2) months in a semester, shall be subject to disciplinary action.\"</div>
<p>Under Section 63(C)(10) of the 2025 Revised Rules on Administrative Cases in the Civil Service (RACCS), promulgated under CSC Resolution No. 2500357, Habitual Tardiness is classified as a <strong>Light Offense</strong> with the following penalties:</p>
<div class=\"penalties\">
    <table>
        <tr><td>a.</td><td>1st Offense &mdash;</td><td>Reprimand</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Suspension of one (1) to thirty (30) days</td></tr>
        <tr><td>c.</td><td>3rd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
</div>
<p>{$pronounSubjTitle} is sternly warned that a repetition of the same or similar offense shall warrant the imposition of a more severe penalty, including suspension or dismissal from the service.</p>
<p>This letter shall form part of {$pronoun} 201 file.</p>
";
        } else {
            $bodyText = "
<p>This is to inform the committee regarding the habitual undertime of <strong>" . ($prefix ? "$prefix " : "") . "{$record->first_name} {$record->last_name}</strong>, <strong>{$overridePosition}</strong> of the {$overrideOffice}.</p>
{$priorWarningText}
<p>As evidenced by {$pronoun} Daily Time Record for the month of {$monthYear}, {$pronounSubj} has again incurred <strong>{$occurrences}</strong> instances of undertime.</p>
<p>{$pronounSubjTitle} is hereby reminded that under the Civil Service Commission (CSC) Memorandum Circular No. 16, s. 2010 on the Policy on Undertime:</p>
<div class=\"quote\">\"Any officer or employee who incurs undertime, regardless of the number of minutes/hours, ten (10) times a month for at least two (2) months in a semester or at least two (2) consecutive months during the year, shall be liable for Simple Misconduct and/or Conduct Prejudicial to the Best Interest of the Service, as the case may be.\"</div>
<p>Under the 2025 Revised Rules on Administrative Cases in the Civil Service (RACCS), promulgated under CSC Resolution No. 2500357, the corresponding penalties are:</p>
<div class=\"penalties\">
    <p style=\"margin-bottom: 4px;\"><strong>For Simple Misconduct (Less Grave Offense):</strong></p>
    <table>
        <tr><td style=\"width: 20px;\">a.</td><td style=\"width: 100px;\">1st Offense &mdash;</td><td>Suspension of one (1) month and one (1) day to six (6) months</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Suspension of six (6) months and one (1) day to one (1) year</td></tr>
        <tr><td>c.</td><td>3rd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
    <br>
    <p style=\"margin-bottom: 4px;\"><strong>For Conduct Prejudicial to the Best Interest of the Service (Grave Offense):</strong></p>
    <table>
        <tr><td style=\"width: 20px;\">a.</td><td style=\"width: 100px;\">1st Offense &mdash;</td><td>Suspension of six (6) months and one (1) day to one (1) year</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
</div>
<p>In view of the foregoing, {$pronounSubj} is hereby <strong>formally reprimanded</strong> for Habitual Undertime. {$pronounSubjTitle} is sternly warned that a repetition of the same or similar offense shall warrant the imposition of a more severe penalty.</p>
<p>This letter shall form part of {$pronoun} 201 file.</p>
";
        }

        $referenceNo = $violation->details['reference_no'] ?? ('LV-REP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)));

        $pdf = Pdf::loadView('exports.leave-violation-notice-pdf', [
            'letterTitle' => $letterTitle,
            'employeeName' => $record->first_name . ' ' . $record->last_name,
            'lastName' => $record->last_name,
            'employeePosition' => $overridePosition,
            'employeeOffice' => $overrideOffice,
            'prefix' => $prefix,
            'bodyText' => $bodyText,
            'facts' => $this->flattenDetails($violation->details),
            'legalBasis' => $this->getLegalBasis($baseType),
            'duProcessNote' => '',
            'signatoryName' => $signatoryName,
            'signatoryPosition' => $signatoryPosition,
            'referenceNo' => $referenceNo,
            'ccRecipients' => null,
            'customAddresseeName' => 'MS. CHERRY P. PEPITO',
            'customAddresseePosition' => 'Provincial Administrator<br>Chairman, Provincial Discipline Committee',
            'customAddresseeOffice' => 'This Province',
            'customSalutation' => "Ma'am:"
        ]);

        $tempPath = sys_get_temp_dir() . '/' . $referenceNo . '.pdf';
        file_put_contents($tempPath, $pdf->output());
        $uploadedFile = new UploadedFile($tempPath, $referenceNo . '.pdf', 'application/pdf', null, true);

        $document = app(IdccPipeline::class)->ingest($uploadedFile, [
            'attachment_field' => 'leave_violation_notice',
            'personnel_id' => $record->id,
            'personnel_type' => 'plantilla_record',
            'ingested_by' => $issuedBy->id,
        ]);
        @unlink($tempPath);

        $violation->update([
            'status' => 'notice_pending',
            'document_id' => $document->id,
            'issued_by' => $issuedBy->id,
            'issued_at' => now(),
            'details' => array_merge($violation->details, ['reference_no' => $referenceNo]),
        ]);
    }

    /**
     * The reference number to cite when a later letter (e.g. a reprimand) references a prior
     * warning. Prefers the number actually persisted on/printed on that prior letter; falls
     * back to the old ad-hoc reconstruction only for records issued before reference_no was
     * persisted, so historical letters still get a (stable, if not verifiably authentic) number.
     */
    private function priorReferenceNo(LeaveViolation $w): string
    {
        return $w->details['reference_no']
            ?? ('LV-' . $w->created_at->format('Ymd') . '-' . strtoupper(substr(md5($w->id), -6)));
    }

    /** "warning" or "reprimand" — lets a citation say what was actually issued, not just "warning" for everything. */
    private function priorActionLabel(LeaveViolation $w): string
    {
        return str_starts_with($w->violation_type, 'REPRIMAND_') ? 'reprimand' : 'warning';
    }

    public function getLetterTitle(string $violationType): string
    {
        return match ($violationType) {
            'AWOL' => 'RETURN-TO-WORK ORDER',
            'HABITUAL_ABSENTEEISM' => 'NOTICE OF HABITUAL ABSENTEEISM',
            'HABITUAL_TARDINESS' => 'NOTICE OF HABITUAL TARDINESS',
            'UNDERTIME' => 'NOTICE OF HABITUAL UNDERTIME',
            'LWOP' => 'NOTICE OF LEAVE WITHOUT PAY',
            'LWOP_TARDINESS' => 'NOTICE OF LEAVE WITHOUT PAY AND TARDINESS',
            'REPRIMAND_HABITUAL_TARDINESS', 'REPRIMAND_UNDERTIME' => 'FORMAL LETTER OF REPRIMAND',
            'LATE_FILING_SICK_LEAVE', 'LATE_FILING_VACATION_LEAVE', 'LATE_FILING_OTHER_LEAVE' => 'NOTICE OF LATE/IMPROPER FILING OF LEAVE',
            'MISSING_SUPPORTING_DOCS' => 'NOTICE TO SUBMIT LACKING REQUIREMENT',
            'LEAVE_DISAPPROVED' => 'NOTICE OF DISAPPROVAL OF LEAVE APPLICATION',
            'SHOW_CAUSE' => 'SHOW-CAUSE ORDER / NOTICE TO EXPLAIN',
            default => 'NOTICE OF LEAVE INFRACTION'
        };
    }

    public function getBodyText(string $violationType, string $monthYear, string $occurrences, int $offenseTier = 1): string
    {
        return match ($violationType) {
            'HABITUAL_TARDINESS' => "
<p>As evidenced by your Daily Time Record for the month of {$monthYear}, you have incurred <strong>{$occurrences}</strong> times of tardiness.</p>
<p>Please be reminded that pursuant to Rule XVII, Section 8 on Government Office Hours of the Omnibus Rules Implementing Book V of Executive Order No. 292, as amended by CSC Memorandum Circular No. 34, s. 1998:</p>
<div class=\"quote\">\"Officers and employees who have incurred tardiness and undertime, regardless of the number of minutes per day, ten (10) times a month for at least two (2) consecutive months during the year or for at least two (2) months in a semester, shall be subject to disciplinary action.\"</div>
<p>Be advised further that under Section 63(C)(10) of the 2025 Revised Rules on Administrative Cases in the Civil Service (RACCS), promulgated under CSC Resolution No. 2500357, Habitual Tardiness is classified as a Light Offense with the following corresponding penalties:</p>
<div class=\"penalties\">
    <table>
        <tr><td>a.</td><td>1st Offense &mdash;</td><td>Reprimand</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Suspension of one (1) to thirty (30) days</td></tr>
        <tr><td>c.</td><td>3rd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
</div>
<p>Please consider this letter as a <strong>formal warning</strong>. You are expected to improve your attendance and punctuality to avoid the imposition of disciplinary action.</p>
<p>Thank you for your immediate attention to this matter.</p>
",
            'UNDERTIME' => "
<p>As evidenced by your Daily Time Record for the month of {$monthYear}, you have incurred <strong>{$occurrences}</strong> instances of undertime.</p>
<p>Please be reminded that under the Civil Service Commission (CSC) Memorandum Circular No. 16, s. 2010 on the Policy on Undertime, it is explicitly provided that:</p>
<div class=\"quote\">\"Any officer or employee who incurs undertime, regardless of the number of minutes/hours, ten (10) times a month for at least two (2) months in a semester or at least two (2) consecutive months during the year, shall be liable for Simple Misconduct and/or Conduct Prejudicial to the Best Interest of the Service, as the case may be.\"</div>
<p>Be advised further that under the 2025 Revised Rules on Administrative Cases in the Civil Service (RACCS), promulgated under CSC Resolution No. 2500357, the corresponding penalties for the above-stated offenses are as follows:</p>
<div class=\"penalties\">
    <p style=\"margin-bottom: 4px;\"><strong>For Simple Misconduct (Less Grave Offense):</strong></p>
    <table>
        <tr><td style=\"width: 20px;\">a.</td><td style=\"width: 100px;\">1st Offense &mdash;</td><td>Suspension of one (1) month and one (1) day to six (6) months</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Suspension of six (6) months and one (1) day to one (1) year</td></tr>
        <tr><td>c.</td><td>3rd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
    <br>
    <p style=\"margin-bottom: 4px;\"><strong>For Conduct Prejudicial to the Best Interest of the Service (Grave Offense):</strong></p>
    <table>
        <tr><td style=\"width: 20px;\">a.</td><td style=\"width: 100px;\">1st Offense &mdash;</td><td>Suspension of six (6) months and one (1) day to one (1) year</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
</div>
<p>Please consider this letter as a <strong>formal warning</strong>. You are expected to improve your attendance immediately to avoid the initiation of administrative disciplinary proceedings against you.</p>
<p>Thank you for your immediate attention to this matter.</p>
",
            'AWOL' => '<p>You are directed to report back to work immediately. Records show you have been continuously absent without approved leave. Failure to return to work and to explain your absence within the period stated in this notice shall be a valid ground to drop you from the rolls without prior notice, per the Omnibus Rules on Leave.</p>',
            'HABITUAL_ABSENTEEISM' => "<p>Records show a pattern of unauthorized absences meeting the threshold for Habitual Absenteeism under the 2025 RACCS (Offense Tier {$offenseTier}). You are directed to submit a written explanation within five (5) working days from receipt of this notice.</p>",
            'LWOP', 'LWOP_TARDINESS' => "<p>Records show that you have exhausted your leave credits and incurred Leave Without Pay (LWOP) and/or tardiness without pay. Please be reminded that under Sec. 62 of the Omnibus Rules on Leave, an employee who is on LWOP for a continuous period of one (1) year may be dropped from the rolls. You are advised to settle any pending leave applications and coordinate with the office regarding your attendance and equivalent salary deductions.</p>",
            'LATE_FILING_SICK_LEAVE' => '<p>Records show that your sick leave application, as detailed in the accompanying facts, was not filed immediately upon your return from leave as required under Sec. 53 of the Omnibus Rules on Leave. Please explain in writing why this application was filed beyond the prescribed period. Failure to file within the prescribed period may cause the disapproval of the application, which would render the corresponding absence unauthorized and without pay.</p>',
            'LATE_FILING_VACATION_LEAVE' => '<p>Records show that your vacation leave application, as detailed in the accompanying facts, was not filed at least five (5) days in advance of its effective date as required under Sec. 51 of the Omnibus Rules on Leave, except where the absence was genuinely unforeseeable. Please explain in writing the circumstances of this filing. Failure to file within the prescribed period may cause the disapproval of the application, which would render the corresponding absence unauthorized and without pay.</p>',
            'LATE_FILING_OTHER_LEAVE' => '<p>Records show that the leave application detailed in the accompanying facts was filed outside the prescribed prior-filing window for its leave type under the Omnibus Rules on Leave. Please explain in writing the circumstances of this filing. Failure to file within the prescribed period may cause the disapproval of the application, which would render the corresponding absence unauthorized and without pay.</p>',
            'MISSING_SUPPORTING_DOCS' => '<p>Records show that the sick leave application detailed in the accompanying facts, being in excess of five (5) successive days, lacks the required medical certificate. If medical consultation was not availed, an affidavit must be submitted in lieu thereof. You are directed to submit the lacking requirement within the period stated in this notice; failure to do so may result in the disapproval of the application.</p>',
            'LEAVE_DISAPPROVED' => '<p>This is to formally notify you that the leave application detailed in the accompanying facts has been DISAPPROVED for the reason stated therein. Under current civil-service rules, a disapproved leave application renders the corresponding absence unauthorized and without pay, and shall be counted toward the tracking of habitual absenteeism where applicable. You may submit a written explanation or remedy within five (5) working days from receipt of this notice.</p>',
            'SHOW_CAUSE' => "<p>You are hereby directed to SHOW CAUSE, in writing, within five (5) working days from receipt of this order, why no administrative disciplinary action should be taken against you in connection with the matter detailed in the accompanying facts (Offense Tier {$offenseTier}). This order affords you the opportunity to explain before any penalty is recommended, consistent with due process under the 2025 RACCS. Failure to submit a written explanation within the period stated shall be construed as a waiver of your right to be heard, and the matter shall be resolved on the basis of the evidence on record.</p>",
            default => '<p>You are directed to submit a written explanation regarding your leave infractions.</p>'
        };
    }

    public function getLegalBasis(string $violationType): string
    {
        return match ($violationType) {
            'AWOL' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998), Sec. 63 — AWOL / Return-to-Work Order.',
            'HABITUAL_ABSENTEEISM' => '2025 RACCS (CSC Resolution No. 2500357), Section 63(C)(10) — Habitual Absenteeism (grave offense).',
            'HABITUAL_TARDINESS', 'REPRIMAND_HABITUAL_TARDINESS' => '2025 RACCS (CSC Resolution No. 2500357), Section 63(C)(10) — Habitual Tardiness (light offense); EO 292, Rule XVII, Sec. 8, as amended by CSC MC No. 34, s. 1998.',
            'UNDERTIME', 'REPRIMAND_UNDERTIME' => 'CSC MC No. 16, s. 2010 — Policy on Undertime; 2025 RACCS (CSC Resolution No. 2500357).',
            'LWOP', 'LWOP_TARDINESS' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998), Sec. 62 — Leave Without Pay.',
            'LATE_FILING_SICK_LEAVE' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998), Sec. 53 — sick leave must be filed immediately upon the employee\'s return from leave.',
            'LATE_FILING_VACATION_LEAVE' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998), Sec. 51 — vacation leave must be filed at least five (5) days in advance of the effective date, whenever possible.',
            'LATE_FILING_OTHER_LEAVE' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998), Secs. 54/56 — leave applications must be filed within the prescribed prior-filing window for the specific leave type; CSC MC No. 05 s.2021 (CS Form No. 6, revised).',
            'MISSING_SUPPORTING_DOCS' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998), Sec. 53 — sick leave in excess of five (5) successive days requires a medical certificate, or an affidavit in lieu where medical consultation was not availed.',
            'LEAVE_DISAPPROVED' => 'Omnibus Rules on Leave (CSC MC No. 41 s.1998); current CSC rule that failure to file within the prescribed period may cause disapproval of the application, rendering the absence unauthorized and without pay.',
            'SHOW_CAUSE' => '2025 RACCS (CSC Resolution No. 2500357) — due-process requirement of notice and opportunity to be heard before any administrative penalty is imposed.',
            default => 'Omnibus Rules on Leave / 2025 RACCS (CSC Resolution No. 2500357)'
        };
    }

    /** True if any reference-warning override was set on this reprimand, even without a linked prior-warning record. */
    private function hasPriorWarningOverrides(LeaveViolation $violation): bool
    {
        foreach (['prior_warning_date_override', 'prior_warning_ref_override', 'prior_warning_month_override', 'prior_warning_year_override'] as $key) {
            if (!empty($violation->details[$key] ?? null)) {
                return true;
            }
        }
        return false;
    }

    private function flattenDetails(array $details): array
    {
        $flat = [];
        foreach ($details as $key => $value) {
            $flat[str_replace('_', ' ', ucfirst($key))] = is_array($value) ? json_encode($value) : $value;
        }
        return $flat;
    }

    public function issuePayrollCoordination(LeaveViolation $violation, User $issuedBy, string $actionType): void
    {
        $record = $violation->plantillaRecord;

        $letterTitle = $actionType === 'DROP' ? 'NOTICE TO DROP FROM PAYROLL' : 'NOTICE FOR SALARY DEDUCTION';
        $bodyText = $actionType === 'DROP'
            ? "This is to formally request the dropping of {$record->first_name} {$record->last_name} from the payroll effective immediately due to {$violation->violation_type}."
            : "This is to formally request a salary deduction for {$record->first_name} {$record->last_name} due to {$violation->violation_type} (Leave Without Pay / Undertime). Please refer to the attached records for the computed deduction days.";

        $referenceNo = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $pdf = Pdf::loadView('exports.payroll-coordination-pdf', [
            'letterTitle' => $letterTitle,
            'employeeName' => "{$record->last_name}, {$record->first_name}",
            'employeeOffice' => $record->office_department,
            'bodyText' => $bodyText,
            'facts' => $this->flattenDetails($violation->details),
            'actionType' => $actionType,
            'signatoryName' => 'HR Management Officer',
            'signatoryPosition' => 'PHRMO',
            'referenceNo' => $referenceNo,
            'dateIssued' => now()->format('F d, Y'),
        ]);

        $tempPath = sys_get_temp_dir() . '/' . $referenceNo . '.pdf';
        file_put_contents($tempPath, $pdf->output());
        $uploadedFile = new UploadedFile($tempPath, $referenceNo . '.pdf', 'application/pdf', null, true);

        // Record the coordination letter in IDCC
        $document = app(IdccPipeline::class)->ingest($uploadedFile, [
            'attachment_field' => 'payroll_coordination',
            'personnel_id' => $record->id,
            'personnel_type' => 'plantilla_record',
            'ingested_by' => $issuedBy->id,
        ]);
        @unlink($tempPath);

        // Mark payroll as coordinated and keep the document linked, same as issue()/issueReprimand(),
        // so the letter can be found again from the Generated Letters list and re-downloaded.
        $violation->update([
            'status' => 'escalated',
            'document_id' => $document->id,
            'issued_by' => $issuedBy->id,
            'issued_at' => now(),
            'details' => array_merge($violation->details ?? [], ['payroll_action_type' => $actionType]),
        ]);
    }
}
