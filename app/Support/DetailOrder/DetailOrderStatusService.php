<?php

namespace App\Support\DetailOrder;

use App\Models\DetailOrder;
use App\Models\Document;
use App\Support\Idcc\IdccPipeline;
use App\Support\Notifications\HrmoNotifier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;

/**
 * Enhancement Spec Sec. 3 — daily 1-year detail flagging + auto-drafted
 * recall letter, same "auto-detect -> auto-draft PDF -> file for human
 * review" shape as LeaveViolationLetterService.
 */
class DetailOrderStatusService
{
    public function __construct(private readonly HrmoNotifier $notifier)
    {
    }

    public function checkStatus(DetailOrder $order): void
    {
        // Recalled/Extended are terminal for the daily sweep — a human already acted on this order.
        if (in_array($order->status, ['Recalled', 'Extended'], true)) {
            return;
        }

        $daysRemaining = $order->days_remaining;

        if ($daysRemaining <= 30 && $daysRemaining > 0) {
            if ($order->status !== 'Nearing Expiry') {
                $order->update(['status' => 'Nearing Expiry']);
                $this->notifyNearingExpiry($order);
            }
            return;
        }

        if ($daysRemaining <= 0) {
            if ($order->recall_letter_id === null) {
                $order->update([
                    'status' => 'Overdue',
                    'recall_letter_id' => $this->generateRecallLetter($order)->id,
                ]);
                $this->notifier->notifyDivisionHead(
                    'Recall Letter Ready for Review',
                    "Recall letter auto-drafted for detail order {$order->detail_order_no} ({$order->plantillaRecord->full_name})." .
                        ' Awaiting review/signature in Document Filing.'
                );
            } elseif ($order->status !== 'Overdue') {
                $order->update(['status' => 'Overdue']);
            }
        }
    }

    /**
     * Sec. 3's 30-day warning is meant for the employee, the detailed unit
     * head, and the HRMO Division Head — but this system has no email/contact
     * field anywhere for an employee or a detailed-unit-head, so those two
     * can't actually be emailed. The Division Head is notified for real; the
     * other two intended recipients are recorded on the order itself so the
     * requirement stays auditable rather than silently dropped.
     */
    private function notifyNearingExpiry(DetailOrder $order): void
    {
        $message = "Detail order {$order->detail_order_no} ({$order->plantillaRecord->full_name}) reaches its 1-year limit in {$order->days_remaining} day(s).";

        $notified = $this->notifier->notifyDivisionHead('Detail Order Nearing 1-Year Limit', $message);

        $note = '[SYSTEM ' . now()->format('Y-m-d') . '] 30-day warning triggered. Notified: ' . (empty($notified) ? 'none (no HRMO Division Head configured)' : implode(', ', $notified))
            . '. Employee and detailed unit head were NOT emailed — no contact info on file for either.';

        $order->update(['remarks' => trim(($order->remarks ? $order->remarks . "\n\n" : '') . $note)]);
    }

    /** Draft-only — never auto-sent. Filed as "pending review" via the same IDCC path as other system-generated notices. */
    public function generateRecallLetter(DetailOrder $order): Document
    {
        $record = $order->plantillaRecord;
        $employeeName = trim($record->first_name . ' ' . $record->last_name);

        $pdf = Pdf::loadView('exports.detail-recall-letter-pdf', [
            'employeeName' => $employeeName,
            'detailOrderNo' => $order->detail_order_no,
            'dateOfOrder' => $order->date_issued->format('F d, Y'),
            'oneYearMark' => $order->one_year_mark->format('F d, Y'),
            'homeUnit' => $order->home_unit ?? $record->office_department,
            'detailedUnitOffice' => $order->detailed_unit,
            'dateIssued' => now()->format('F d, Y'),
            'signatoryName' => 'HRMO Division Head',
            'signatoryPosition' => 'PHRMO, Provincial Government of Bukidnon',
        ]);

        $referenceNo = 'DO-RECALL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $tempPath = sys_get_temp_dir() . '/' . $referenceNo . '.pdf';
        file_put_contents($tempPath, $pdf->output());
        $uploadedFile = new UploadedFile($tempPath, $referenceNo . '.pdf', 'application/pdf', null, true);

        $document = app(IdccPipeline::class)->ingest($uploadedFile, [
            'attachment_field' => 'detail_order_recall_letter',
            'personnel_id' => $record->id,
            'personnel_type' => 'plantilla_record',
            // No ingested_by — this document is system-generated, not uploaded by a user;
            // it sits pending HRMO Division Head review/signature before it is ever sent.
        ]);
        @unlink($tempPath);

        // Spec Sec. 3 Approval Rule — the letter must be visibly tagged as a
        // pending-review draft in Document Filing, never mistaken for an
        // already-issued notice. review_reason is already rendered on the
        // IDCC document detail view for exactly this kind of flag.
        $document->update(['review_reason' => 'DRAFT — Pending HRMO Division Head Review. Not yet sent.']);

        return $document;
    }
}
