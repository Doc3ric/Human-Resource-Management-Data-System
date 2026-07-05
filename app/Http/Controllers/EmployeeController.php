<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees with filters and search.
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        // Search by name or employee number
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by gender
        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        // Filter by civil status
        if ($request->filled('civil_status')) {
            $query->where('civil_status', $request->input('civil_status'));
        }

        // Sort
        $sortBy = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $employees = $query->paginate(15);
        
        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'Active')->count(),
            'inactive' => Employee::where('status', 'Inactive')->count(),
            'retired' => Employee::where('status', 'Retired')->count(),
        ];

        return view('employees.index', compact('employees', 'stats'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'employee_number' => 'required|string|max:255|unique:employees',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:Male,Female',
            'email' => 'nullable|email|unique:employees',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'civil_status' => 'nullable|string',
            'date_hired' => 'nullable|date',
            'status' => 'required|in:Active,Inactive,Retired,Resigned',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $employee->load('appointments.position');
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the employee.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the employee.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'employee_number' => 'required|string|max:255|unique:employees,employee_number,' . $employee->id,
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:Male,Female',
            'email' => 'nullable|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'civil_status' => 'nullable|string',
            'date_hired' => 'nullable|date',
            'status' => 'required|in:Active,Inactive,Retired,Resigned',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Delete the employee.
     */
    public function destroy(Employee $employee)
    {
        // Check for appointments
        if ($employee->appointments()->exists()) {
            return redirect()->route('employees.index')
                ->with('error', 'Cannot delete employee with appointment records.');
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Export employees to Excel/PDF (UI placeholder).
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'excel');
        
        // For now, just show a message - actual export comes later
        return redirect()->route('employees.index')
            ->with('info', "Export to {$format} feature coming soon!");
    }
}
