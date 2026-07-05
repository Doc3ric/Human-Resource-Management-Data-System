<?php

namespace App\Support\Training;

use App\Models\TrainingCertificate;
use App\Models\TrainingParticipant;
use App\Models\User;
use App\Support\Idcc\IdccPipeline;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;

/**
 * Module 10B.3/10B.4/10B.6 — generates attendance/resource-speaker
 * certificates and auto-links them to the participant's employee record via
 * the IDCC pipeline (same single-source-of-truth principle as everything
 * else routed through IdccPipeline).
 */
class TrainingCertificateService
{
    /** Default: anyone not marked absent qualifies. Programs needing a stricter
     *  hours threshold can tighten this later — kept simple and testable now. */
    public function isEligible(TrainingParticipant $participant): bool
    {
        return $participant->attendance_status !== 'absent';
    }

    public function issue(TrainingParticipant $participant, User $issuedBy): TrainingCertificate
    {
        if (!$this->isEligible($participant)) {
            throw new \RuntimeException("{$participant->displayName()} does not meet the attendance threshold for a certificate (status: {$participant->attendance_status}).");
        }

        $training = $participant->training;
        $certificateType = $participant->is_resource_speaker ? 'resource_speaker' : 'attendance';
        $referenceNo = 'CERT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $pdf = Pdf::loadView('exports.training-certificate-pdf', [
            'certificateType' => $certificateType,
            'participantName' => $this->toProperCase($participant->displayName()),
            'trainingTitle' => $training->title,
            'trainingType' => $training->type,
            'dateRange' => $training->date_from->format('M d') . '–' . $training->date_to->format('M d, Y'),
            'venue' => $training->venue,
            'institution' => $training->institution,
            'hours' => $participant->hours_attended ?? $training->total_hours,
            'topic' => $participant->topic,
            'referenceNo' => $referenceNo,
            'signatories' => [['name' => 'HR Management Officer', 'position' => 'PHRMO']],
        ]);

        $tempPath = sys_get_temp_dir() . '/' . $referenceNo . '.pdf';
        file_put_contents($tempPath, $pdf->output());

        $uploadedFile = new UploadedFile($tempPath, $referenceNo . '.pdf', 'application/pdf', null, true);

        $document = app(IdccPipeline::class)->ingest($uploadedFile, [
            'attachment_field' => $certificateType === 'resource_speaker' ? 'training_resource_speaker_cert' : 'training_cert',
            'personnel_id' => $participant->personnel_id,
            'personnel_type' => $participant->personnel_id ? 'plantilla_record' : null,
            'ingested_by' => $issuedBy->id,
        ]);

        @unlink($tempPath);

        return TrainingCertificate::create([
            'training_participant_id' => $participant->id,
            'certificate_type' => $certificateType,
            'reference_no' => $referenceNo,
            'document_id' => $document->id,
            'issued_by' => $issuedBy->id,
        ]);
    }

    /**
     * @return array{issued: TrainingCertificate[], skipped: array}
     */
    public function issueBatch(iterable $participants, User $issuedBy): array
    {
        $issued = [];
        $skipped = [];

        foreach ($participants as $participant) {
            if (!$this->isEligible($participant)) {
                $skipped[] = ['participant' => $participant->displayName(), 'reason' => "attendance status: {$participant->attendance_status}"];
                continue;
            }
            $issued[] = $this->issue($participant, $issuedBy);
        }

        return ['issued' => $issued, 'skipped' => $skipped];
    }

    private function toProperCase(string $name): string
    {
        return mb_convert_case(mb_strtolower($name), MB_CASE_TITLE);
    }
}
