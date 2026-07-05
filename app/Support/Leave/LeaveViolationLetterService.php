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

        $referenceNo = 'LV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
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
        ]);
    }

    /**
     * Issue a formal reprimand letter (2nd offense) that references the prior warning.
     */
    public function issueReprimand(LeaveViolation $violation, User $issuedBy, ?LeaveViolation $priorWarning = null): void
    {
        $record = $violation->plantillaRecord;
        $baseType = str_replace('REPRIMAND_', '', $violation->violation_type);
        $violationLabel = $baseType === 'HABITUAL_TARDINESS' ? 'Habitual Tardiness' : 'Habitual Undertime';

        $letterTitle = 'FORMAL LETTER OF REPRIMAND';

        $monthYear = $violation->details['month'] . ' ' . $violation->details['year'];
        $occurrences = $violation->details['occurrences'];
        $prefix = $violation->details['prefix'] ?? '';
        $signatoryName = $violation->details['signatory_name'] ?? 'HR Management Officer';
        $signatoryPosition = $violation->details['signatory_position'] ?? 'PHRMO';

        // Build the prior warning reference
        $priorWarningText = '';
        if ($priorWarning) {
            $priorDate = $priorWarning->issued_at
                ? $priorWarning->issued_at->format('F d, Y')
                : $priorWarning->created_at->format('F d, Y');
            $priorMonth = $priorWarning->details['month'] ?? '';
            $priorYear = $priorWarning->details['year'] ?? '';
            $priorRefNo = 'LV-' . $priorWarning->created_at->format('Ymd') . '-' . strtoupper(substr(md5($priorWarning->id), -6));

            $priorWarningText = "<p>Records of this Office show that on <strong>{$priorDate}</strong>, a formal warning (Reference No. <strong>{$priorRefNo}</strong>) was issued to you regarding {$violationLabel} for the month of {$priorMonth} {$priorYear}. Despite said warning, records further show that you have continued to incur the same violation.</p>";
        }

        // Build the body text for reprimand
        if ($baseType === 'HABITUAL_TARDINESS') {
            $bodyText = "
{$priorWarningText}
<p>As evidenced by your Daily Time Record for the month of {$monthYear}, you have again incurred <strong>{$occurrences}</strong> times of tardiness.</p>
<p>You are hereby reminded that pursuant to Rule XVII, Section 8 on Government Office Hours of the Omnibus Rules Implementing Book V of Executive Order No. 292, as amended by CSC Memorandum Circular No. 34, s. 1998:</p>
<div class=\"quote\">\"Officers and employees who have incurred tardiness and undertime, regardless of the number of minutes per day, ten (10) times a month for at least two (2) consecutive months during the year or for at least two (2) months in a semester, shall be subject to disciplinary action.\"</div>
<p>Under Section 63(C)(10) of the 2025 Revised Rules on Administrative Cases in the Civil Service (RACCS), promulgated under CSC Resolution No. 2500357, Habitual Tardiness is classified as a <strong>Light Offense</strong> with the following penalties:</p>
<div class=\"penalties\">
    <table>
        <tr><td>a.</td><td>1st Offense &mdash;</td><td>Reprimand</td></tr>
        <tr><td>b.</td><td>2nd Offense &mdash;</td><td>Suspension of one (1) to thirty (30) days</td></tr>
        <tr><td>c.</td><td>3rd Offense &mdash;</td><td>Dismissal from the service</td></tr>
    </table>
</div>
<p>In view of the foregoing, you are hereby <strong>formally reprimanded</strong> for Habitual Tardiness as the prescribed penalty for the 1st Offense under the above-cited rules.</p>
<p>You are sternly warned that a repetition of the same or similar offense shall warrant the imposition of a more severe penalty, including suspension or dismissal from the service.</p>
<p>This letter shall form part of your 201 file.</p>
";
        } else {
            $bodyText = "
{$priorWarningText}
<p>As evidenced by your Daily Time Record for the month of {$monthYear}, you have again incurred <strong>{$occurrences}</strong> instances of undertime.</p>
<p>You are hereby reminded that under the Civil Service Commission (CSC) Memorandum Circular No. 16, s. 2010 on the Policy on Undertime:</p>
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
<p>In view of the foregoing, you are hereby <strong>formally reprimanded</strong> for Habitual Undertime. You are sternly warned that a repetition of the same or similar offense shall warrant the imposition of a more severe penalty.</p>
<p>This letter shall form part of your 201 file.</p>
";
        }

        $referenceNo = 'LV-REP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $ccRecipients = 'Provincial Discipline Committee<br>Provincial Government of Bukidnon';

        $pdf = Pdf::loadView('exports.leave-violation-notice-pdf', [
            'letterTitle' => $letterTitle,
            'employeeName' => $record->first_name . ' ' . $record->last_name,
            'lastName' => $record->last_name,
            'employeePosition' => $record->position_title,
            'employeeOffice' => $record->office_department,
            'prefix' => $prefix,
            'bodyText' => $bodyText,
            'facts' => $this->flattenDetails($violation->details),
            'legalBasis' => $this->getLegalBasis($baseType),
            'duProcessNote' => '',
            'signatoryName' => $signatoryName,
            'signatoryPosition' => $signatoryPosition,
            'referenceNo' => $referenceNo,
            'ccRecipients' => $ccRecipients,
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
        ]);
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
            default => 'Omnibus Rules on Leave / 2025 RACCS (CSC Resolution No. 2500357)'
        };
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
        app(IdccPipeline::class)->ingest($uploadedFile, [
            'attachment_field' => 'payroll_coordination',
            'personnel_id' => $record->id,
            'personnel_type' => 'plantilla_record',
            'ingested_by' => $issuedBy->id,
        ]);
        @unlink($tempPath);

        // Also update the violation to note that payroll was coordinated
        $violation->update(['status' => 'escalated']);
    }
}
