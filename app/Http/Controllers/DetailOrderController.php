<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DetailOrder;
use App\Models\PlantillaRecord;
use Illuminate\Http\Request;

/** Enhancement Spec Sec. 3 — Detailed Unit 1-year tracking & auto-drafted recall letter. */
class DetailOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DetailOrder::with('plantillaRecord')->orderByDesc('date_effective_start');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $orders = $query->paginate(20)->withQueryString();

        $metrics = [
            'total' => DetailOrder::count(),
            'nearing_expiry' => DetailOrder::where('status', 'Nearing Expiry')->count(),
            'overdue' => DetailOrder::where('status', 'Overdue')->count(),
            'active' => DetailOrder::where('status', 'Active')->count(),
        ];

        return view('detail-orders.index', compact('orders', 'metrics'));
    }

    public function create()
    {
        $employees = PlantillaRecord::filled()->select('id', 'first_name', 'last_name', 'office_department', 'position_title')
            ->orderBy('last_name')
            ->get();

        return view('detail-orders.create', compact('employees'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('add Detail Orders'), 403);

        $validated = $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'detailed_unit' => 'required|string|max:255',
            'home_unit' => 'nullable|string|max:255',
            'detail_order_no' => 'required|string|max:255',
            'date_issued' => 'required|date',
            'date_effective_start' => 'required|date',
            'date_effective_end' => 'nullable|date|after:date_effective_start',
            'remarks' => 'nullable|string',
        ]);

        $record = PlantillaRecord::findOrFail($validated['plantilla_record_id']);

        $order = DetailOrder::create([
            'plantilla_record_id' => $validated['plantilla_record_id'],
            'home_unit' => $validated['home_unit'] ?: $record->office_department,
            'detailed_unit' => $validated['detailed_unit'],
            'detail_order_no' => $validated['detail_order_no'],
            'date_issued' => $validated['date_issued'],
            'date_effective_start' => $validated['date_effective_start'],
            // Open-ended orders default to the 1-year CSC limit, per spec Sec. 3.
            'date_effective_end' => $validated['date_effective_end']
                ?? \Illuminate\Support\Carbon::parse($validated['date_effective_start'])->addDays(365),
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'Active',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Added Detail Order',
            'description' => "Recorded detail order {$order->detail_order_no} for {$record->first_name} {$record->last_name} to {$order->detailed_unit}.",
        ]);

        return redirect()->route('detail-orders.index')->with('success', 'Detail order recorded successfully.');
    }

    /** Manual close-out once the employee has actually reported back — the daily job only drafts the letter, never sends or resolves it. */
    public function markRecalled(Request $request, DetailOrder $detailOrder)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Detail Orders'), 403);

        $detailOrder->update(['status' => 'Recalled']);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Recalled Detail Order',
            'description' => "Marked detail order {$detailOrder->detail_order_no} as Recalled.",
        ]);

        return back()->with('success', 'Detail order marked as recalled.');
    }

    /** Records a properly-authorized extension beyond the 1-year limit, so the daily sweep stops re-flagging this order. */
    public function markExtended(Request $request, DetailOrder $detailOrder)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->can('edit Detail Orders'), 403);

        $validated = $request->validate([
            'date_effective_end' => 'required|date|after:today',
            'remarks' => 'nullable|string',
        ]);

        $detailOrder->update([
            'status' => 'Extended',
            'date_effective_end' => $validated['date_effective_end'],
            'remarks' => $validated['remarks'] ?? $detailOrder->remarks,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Extended Detail Order',
            'description' => "Extended detail order {$detailOrder->detail_order_no} to {$validated['date_effective_end']}.",
        ]);

        return back()->with('success', 'Detail order extension recorded.');
    }

    public function downloadRecallLetter(DetailOrder $detailOrder)
    {
        abort_unless($detailOrder->recall_letter_id, 404);

        return redirect()->route('idcc.preview', $detailOrder->recall_letter_id);
    }
}
