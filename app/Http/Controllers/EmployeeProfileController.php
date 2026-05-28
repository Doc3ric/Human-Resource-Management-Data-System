<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\CasualEmployee;
use App\Models\JobOrder;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    public function show($type, $id)
    {
        $employee = match($type) {
            'plantilla', 'permanent' => PlantillaRecord::with(['attachments'])->findOrFail($id),
            'casual'                 => CasualEmployee::with(['attachments'])->findOrFail($id),
            'job_orders', 'job-order'=> JobOrder::with(['attachments'])->findOrFail($id),
            default                  => abort(404),
        };

        return view('employee-profile.show', compact('employee', 'type'));
    }

    public function updatePicture(Request $request, $type, $id)
    {
        $request->validate([
            'profile_picture' => 'required|image|max:5120',
        ]);

        $employee = match($type) {
            'plantilla', 'permanent' => PlantillaRecord::findOrFail($id),
            'casual'                 => CasualEmployee::findOrFail($id),
            'job_orders', 'job-order'=> JobOrder::findOrFail($id),
            default                  => abort(404),
        };

        if ($employee->profile_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($employee->profile_picture)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->profile_picture);
        }

        $path = $request->file('profile_picture')->store('employee_profiles', 'public');
        
        // Use query builder or direct update to bypass needing fillable if it's missing, but it's cleaner to just update
        $employee->profile_picture = $path;
        $employee->save();

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function removePicture($type, $id)
    {
        $employee = match($type) {
            'plantilla', 'permanent' => PlantillaRecord::findOrFail($id),
            'casual'                 => CasualEmployee::findOrFail($id),
            'job_orders', 'job-order'=> JobOrder::findOrFail($id),
            default                  => abort(404),
        };

        if ($employee->profile_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($employee->profile_picture)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->profile_picture);
        }

        $employee->profile_picture = null;
        $employee->save();

        return back()->with('success', 'Profile picture removed successfully.');
    }
}
