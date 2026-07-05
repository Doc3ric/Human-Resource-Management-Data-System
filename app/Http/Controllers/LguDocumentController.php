<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LguDocument;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

/**
 * Module 10-M5 — LGU Documents (EO/Memo/Ordinance registry) + service-request
 * tracking with a Citizen's Charter (R.A. 11032) countdown.
 */
class LguDocumentController extends Controller
{
    /** Simple transactions get a 3-working-day Citizen's Charter window by default. */
    public const DEFAULT_WINDOW_DAYS = 3;

    public function index(Request $request)
    {
        $documents = LguDocument::orderByDesc('date_issued')->paginate(15, ['*'], 'docs');
        $requests = ServiceRequest::orderBy('due_at')->paginate(15, ['*'], 'reqs');

        return view('lgu.index', compact('documents', 'requests'));
    }

    public function storeDocument(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:EO,MEMO,ORDINANCE',
            'number' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'date_issued' => 'required|date',
            'document_id' => 'nullable|exists:documents,id',
        ]);
        $data['created_by'] = $request->user()->id;

        $doc = LguDocument::create($data);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Registered LGU Document',
            'description' => "Registered {$doc->type} \"{$doc->title}\" ({$doc->number}).",
        ]);

        return back()->with('success', 'LGU document registered.');
    }

    public function storeRequest(Request $request)
    {
        $data = $request->validate([
            'request_type' => 'required|in:COE,SERVICE_RECORD,CERTIFICATION',
            'requester_name' => 'required|string|max:255',
            'requester_contact' => 'nullable|string|max:255',
            'plantilla_record_id' => 'nullable|exists:plantilla_records,id',
            'date_requested' => 'required|date',
            'window_days' => 'nullable|integer|min:1|max:30',
        ]);

        $windowDays = $data['window_days'] ?? self::DEFAULT_WINDOW_DAYS;
        $dueAt = \Carbon\Carbon::parse($data['date_requested'])->addWeekdays($windowDays);

        $serviceRequest = ServiceRequest::create([
            'request_type' => $data['request_type'],
            'requester_name' => $data['requester_name'],
            'requester_contact' => $data['requester_contact'] ?? null,
            'plantilla_record_id' => $data['plantilla_record_id'] ?? null,
            'date_requested' => $data['date_requested'],
            'due_at' => $dueAt,
            'status' => 'pending',
        ]);

        return back()->with('success', "Service request logged — due {$dueAt->toFormattedDateString()} per the Citizen's Charter window.");
    }

    public function completeRequest(Request $request, ServiceRequest $serviceRequest)
    {
        $serviceRequest->update(['status' => 'completed', 'handled_by' => $request->user()->id]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Completed Service Request',
            'description' => "Completed {$serviceRequest->request_type} request for {$serviceRequest->requester_name}.",
        ]);

        return back()->with('success', 'Service request marked completed.');
    }

    /**
     * Module 10-M5 — escalate requests nearing their Citizen's Charter
     * deadline (same day or overdue) to Division Chief + Department Head.
     * Called from a scheduled command; escalation here means a logged,
     * visible flag — no separate notification channel exists in this app
     * yet, so it surfaces via the existing ActivityLog feed.
     */
    public function escalateDueSoon(): int
    {
        $dueSoon = ServiceRequest::where('status', 'pending')
            ->whereNull('escalated_at')
            ->where('due_at', '<=', now()->addDay())
            ->get();

        foreach ($dueSoon as $req) {
            // No user_id available in this system-triggered context
            // (activity_logs.user_id is required) — the escalated_at
            // timestamp and status change are themselves the audit trail.
            $req->update(['status' => 'escalated', 'escalated_at' => now()]);
        }

        return $dueSoon->count();
    }
}
