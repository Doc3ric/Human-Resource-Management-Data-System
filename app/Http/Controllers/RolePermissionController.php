<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;

class RolePermissionController extends Controller
{
    // Modules aligned with the actual sidebar groups. Only sub-modules with real
    // routed controllers are listed here (Module 4A.3's "auto-include" clause —
    // future modules register their own rows when they're actually built).
    private array $modules = [
        'Dashboard & GAD Analytics' => [
            // Enhancement Spec RBAC Baseline — granted to every role by
            // default regardless of other restrictions (see the one-time
            // backfill migration for existing roles; $defaults below covers
            // any future role created with zero permissions).
            'Dashboard',
            'GAD Analytics',
        ],
        'Personnel Records' => [
            'All Data', 'Plantilla', 'Permanent Employees', 'Casual Employees', 'Job Orders',
        ],
        'Appointment' => [
            'Recruitment', 'Contract Status', 'Batch Renewal', 'Panel Members', 'Interview Evaluations',
            'TWG Scoring', 'HRMPSB Rating Scales', 'HRMPSB Signatories',
            // Module 6.1 — additive, criterion-driven scoring engine alongside
            // the existing fixed-column "TWG Scoring" above. "edit" also gates
            // the Chairperson-only unlock action (Module 6.4).
            'TWG Dynamic Scoring',
            // VPPM (RA 7041 / 2025 ORAOHRA Sec.26/30/31) — "edit" also gates
            // who may submit to CSC FO / log posting sites / republish.
            'Vacancy Publication',
        ],
        'Employee Development' => [
            'IPCR Ratings', 'Training & Certificates',
        ],
        'Welfare & Benefits' => [
            'Plantilla of Personnel', 'Salary Grades', 'Salary Grade Table', 'SSL Schedules', 'Retirement',
        ],
        'Leave Administration' => [
            'Leave Application', 'Leave Violations',
        ],
        'IDCC & Documents' => [
            // "view" on these is the sensitive capability bit itself — none
            // are granted by default to any role (Module 9 Stage 5/9A.7).
            'Document Ingestion & Capture', 'SPI Unmask', 'RACCS Access',
            'Bluetooth Capture', 'External Vision Opt-in',
            // "edit" here gates who may edit the Records Retention Matrix
            // (Module 1B.5/1B.7 — Records Officer only by default).
            'Records Retention Matrix', 'LGU Documents',
        ],
        'Discipline' => [
            // Incident: view=incident_review, add=incident_create, edit=incident_finalize, delete=incident_escalate
            'Incident Reports',
            // RACCS: Sensitive — none granted by default. Role-gated separately to
            // Discipline Committee regardless of this bit.
            'Disciplinary Cases',
        ],
        'System & Administration' => [
            'User Management', 'Audit Logs', 'Activity Logs', 'Import Data', 'Database Rollback', 'Backup & Recovery',
        ],
    ];

    private array $actions = ['view', 'add', 'edit', 'delete', 'archive'];

    // Default permissions per role (seeded on first load if missing)
    // Enhancement Spec RBAC Baseline — granted to every role regardless of
    // its other permissions (existing roles are backfilled by a dedicated
    // migration; this list only matters for a brand-new role with zero
    // permissions, per the "seed defaults" rule below).
    private array $baselineDefaults = ['view Dashboard', 'view LGU Documents'];

