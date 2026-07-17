<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\PlantillaRecord;
use App\Support\Leave\LeaveApplicationService;
use App\Support\Leave\LeaveGuardrail;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(private readonly LeaveApplicationService $applications)
    {
    }

    public function index(Request $request)
    {
        $query = LeaveApplication::with(['plantillaRecord', 'leaveType'])->orderByDesc('filed_at');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        $applications = $query->paginate(20)->withQueryString();
        $leaveTypes = LeaveType::orderBy('code')->get();
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        $total = LeaveApplication::count();
        $pending = LeaveApplication::where('status', 'pending')->count();
        $approved = LeaveApplication::where('status', 'approved')->count();

        $metrics = [
            'total' => $total,
            'pending' => $pending,
            'pending_pct' => $total > 0 ? round(($pending / $total) * 100, 1) : 0,
            'approved' => $approved,
            'approved_pct' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
        ];

        return view('leave.index', compact('applications', 'leaveTypes', 'metrics', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'days_requested' => 'required|numeric|min:0.5',
            'filed_at' => 'required|date',
            'reason' => 'nullable|string|max:1000',
            'document_id' => 'nullable|exists:documents,id',
        ]);

        $record = PlantillaRecord::findOrFail($data['plantilla_record_id']);
        $type = LeaveType::findOrFail($data['leave_type_id']);

        try {
            $application = $this->applications->file(
                $record, $type, $data['date_from'], $data['date_to'],
                (float) $data['days_requested'], $data['filed_at'], $request->user(),
                $data['reason'] ?? null, $data['document_id'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['leave' => $e->getMessage()])->withInput();
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Filed Leave Application',
            'description' => "Filed {$type->code} leave for {$record->last_name}, {$record->first_name} ({$data['date_from']} to {$data['date_to']}).",
        ]);

        return redirect()->route('leave.index')->with('success', "Leave application #{$application->id} filed — pending approval.");
    }

    public function approve(Request $request, LeaveApplication $leaveApplication)
    {
        $this->applications->approve($leaveApplication, $request->user());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Approved Leave',
            'description' => "Approved leave application #{$leaveApplication->id}.",
        ]);

        return back()->with('success', 'Leave application approved.');
    }

    public function disapprove(Request $request, LeaveApplication $leaveApplication)
    {
        $data = $request->validate(['disapproval_reason' => 'required|string|max:500']);
        $this->applications->disapprove($leaveApplication, $request->user(), $data['disapproval_reason']);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Disapproved Leave',
            'description' => "Disapproved leave application #{$leaveApplication->id}: {$data['disapproval_reason']}",
        ]);

        return back()->with('success', 'Leave application disapproved.');
    }

    /** Balances for one personnel record, used by the leave-filing form. */
    public function balances(PlantillaRecord $plantilla)
    {
        if (LeaveGuardrail::isExcluded($plantilla)) {
            return response()->json(['excluded' => true, 'message' => LeaveGuardrail::BLOCK_MESSAGE], 422);
        }

        $year = (int) date('Y');
        $balances = LeaveBalance::where('plantilla_record_id', $plantilla->id)
            ->where('year', $year)
            ->with('leaveType')
            ->get()
            ->map(fn ($b) => [
                'code' => $b->leaveType->code,
                'earned' => (float) $b->earned_days,
                'used' => (float) $b->used_days,
                'remaining' => $b->remaining_days,
            ]);

        return response()->json(['excluded' => false, 'balances' => $balances]);
    }
}
