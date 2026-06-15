<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PlantillaRecord;
use App\Models\Employee;
use App\Models\Position;
use App\Models\OrganizationalUnit;
use App\Models\User;
use App\Models\Appointment;
use App\Models\SalarySchedule;
use App\Models\ActivityLog;
use App\Models\JobOrder;
use App\Models\CasualEmployee;

class ArchiveController extends Controller
{
    /**
     * Bulk archive (soft-delete) multiple records by IDs.
     */
    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer',
            'type'  => 'required|string|in:plantilla,casual,job_orders,permanent',
        ]);

        $ids  = $request->input('ids');
        $type = $request->input('type');

        $model = match ($type) {
            'plantilla'   => PlantillaRecord::class,
            'casual'      => CasualEmployee::class,
            'job_orders'  => JobOrder::class,
            'permanent'   => PlantillaRecord::class,
            default       => null,
        };

        if (!$model) {
            return back()->with('error', 'Invalid record type for bulk archive.');
        }

        $deleted = $model::whereIn('id', $ids)->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Bulk Archived',
                'description' => "Bulk archived {$deleted} {$type} record(s) — IDs: " . implode(', ', $ids),
            ]);
        }

        return back()->with('success', "{$deleted} record(s) archived successfully. You can restore them from the Archives page.");
    }

    /**
     * Bulk permanently delete multiple records by IDs.
     * Restricted to Super Admin.
     */
    public function bulkForceDelete(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Admins can permanently delete records.');
        }

        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer',
            'type'  => 'required|string|in:plantilla,casual,job_orders,permanent',
        ]);

        $ids  = $request->input('ids');
        $type = $request->input('type');

        $model = match ($type) {
            'plantilla'   => PlantillaRecord::class,
            'casual'      => CasualEmployee::class,
            'job_orders'  => JobOrder::class,
            'permanent'   => PlantillaRecord::class,
            default       => null,
        };

        if (!$model) {
            return back()->with('error', 'Invalid record type for bulk deletion.');
        }

        $deleted = $model::withTrashed()->whereIn('id', $ids)->forceDelete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Bulk Force Deleted',
                'description' => "Bulk permanently deleted {$deleted} {$type} record(s) — IDs: " . implode(', ', $ids),
            ]);
        }

        return back()->with('success', "{$deleted} record(s) have been permanently deleted.");
    }

    /**
     * Centralized Archives / Recycle Bin page.
     */
    public function index(Request $request)
    {

        $type = $request->input('type', 'plantilla');
        $search = $request->input('search');

        $data = match ($type) {
            'employees' => Employee::onlyTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('employee_number', 'like', "%{$search}%");
                    });
                })->latest('deleted_at')->paginate(25)->withQueryString(),
            
            'positions' => Position::onlyTrashed()->with('organizationalUnit')
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                    });
                })->latest('deleted_at')->paginate(25)->withQueryString(),
            
            'org_units' => OrganizationalUnit::onlyTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                    });
                })->latest('deleted_at')->paginate(25)->withQueryString(),
            
            'users' => User::onlyTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                })->latest('deleted_at')->paginate(25)->withQueryString(),
            
            'appointments' => Appointment::onlyTrashed()->with('employee', 'position')
                ->when($search, function ($query, $search) {
                    $query->whereHas('employee', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('position', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });
                })->latest('deleted_at')->paginate(25)->withQueryString(),
            
            'salary_schedules' => SalarySchedule::onlyTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('law_name', 'like', "%{$search}%");
                    });
                })->latest('deleted_at')->paginate(25)->withQueryString(),
            
            default => PlantillaRecord::onlyTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('item', 'like', "%{$search}%")
                          ->orWhere('position_title', 'like', "%{$search}%");
                    });
                })->latest('deleted_at')->paginate(25)->withQueryString(),
        };

        $counts = [
            'plantilla'        => PlantillaRecord::onlyTrashed()->count(),
            'employees'        => Employee::onlyTrashed()->count(),
            'positions'        => Position::onlyTrashed()->count(),
            'org_units'        => OrganizationalUnit::onlyTrashed()->count(),
            'users'            => User::onlyTrashed()->count(),
            'appointments'     => Appointment::onlyTrashed()->count(),
            'salary_schedules' => SalarySchedule::onlyTrashed()->count(),
            'job_orders'       => JobOrder::onlyTrashed()->count(),
            'casual'           => CasualEmployee::onlyTrashed()->count(),
        ];

        return view('archives.index', compact('data', 'type', 'counts'));
    }

    /**
     * Restore an archived record back to its active state.
     */
    public function restore(Request $request, $type, $id)
    {
        $model = $this->resolveModel($type, $id);

        if (!$model) {
            return back()->with('error', 'Record not found in archives.');
        }

        if ($type === 'salary_schedules') {
            $model->salaryGrades()->onlyTrashed()->restore();
        }
        $model->restore();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Restored Record',
                'description' => "Restored archived {$type} record (ID: {$id}) from the Archive.",
            ]);
        }

        return back()->with('success', 'Record successfully restored.');
    }

    /**
     * Permanently delete an archived record (cannot be undone).
     */
    public function forceDelete(Request $request, $type, $id)
    {
        $model = $this->resolveModel($type, $id);

        if (!$model) {
            return back()->with('error', 'Record not found in archives.');
        }

        if ($type === 'salary_schedules') {
            $model->salaryGrades()->onlyTrashed()->forceDelete();
        }
        $model->forceDelete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Permanently Deleted',
                'description' => "Permanently deleted {$type} record (ID: {$id}) from the Archive.",
            ]);
        }

        return back()->with('success', 'Record permanently deleted.');
    }

    /**
     * Archive (soft-delete) a PlantillaRecord slot entirely.
     * This is a stronger action than "Vacate" — it removes the item slot.
     */
    public function archivePlantilla(PlantillaRecord $allDatum)
    {
        $item = $allDatum->item;
        $name = trim($allDatum->first_name . ' ' . $allDatum->last_name) ?: 'Vacant';

        $allDatum->delete(); // Soft delete

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Archived Record',
                'description' => "Archived plantilla slot for Item {$item} ({$name}) — moved to Archives.",
            ]);
        }

        return back()->with('success', "Item {$item} archived successfully. You can restore it from the Archives page.");
    }

    /**
     * Archive (soft-delete) a Job Order record.
     */
    public function archiveJobOrder(JobOrder $jobOrder)
    {
        $name = $jobOrder->full_name ?: 'Unknown';
        $jobOrder->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Archived Record',
                'description' => "Archived Job Order record for {$name} — moved to Archives.",
            ]);
        }

        return back()->with('success', "Job Order record for {$name} archived successfully. You can restore it from the Archives page.");
    }

    /**
     * Archive (soft-delete) a Casual Employee record.
     */
    public function archiveCasual(CasualEmployee $casual)
    {
        $name = $casual->full_name ?: 'Unknown';
        $casual->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Archived Record',
                'description' => "Archived Casual employee record for {$name} — moved to Archives.",
            ]);
        }

        return back()->with('success', "Casual record for {$name} archived successfully. You can restore it from the Archives page.");
    }

    /**
     * Archive (soft-delete) a Permanent Employee (PlantillaRecord).
     */
    public function archivePermanent(PlantillaRecord $record)
    {
        $name = trim($record->first_name . ' ' . $record->last_name) ?: 'Vacant';
        $record->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Archived Record',
                'description' => "Archived permanent employee record for {$name} — moved to Archives.",
            ]);
        }

        return back()->with('success', "Record for {$name} archived successfully. You can restore it from the Archives page.");
    }

    /**
     * Resolve model from type string and id (only from trashed records).
     */
    private function resolveModel(string $type, int|string $id)
    {
        return match ($type) {
            'plantilla'        => PlantillaRecord::onlyTrashed()->find($id),
            'employees'        => Employee::onlyTrashed()->find($id),
            'positions'        => Position::onlyTrashed()->find($id),
            'org_units'        => OrganizationalUnit::onlyTrashed()->find($id),
            'users'            => User::onlyTrashed()->find($id),
            'appointments'     => Appointment::onlyTrashed()->find($id),
            'salary_schedules' => SalarySchedule::onlyTrashed()->find($id),
            'job_orders'       => JobOrder::onlyTrashed()->find($id),
            'casual'           => CasualEmployee::onlyTrashed()->find($id),
            default            => null,
        };
    }
}
