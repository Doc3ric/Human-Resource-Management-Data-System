<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments with filters.
     */
    public function index(Request $request)
    {
        $query = Appointment::with('employee', 'position');

        // Search by employee name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            })->orWhereHas('position', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by appointment type
        if ($request->filled('type')) {
            $query->where('appointment_type', $request->input('type'));
        }

        // Sort
        $sortBy = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $appointments = $query->paginate(15);

        $stats = [
            'total' => Appointment::count(),
            'active' => Appointment::where('status', 'Active')->count(),
            'ended' => Appointment::where('status', 'Ended')->count(),
        ];

        $employees = Employee::where('status', 'Active')->get();
        $positions = Position::where('abolished', false)->get();

        return view('appointments.index', compact('appointments', 'stats', 'employees', 'positions'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $employees = Employee::where('status', 'Active')->get();
        $positions = Position::where('abolished', false)->get();
        return view('appointments.create', compact('employees', 'positions'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'position_id' => 'required|exists:positions,id',
            'appointment_start' => 'required|date',
            'appointment_end' => 'nullable|date|after_or_equal:appointment_start',
            'appointment_type' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|in:Active,Ended',
        ]);

        Appointment::create($validated);

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load('employee', 'position');
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the appointment.
     */
    public function edit(Appointment $appointment)
    {
        $employees = Employee::where('status', 'Active')->get();
        $positions = Position::where('abolished', false)->get();
        return view('appointments.edit', compact('appointment', 'employees', 'positions'));
    }

    /**
     * Update the appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'position_id' => 'required|exists:positions,id',
            'appointment_start' => 'required|date',
            'appointment_end' => 'nullable|date|after_or_equal:appointment_start',
            'appointment_type' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|in:Active,Ended',
        ]);

        $appointment->update($validated);

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    /**
     * Delete the appointment.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
