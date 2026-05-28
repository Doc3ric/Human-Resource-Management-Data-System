<?php

namespace App\Http\Controllers;

use App\Models\OrganizationalUnit;
use Illuminate\Http\Request;

class OrganizationalUnitController extends Controller
{
    /**
     * Display a listing of organizational units.
     */
    public function index()
    {
        $units = OrganizationalUnit::all();
        return view('organizational-units.index', compact('units'));
    }

    /**
     * Show the form for creating a new organizational unit.
     */
    public function create()
    {
        return view('organizational-units.create');
    }

    /**
     * Store a newly created organizational unit in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizational_units',
            'code' => 'required|string|max:255|unique:organizational_units',
            'status' => 'required|in:Active,Inactive',
        ]);

        OrganizationalUnit::create($validated);

        return redirect()->route('organizational-units.index')
            ->with('success', 'Organizational unit created successfully.');
    }

    /**
     * Display the specified organizational unit.
     */
    public function show(OrganizationalUnit $organizationalUnit)
    {
        return view('organizational-units.show', compact('organizationalUnit'));
    }

    /**
     * Show the form for editing the specified organizational unit.
     */
    public function edit(OrganizationalUnit $organizationalUnit)
    {
        return view('organizational-units.edit', compact('organizationalUnit'));
    }

    /**
     * Update the specified organizational unit in storage.
     */
    public function update(Request $request, OrganizationalUnit $organizationalUnit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizational_units,name,' . $organizationalUnit->id,
            'code' => 'required|string|max:255|unique:organizational_units,code,' . $organizationalUnit->id,
            'status' => 'required|in:Active,Inactive',
        ]);

        $organizationalUnit->update($validated);

        return redirect()->route('organizational-units.index')
            ->with('success', 'Organizational unit updated successfully.');
    }

    /**
     * Remove the specified organizational unit from storage.
     */
    public function destroy(OrganizationalUnit $organizationalUnit)
    {
        $organizationalUnit->delete();

        return redirect()->route('organizational-units.index')
            ->with('success', 'Organizational unit deleted successfully.');
    }
}
