<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\OrganizationalUnit;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index()
    {
        $positions = Position::with('organizationalUnit')->get();
        $stats = [
            'total' => Position::count(),
            'filled' => Position::whereHas('appointments')->count(),
            'vacant' => Position::where('abolished', false)->whereDoesntHave('appointments')->count(),
            'abolished' => Position::where('abolished', true)->count(),
        ];
        
        return view('positions.index', compact('positions', 'stats'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create()
    {
        $organizationalUnits = OrganizationalUnit::where('status', 'Active')->get();
        return view('positions.create', compact('organizationalUnits'));
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_department_id' => 'required|exists:office_departments,id',
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:positions',
            'salary_grade' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'abolished' => 'nullable|boolean',
        ]);

        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified position.
     */
    public function show(Position $position)
    {
        $position->load('organizationalUnit', 'appointments');
        return view('positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position)
    {
        $organizationalUnits = OrganizationalUnit::where('status', 'Active')->get();
        return view('positions.edit', compact('position', 'organizationalUnits'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'office_department_id' => 'required|exists:office_departments,id',
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:positions,code,' . $position->id,
            'salary_grade' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'abolished' => 'nullable|boolean',
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy(Position $position)
    {
        // Check if position has appointments
        if ($position->appointments()->exists()) {
            return redirect()->route('positions.index')
                ->with('error', 'Cannot delete position with active appointments.');
        }

        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Position deleted successfully.');
    }
}