    private array $defaults = [
        'Personnel Records' => [
            'view All Data', 'add All Data', 'edit All Data', 'delete All Data', 'archive All Data',
            'view Plantilla', 'add Plantilla', 'edit Plantilla', 'delete Plantilla', 'archive Plantilla',
            'view Permanent Employees', 'add Permanent Employees', 'edit Permanent Employees', 'delete Permanent Employees', 'archive Permanent Employees',
            'view Casual Employees', 'add Casual Employees', 'edit Casual Employees', 'delete Casual Employees', 'archive Casual Employees',
            'view Job Orders', 'add Job Orders', 'edit Job Orders', 'delete Job Orders', 'archive Job Orders',
            'view Recruitment', 'view Contract Status', 'view Batch Renewal',
            'view IPCR Ratings',
            'view Leave Application', 'add Leave Application', 'edit Leave Application',
        ],
        'Appointment' => [
            'view Recruitment', 'add Recruitment', 'edit Recruitment', 'delete Recruitment',
            'view Contract Status', 'view Batch Renewal', 'add Batch Renewal',
            'view Panel Members', 'add Panel Members', 'edit Panel Members',
            'view Interview Evaluations', 'add Interview Evaluations', 'edit Interview Evaluations',
            'view TWG Scoring', 'add TWG Scoring', 'edit TWG Scoring',
            'view Vacancy Publication', 'add Vacancy Publication', 'edit Vacancy Publication',
        ],
        'Performance Management' => [
            'view IPCR Ratings', 'add IPCR Ratings', 'edit IPCR Ratings',
            'view GAD Analytics',
        ],
        'Welfare & Benefits' => [
            'view Plantilla of Personnel', 'add Plantilla of Personnel', 'edit Plantilla of Personnel',
            'view Salary Grades', 'view Salary Grade Table',
            'view SSL Schedules', 'add SSL Schedules', 'edit SSL Schedules',
            'view Retirement',
        ],
        'Viewer' => [
            'view All Data', 'view Plantilla', 'view Permanent Employees',
            'view Casual Employees', 'view Job Orders',
            'view Recruitment', 'view Contract Status',
            'view IPCR Ratings',
            'view Plantilla of Personnel', 'view Salary Grades', 'view Salary Grade Table',
            'view SSL Schedules', 'view Retirement',
            'view Panel Members', 'view Interview Evaluations', 'view TWG Scoring', 'view GAD Analytics',
        ],
        'Leave Administration' => [
            'view Leave Application', 'add Leave Application', 'edit Leave Application',
            'view Leave Violations', 'add Leave Violations', 'edit Leave Violations',
        ],
        'Employee Development' => [
            'view IPCR Ratings', 'add IPCR Ratings', 'edit IPCR Ratings',
            'view Training & Certificates', 'add Training & Certificates', 'edit Training & Certificates',
        ],
        // SPI Unmask / RACCS Access deliberately excluded — those stay
        // sensitive/MFA-gated (Module 9A.7) and are granted per-user via the
        // matrix or User Overrides screen, never by role default.
        'Document Filing' => [
            'view Document Ingestion & Capture', 'add Document Ingestion & Capture', 'edit Document Ingestion & Capture',
            'view LGU Documents', 'add LGU Documents', 'edit LGU Documents',
            'view Records Retention Matrix', 'add Records Retention Matrix', 'edit Records Retention Matrix',
        ],
        // Disciplinary Cases (RACCS) deliberately excluded — stays reserved
        // for the separate "Discipline Committee" role, MFA-gated.
        'Discipline' => [
            'view Incident Reports', 'add Incident Reports', 'edit Incident Reports',
        ],
    ];

