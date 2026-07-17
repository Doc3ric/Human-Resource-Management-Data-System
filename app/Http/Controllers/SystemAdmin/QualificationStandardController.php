<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\QualificationStandard;
use Illuminate\Http\Request;

class QualificationStandardController extends Controller
{
    public function index(Request $request)
    {
        $query = QualificationStandard::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('position_title', 'like', "%{$search}%");
        }

        $standards = $query->orderBy('position_title')->paginate(20)->withQueryString();

        return view('system.qualification-standards.index', compact('standards'));
    }

    public function create()
    {
        return view('system.qualification-standards.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'position_title' => 'required|string|max:255|unique:qualification_standards,position_title',
            'education' => 'nullable|string',
            'training' => 'nullable|string',
            'experience' => 'nullable|string',
            'eligibility' => 'nullable|string',
        ]);

        QualificationStandard::create($data);

        return redirect()->route('system.qualification-standards.index')->with('success', 'Qualification Standard created successfully.');
    }

    public function edit(QualificationStandard $qualificationStandard)
    {
        return view('system.qualification-standards.edit', compact('qualificationStandard'));
    }

    public function update(Request $request, QualificationStandard $qualificationStandard)
    {
        $data = $request->validate([
            'position_title' => 'required|string|max:255|unique:qualification_standards,position_title,' . $qualificationStandard->id,
            'education' => 'nullable|string',
            'training' => 'nullable|string',
            'experience' => 'nullable|string',
            'eligibility' => 'nullable|string',
        ]);

        $qualificationStandard->update($data);

        return redirect()->route('system.qualification-standards.index')->with('success', 'Qualification Standard updated successfully.');
    }

    public function destroy(QualificationStandard $qualificationStandard)
    {
        $qualificationStandard->delete();
        return redirect()->route('system.qualification-standards.index')->with('success', 'Qualification Standard deleted successfully.');
    }
}