    public function index()
    {
        $roles      = Role::where('name', '!=', 'System & Administration')->orderBy('name')->get();
        $superAdmin = Role::where('name', 'System & Administration')->first();
        $modules    = $this->modules;
        $actions    = $this->actions;

        // Create any missing permissions
        foreach ($modules as $moduleList) {
            foreach ($moduleList as $module) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate(['name' => "{$action} {$module}", 'guard_name' => 'web']);
                }
            }
        }

        // Seed defaults for roles that have zero permissions assigned
        foreach ($roles as $role) {
            if ($role->permissions->count() === 0 && isset($this->defaults[$role->name])) {
                $perms = collect($this->defaults[$role->name])
                    ->merge($this->baselineDefaults)
                    ->unique()
                    ->map(fn($p) => Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']))
                    ->pluck('name')
                    ->toArray();
                $role->syncPermissions($perms);
            }
        }

        // Refresh roles with their permissions
        $roles = Role::where('name', '!=', 'System & Administration')->orderBy('name')->get();

        return view('users.role-matrix', compact('roles', 'superAdmin', 'modules', 'actions'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only System & Administration can update the matrix.');
        }

        $data = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'nullable|array',
        ]);

        $submitted = $data['permissions'] ?? [];

        // Build the full list of permission names managed by this matrix
        $allManagedPerms = [];
        foreach ($this->modules as $moduleList) {
            foreach ($moduleList as $module) {
                foreach ($this->actions as $action) {
                    $allManagedPerms[] = "{$action} {$module}";
                }
            }
        }

        foreach (Role::where('name', '!=', 'System & Administration')->get() as $role) {
            // Permissions checked for this role in the submitted form
            $checkedPerms = $this->applyViewLock(array_keys($submitted[$role->id] ?? []));

            // Keep permissions outside this matrix untouched, sync the ones inside
            $existingOther = $role->permissions
                ->pluck('name')
                ->filter(fn($p) => !in_array($p, $allManagedPerms))
                ->values()
                ->toArray();

            // Enhancement Spec RBAC Baseline — Dashboard/Document Filing stay
            // granted "regardless of other permission restrictions", so the
            // matrix can't uncheck them away even if submitted without them.
            $role->syncPermissions(array_unique(array_merge($existingOther, $checkedPerms, $this->baselineDefaults)));
        }

        // Flush Spatie permission cache so changes take effect immediately
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'Updated Role Permissions',
            'description' => 'Updated the Role-Permission matrix configurations.',
        ]);

        return redirect()->route('users.role-matrix')->with('success', 'Role-Permission Matrix updated. Changes are effective immediately.');
    }

    /**
     * Module 4A.2: per-user overrides on top of role defaults. Spatie has no
     * built-in "deny" override, so this manages direct-to-user grants
     * (additive on top of whatever the user's role already grants) — the
     * common real-world case of "give this one person a bit extra."
     */
    public function userOverrides(Request $request)
    {
        $users = \App\Models\User::where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        $selectedUser = $request->filled('user_id')
            ? $users->firstWhere('id', (int) $request->integer('user_id'))
            : $users->first();

        $modules = $this->modules;
        $actions = $this->actions;
        $directPerms = $selectedUser
            ? $selectedUser->getDirectPermissions()->pluck('name')->flip()->toArray()
            : [];

        return view('users.role-matrix-user-overrides', compact(
            'users', 'selectedUser', 'modules', 'actions', 'directPerms'
        ));
    }

    public function storeUserOverrides(Request $request, \App\Models\User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only System & Administration can update permission overrides.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own permission overrides.');
        }

        $data = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'nullable|string',
        ]);

        $checkedPerms = $this->applyViewLock(array_keys($data['permissions'] ?? []));

        foreach ($checkedPerms as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        $user->syncPermissions($checkedPerms);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'Updated User Permission Overrides',
            'description' => "Updated direct permission overrides for {$user->name}.",
        ]);

        return redirect()->route('users.role-matrix.user-overrides', ['user_id' => $user->id])
            ->with('success', "Permission overrides for {$user->name} updated.");
    }

    /**
     * Module 4A.1: checking Add/Edit/Delete auto-enables and locks View for
     * that same sub-module.
     */
    private function applyViewLock(array $checkedPerms): array
    {
        $checked = array_flip($checkedPerms);

        foreach ($this->modules as $moduleList) {
            foreach ($moduleList as $module) {
                $hasWriteBit = isset($checked["add {$module}"])
                    || isset($checked["edit {$module}"])
                    || isset($checked["delete {$module}"]);

                if ($hasWriteBit) {
                    $checked["view {$module}"] = true;
                }
            }
        }

        return array_keys($checked);
    }
}
