<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GadAnalyticsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\StepIncrementController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AllDataController;
use App\Http\Controllers\RetirementController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SalaryScheduleController;
use App\Http\Controllers\EmployeeAttachmentController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\CasualController;
use App\Http\Controllers\PermanentController;
use App\Http\Controllers\EmployeeCodeController;
use Illuminate\Support\Facades\Route;

// ============================================
// Guest Routes
// ============================================
Route::redirect('/', '/login');

// Public-facing informational pages (no auth required)
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

// ── Setup Wizard (unguarded — SetupController redirects if users exist) ───
Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/', [SetupController::class, 'index'])->name('index');
    Route::get('/step/{step}', [SetupController::class, 'step'])->name('step');
    Route::post('/requirements', [SetupController::class, 'checkRequirements'])->name('requirements');
    Route::post('/test-database', [SetupController::class, 'testDatabase'])->name('test-database');
    Route::post('/run-migrations', [SetupController::class, 'runMigrations'])->name('run-migrations');
    Route::get('/seed', [SetupController::class, 'seedData'])->name('seed');
    Route::post('/create-admin', [SetupController::class, 'createAdmin'])->name('create-admin');
});

// ============================================
// Authenticated Routes
// ============================================
//
// ── Module 4A RBAC route-cutover audit (2026-07-05) ──────────────────────
// Every remaining `role:` middleware usage below was checked against the
// LIVE Spatie grants (`Role::with('permissions')->get()`), not just the
// $defaults array in RolePermissionController, before deciding whether to
// convert it to `permission:`. Conversion only happened where every
// non-super-admin role currently reaching the route via its role: alias
// already holds the matching permission bit (super_admin always passes
// either way via the Gate::before bypass in AppServiceProvider). Where a
// role: block remains below, it is because converting it would silently
// revoke access for a role that has no matching permission grant today
// (e.g. inventory_admin/"Personnel Records" holds no Audit Logs, Archives,
// User Management, Panel Members, or Employee-Code-generation permission
// at all, and salary_admin/"Welfare & Benefits" holds no Step Increment
// permission — that module isn't even registered in
// RolePermissionController's $modules list yet, a genuine Module 4A.3
// completeness gap, not just a route-wiring one). Granting those bits is
// an actual RBAC policy decision for whoever administers the Role Matrix
// UI to make deliberately, not something to infer from route middleware —
// so those blocks stay on `role:` and are flagged here rather than guessed
// at. GAD Analytics (below) was the one block that converted cleanly.
// Retirement's non-conversion was already documented in place by a prior
// pass; this audit re-confirmed it's still accurate.
Route::middleware('auth')->group(function () {

    // Dashboard (all roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
        ->name('activity-logs.index');
    Route::post('/activity-logs/{log}/undo', [\App\Http\Controllers\ActivityLogController::class, 'undo'])
        ->name('activity-logs.undo');
    Route::post('/activity-logs/mark-read', [\App\Http\Controllers\ActivityLogController::class, 'markAsRead'])
        ->name('activity-logs.mark-read');

    // Audit Logs (using Spatie Activitylog)
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])
        ->middleware('role:super_admin,inventory_admin')
        ->name('audit-logs.index');
    Route::post('/audit-logs/{activity}/undo', [\App\Http\Controllers\AuditLogController::class, 'undo'])
        ->middleware('role:super_admin,inventory_admin')
        ->name('audit-logs.undo');

    // ── Rollback by Date Range (super_admin only) ─────────────────────────
    Route::middleware('role:super_admin')
        ->prefix('rollback')
        ->name('rollback.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\RollbackController::class, 'index'])->name('index');
            Route::post('/preview', [\App\Http\Controllers\RollbackController::class, 'preview'])->name('preview');
            Route::post('/execute', [\App\Http\Controllers\RollbackController::class, 'execute'])->name('execute');
        });

    // ── Inventory of Personnel (inventory_admin + super_admin + viewer read) ─
    // Module 4A RBAC cutover — same live-verified pattern as Job Orders/Casual
    // (Personnel Records holds view/add/edit/delete Plantilla; Viewer holds
    // view Plantilla). No internal controller hard-gate exists here (unlike
    // CasualController::deleteAll()) and there is no bulk delete-all route
    // for Plantilla, so this group converts cleanly with no exclusions.
    Route::middleware('permission:view Plantilla')->group(function () {
        Route::get('plantilla/pwd-report', [PlantillaController::class, 'pwdReport'])->name('plantilla.pwd-report');
        Route::get('plantilla/pwd-report/export/pdf', [PlantillaController::class, 'exportPwdPdf'])->name('plantilla.pwd-report.export.pdf');
        Route::get('plantilla/pwd-report/export/excel', [PlantillaController::class, 'exportPwdExcel'])->name('plantilla.pwd-report.export.excel');
        Route::get('plantilla/ip-report', [PlantillaController::class, 'ipReport'])->name('plantilla.ip-report');
        Route::get('plantilla/ip-report/export/pdf', [PlantillaController::class, 'exportIpPdf'])->name('plantilla.ip-report.export.pdf');
        Route::get('plantilla/ip-report/export/excel', [PlantillaController::class, 'exportIpExcel'])->name('plantilla.ip-report.export.excel');
        Route::get('plantilla/position-report', [PlantillaController::class, 'positionReport'])->name('plantilla.position-report');
        Route::get('plantilla/position-report/export/pdf', [PlantillaController::class, 'exportPositionReportPdf'])->name('plantilla.position-report.export.pdf');
        Route::get('plantilla/position-report/export/excel', [PlantillaController::class, 'exportPositionReportExcel'])->name('plantilla.position-report.export.excel');
        Route::get('plantilla/reports', [PlantillaController::class, 'reports'])->name('plantilla.reports');
        Route::get('plantilla/reports/export/pdf/{report}', [PlantillaController::class, 'exportReportPdf'])->name('plantilla.reports.export.pdf');
        Route::get('plantilla/reports/export/excel/{report}', [PlantillaController::class, 'exportReportExcel'])->name('plantilla.reports.export.excel');
        Route::get('plantilla/reports/custom/pdf', [PlantillaController::class, 'exportCustomReportPdf'])->name('plantilla.reports.custom.pdf');
        Route::get('plantilla/reports/custom/excel', [PlantillaController::class, 'exportCustomReportExcel'])->name('plantilla.reports.custom.excel');
        Route::get('plantilla/vacant/export/pdf', [PlantillaController::class, 'exportVacantPdf'])->name('plantilla.vacant.export.pdf');
        Route::get('plantilla/vacant/export/excel', [PlantillaController::class, 'exportVacantExcel'])->name('plantilla.vacant.export.excel');
        Route::get('plantilla/vacant-funded/detail', [PlantillaController::class, 'vacantFundedDetail'])->name('plantilla.vacant-funded.detail');
        Route::get('plantilla/vacant-funded/detail/export/excel', [PlantillaController::class, 'exportVacantFundedDetailExcel'])->name('plantilla.vacant-funded.detail.export.excel');
        Route::get('plantilla/vacant-funded/detail/export/pdf', [PlantillaController::class, 'exportVacantFundedDetailPdf'])->name('plantilla.vacant-funded.detail.export.pdf');
        Route::get('plantilla/vacant-unfunded/detail', [PlantillaController::class, 'vacantUnfundedDetail'])->name('plantilla.vacant-unfunded.detail');
        Route::get('plantilla/vacant-unfunded/detail/export/excel', [PlantillaController::class, 'exportVacantUnfundedDetailExcel'])->name('plantilla.vacant-unfunded.detail.export.excel');
        Route::get('plantilla/vacant-unfunded/detail/export/pdf', [PlantillaController::class, 'exportVacantUnfundedDetailPdf'])->name('plantilla.vacant-unfunded.detail.export.pdf');
        Route::get('plantilla/form9', [PlantillaController::class, 'generateForm9'])->name('plantilla.form9');
        Route::get('plantilla/{plantilla}/service-record', [PlantillaController::class, 'generateServiceRecord'])->name('plantilla.service-record');
        Route::get('plantilla/{plantilla}/form33', [PlantillaController::class, 'generateForm33'])->name('plantilla.form33');
        Route::get('plantilla/attachments/{attachment}/download', [EmployeeAttachmentController::class, 'download'])->name('plantilla.attachments.download');
        Route::get('plantilla/attachments/{attachment}/view', [EmployeeAttachmentController::class, 'view'])->name('plantilla.attachments.view');
        Route::get('plantilla/item-details', [PlantillaController::class, 'getItemDetails'])->name('plantilla.item-details');
        // Plantilla CRUD — write routes locked to inventory_admin+
        //
        // BUG FIX (pre-existing, discovered during the RBAC cutover, unrelated
        // to it): plantilla/create must be registered BEFORE plantilla/{plantilla}
        // — otherwise the wildcard show route (same 2-segment shape) swallows
        // the literal "create" segment first, and PlantillaRecord model
        // binding then 404s on "create" as if it were an ID. This has been
        // silently breaking the real "Add New Employee" button (resources/
        // views/plantilla/index.blade.php) for every user, always — verified
        // via the router's own match() resolving /plantilla/create to
        // plantilla.show before this fix. Every other static plantilla/X
        // route in this group (pwd-report, ip-report, reports, etc.) was
        // already correctly ordered before {plantilla}; only "create" wasn't.
        Route::get('plantilla', [PlantillaController::class, 'index'])->name('plantilla.index');
        Route::get('plantilla/create', [PlantillaController::class, 'create'])->middleware('permission:add Plantilla')->name('plantilla.create');
        Route::get('plantilla/{plantilla}', [PlantillaController::class, 'show'])->name('plantilla.show');
        Route::post('plantilla', [PlantillaController::class, 'store'])->middleware('permission:add Plantilla')->name('plantilla.store');
        Route::get('plantilla/{plantilla}/edit', [PlantillaController::class, 'edit'])->middleware('permission:edit Plantilla')->name('plantilla.edit');
        Route::put('plantilla/{plantilla}', [PlantillaController::class, 'update'])->middleware('permission:edit Plantilla')->name('plantilla.update');
        Route::patch('plantilla/{plantilla}', [PlantillaController::class, 'update'])->middleware('permission:edit Plantilla');
        Route::delete('plantilla/{plantilla}', [PlantillaController::class, 'destroy'])->middleware('permission:delete Plantilla')->name('plantilla.destroy');
        // Attachment write (inventory+)
        Route::post('plantilla/{plantilla}/attachments', [EmployeeAttachmentController::class, 'store'])->middleware('permission:add Plantilla')->name('plantilla.attachments.store');
        Route::delete('plantilla/attachments/{attachment}', [EmployeeAttachmentController::class, 'destroy'])->middleware('permission:delete Plantilla')->name('plantilla.attachments.destroy');
        // Promotion (inventory+)
        Route::get('plantilla/{plantilla}/promote', [PlantillaController::class, 'promoteForm'])->middleware('permission:edit Plantilla')->name('plantilla.promote.form');
        Route::post('plantilla/{plantilla}/promote', [PlantillaController::class, 'promoteSubmit'])->middleware('permission:edit Plantilla')->name('plantilla.promote.submit');
        // Quick Add (inventory+)
        Route::get('plantilla/quick-add/office-position', [PlantillaController::class, 'quickAddForm'])->middleware('permission:add Plantilla')->name('plantilla.quick-add.form');
        Route::post('plantilla/quick-add/office-position', [PlantillaController::class, 'quickAddSubmit'])->middleware('permission:add Plantilla')->name('plantilla.quick-add.submit');
    });

    // ── Job Order Inventory ───────────────────────────────────────────────
    // Module 4A RBAC cutover pilot: converted from role: to the matrix-driven
    // permission: middleware (live-verified — Personnel Records/Viewer roles
    // already hold the equivalent bits in the production DB). delete-all is
    // deliberately LEFT on role:super_admin — it wipes all Job Orders, a
    // stricter scope than the generic "delete Job Orders" bit (which
    // Personnel Records also holds) would give, so converting it would widen
    // access to a bulk-destructive action. Not converted; flagged, not missed.
    Route::middleware('permission:view Job Orders')
        ->prefix('job-orders')
        ->name('job-orders.')
        ->group(function () {
            Route::get('/export/pdf', [JobOrderController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [JobOrderController::class, 'exportExcel'])->name('export.excel');
            Route::get('/attachments/{attachment}/download', [EmployeeAttachmentController::class, 'download'])->name('attachments.download');
            Route::get('/attachments/{attachment}/view', [EmployeeAttachmentController::class, 'view'])->name('attachments.view');
            Route::get('/', [JobOrderController::class, 'index'])->name('index');
            Route::get('/{jobOrder}/edit', [JobOrderController::class, 'edit'])->name('edit');
            // Write routes (inventory+)
            Route::get('/template', [JobOrderController::class, 'downloadTemplate'])->middleware('permission:add Job Orders')->name('template');
            Route::get('/import-history', [JobOrderController::class, 'importHistory'])->middleware('permission:add Job Orders')->name('import.history');
            Route::delete('/import-history/{id}/undo', [JobOrderController::class, 'undoImport'])->middleware('permission:delete Job Orders')->name('import.undo');
            Route::post('/import', [JobOrderController::class, 'importExcel'])->middleware('permission:add Job Orders')->name('import');
            Route::delete('/delete-all', [JobOrderController::class, 'deleteAll'])->middleware('role:super_admin')->name('delete-all');
            Route::post('/{jobOrder}/attachments', [EmployeeAttachmentController::class, 'storeForJobOrder'])->middleware('permission:add Job Orders')->name('attachments.store');
            Route::delete('/attachments/{attachment}', [EmployeeAttachmentController::class, 'destroy'])->middleware('permission:delete Job Orders')->name('attachments.destroy');
            Route::get('/create', [JobOrderController::class, 'create'])->middleware('permission:add Job Orders')->name('create');
            Route::post('/', [JobOrderController::class, 'store'])->middleware('permission:add Job Orders')->name('store');
            Route::put('/{jobOrder}', [JobOrderController::class, 'update'])->middleware('permission:edit Job Orders')->name('update');
            Route::delete('/{jobOrder}', [JobOrderController::class, 'destroy'])->middleware('permission:delete Job Orders')->name('destroy');
        });

    // ── Casual Employees Inventory ────────────────────────────────────────
    // Module 4A RBAC cutover pilot — same pattern as Job Orders above.
    // Casual's delete-all was already role:super_admin,inventory_admin (not
    // super-admin-only like Job Orders'), so it converts cleanly to the
    // matching permission bit without widening access.
    Route::middleware('permission:view Casual Employees')
        ->prefix('casual')
        ->name('casual.')
        ->group(function () {
            Route::get('/export/pdf', [CasualController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [CasualController::class, 'exportExcel'])->name('export.excel');
            Route::get('/attachments/{attachment}/download', [EmployeeAttachmentController::class, 'download'])->name('attachments.download');
            Route::get('/attachments/{attachment}/view', [EmployeeAttachmentController::class, 'view'])->name('attachments.view');
            Route::get('/', [CasualController::class, 'index'])->name('index');
            Route::get('/{casual}/edit', [CasualController::class, 'edit'])->name('edit');
            // Write routes (inventory+)
            Route::get('/template', [CasualController::class, 'downloadTemplate'])->middleware('permission:add Casual Employees')->name('template');
            Route::get('/import-history', [CasualController::class, 'importHistory'])->middleware('permission:add Casual Employees')->name('import.history');
            Route::delete('/import-history/{id}/undo', [CasualController::class, 'undoImport'])->middleware('permission:delete Casual Employees')->name('import.undo');
            Route::post('/import', [CasualController::class, 'importExcel'])->middleware('permission:add Casual Employees')->name('import');
            Route::delete('/delete-all', [CasualController::class, 'deleteAll'])->middleware('permission:delete Casual Employees')->name('delete-all');
            Route::post('/{casual}/attachments', [EmployeeAttachmentController::class, 'storeForCasual'])->middleware('permission:add Casual Employees')->name('attachments.store');
            Route::delete('/attachments/{attachment}', [EmployeeAttachmentController::class, 'destroy'])->middleware('permission:delete Casual Employees')->name('attachments.destroy');
            Route::get('/create', [CasualController::class, 'create'])->middleware('permission:add Casual Employees')->name('create');
            Route::post('/', [CasualController::class, 'store'])->middleware('permission:add Casual Employees')->name('store');
            Route::put('/{casual}', [CasualController::class, 'update'])->middleware('permission:edit Casual Employees')->name('update');
            Route::delete('/{casual}', [CasualController::class, 'destroy'])->middleware('permission:delete Casual Employees')->name('destroy');
        });

    // ── Permanent Employees ───────────────────────────────────────────────
    // Module 4A RBAC cutover — Personnel Records/Viewer already hold "view
    // Permanent Employees" in the live DB. delete-all is already
    // super-admin-only via an internal Auth::user()->isSuperAdmin() hard-gate
    // in PermanentController::deleteAll(), so its role:super_admin is left
    // as-is (consistent, not converted).
    Route::middleware('permission:view Permanent Employees')
        ->prefix('permanent')
        ->name('permanent.')
        ->group(function () {
            Route::get('/export/excel', [PermanentController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf',   [PermanentController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/', [PermanentController::class, 'index'])->name('index');
            Route::delete('/delete-all', [PermanentController::class, 'deleteAll'])->middleware('role:super_admin')->name('delete-all');
        });

    // ── Retirement Management ─────────────────────────────────────────────
    // NOT converted to permission: — live-DB check found "Personnel Records"
    // (inventory_admin) holds ZERO Retirement bits, and "Welfare & Benefits"
    // (the RBAC matrix's nominal owner per RolePermissionController's
    // $modules grouping) holds only "view Retirement", no add/edit/delete.
    // Converting this route as-is would lock out the real users who
    // currently reach it via role:inventory_admin. Fixing this needs an
    // actual RBAC grant decision (who should hold add/edit Retirement),
    // not a route-middleware-only change — flagged as a separate follow-up.
    Route::middleware('role:super_admin,inventory_admin,viewer')
        ->prefix('retirement')
        ->name('retirement.')
        ->group(function () {
            Route::get('/', [RetirementController::class, 'index'])->name('index');
            Route::get('/history', [RetirementController::class, 'history'])->name('history');
            Route::get('/export/pdf', [RetirementController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [RetirementController::class, 'exportExcel'])->name('export.excel');
            // Write routes (inventory+)
            Route::put('/{plantilla}/history', [RetirementController::class, 'updateHistory'])->middleware('role:super_admin,inventory_admin')->name('update-history');
            Route::post('/process-all', [RetirementController::class, 'processAll'])->middleware('role:super_admin,inventory_admin')->name('process-all');
            Route::post('/{plantilla}', [RetirementController::class, 'process'])->middleware('role:super_admin,inventory_admin')->name('process');
        });

    // ── All Data ──────────────────────────────────────────────────────────
    // Module 4A RBAC cutover — Personnel Records/Viewer already hold the full
    // All Data bit set in the live DB. No {allDatum} GET/show route exists
    // here, so unlike Plantilla there's no static-vs-wildcard ordering risk
    // for /create. No internal controller hard-gate found either.
    Route::middleware('permission:view All Data')
        ->prefix('all-data')
        ->name('all-data.')
        ->group(function () {
            Route::get('/salary/{grade}/{step}', [AllDataController::class, 'getSalary'])->name('salary');
            Route::get('/items-by-office', [AllDataController::class, 'getItemsByOffice'])->name('items-by-office');
            Route::get('/', [AllDataController::class, 'index'])->name('index');
            Route::get('/export/excel', [AllDataController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [AllDataController::class, 'exportPdf'])->name('export.pdf');
            // Write routes (inventory+)
            Route::post('/sync-salaries', [AllDataController::class, 'syncSalaries'])->middleware('permission:edit All Data')->name('sync-salaries');
            Route::get('/create', [AllDataController::class, 'create'])->middleware('permission:add All Data')->name('create');
            Route::post('/', [AllDataController::class, 'store'])->middleware('permission:add All Data')->name('store');
            // export/full was originally grouped under "write" (excluded from
            // viewer), not the general view-level export/excel|pdf above —
            // "edit All Data" (which Viewer lacks) preserves that exclusion;
            // "view All Data" (which Viewer holds) would have widened access.
            Route::get('/export/full', [AllDataController::class, 'exportFull'])->middleware('permission:edit All Data')->name('export.full');
            Route::get('/{allDatum}/edit', [AllDataController::class, 'edit'])->middleware('permission:edit All Data')->name('edit');
            Route::put('/{allDatum}', [AllDataController::class, 'update'])->middleware('permission:edit All Data')->name('update');
            Route::delete('/{allDatum}', [AllDataController::class, 'destroy'])->middleware('permission:delete All Data')->name('destroy');
        });

    // ── Step Increment & Longevity (salary_admin + super_admin) ──────────
    Route::middleware('role:super_admin,salary_admin')
        ->prefix('step-increment')
        ->name('step-increment.')
        ->group(function () {
            Route::get('/hub', [StepIncrementController::class, 'hub'])->name('hub');
            Route::get('/loyalty', [StepIncrementController::class, 'loyalty'])->name('loyalty');
            Route::get('/nosa', [StepIncrementController::class, 'nosaReport'])->name('nosa');
            Route::get('/nosa/export-pdf-by-office', [StepIncrementController::class, 'exportNosaReportPdfByOffice'])->name('nosa.export-pdf-by-office');
            Route::post('/process-all', [StepIncrementController::class, 'processAllDue'])->name('process-all');
            Route::get('/', [StepIncrementController::class, 'index'])->name('index');
            Route::get('/history', [StepIncrementController::class, 'history'])->name('history');
            Route::get('/salary-table', [StepIncrementController::class, 'salaryTable'])->name('salary-table');
            Route::get('/office-report', [StepIncrementController::class, 'officeReport'])->name('office-report');
            Route::get('/office-report/export-excel', [StepIncrementController::class, 'exportOfficeReportExcel'])->name('office-report.export-excel');
            Route::get('/office-report/export-pdf', [StepIncrementController::class, 'exportOfficeReportPdf'])->name('office-report.export-pdf');
            Route::get('/office-report/export-excel-by-office', [StepIncrementController::class, 'exportOfficeReportExcelByOffice'])->name('office-report.export-excel-by-office');
            Route::get('/office-report/export-pdf-by-office', [StepIncrementController::class, 'exportOfficeReportPdfByOffice'])->name('office-report.export-pdf-by-office');
            Route::post('/{plantilla}/process', [StepIncrementController::class, 'process'])->name('process');
            Route::post('/{plantilla}/process/magna-carta', [StepIncrementController::class, 'processMagnaCarta'])->name('process-magna-carta');
            Route::get('/pdf/nosi/bulk', [\App\Http\Controllers\NoticeController::class, 'generateNosiBulk'])->name('pdf.nosi-bulk');
            Route::get('/pdf/nolp/bulk', [\App\Http\Controllers\NoticeController::class, 'generateNolpBulk'])->name('pdf.nolp-bulk');
            Route::get('/{plantilla}/pdf/nosi', [\App\Http\Controllers\NoticeController::class, 'generateNosi'])->name('pdf.nosi');
            Route::get('/{plantilla}/pdf/nolp', [\App\Http\Controllers\NoticeController::class, 'generateNolp'])->name('pdf.nolp');
            Route::get('/docx/nosi/bulk', [\App\Http\Controllers\NoticeController::class, 'generateNosiBulkDocx'])->name('docx.nosi-bulk');
            Route::get('/docx/nolp/bulk', [\App\Http\Controllers\NoticeController::class, 'generateNolpBulkDocx'])->name('docx.nolp-bulk');
            Route::get('/{plantilla}/docx/nosi', [\App\Http\Controllers\NoticeController::class, 'generateNosiDocx'])->name('docx.nosi');
            Route::get('/{plantilla}/docx/nolp', [\App\Http\Controllers\NoticeController::class, 'generateNolpDocx'])->name('docx.nolp');
            Route::get('/{plantilla}/pdf/nosa', [\App\Http\Controllers\NoticeController::class, 'generateNosa'])->name('pdf.nosa');
            Route::get('/{plantilla}/pdf/loyalty-incentive', [\App\Http\Controllers\NoticeController::class, 'generateLoyaltyIncentive'])->name('pdf.loyalty-incentive');
            Route::get('/loyalty-incentive-settings',         [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'index'])->name('loyalty-incentive-settings.index');
            Route::post('/loyalty-incentive-settings/upload', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'upload'])->name('loyalty-incentive-settings.upload');
            Route::post('/loyalty-incentive-settings/{id}/set-active', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'setActive'])->name('loyalty-incentive-settings.set-active');
            Route::post('/loyalty-incentive-settings/set-none', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'setNone'])->name('loyalty-incentive-settings.set-none');
            Route::delete('/loyalty-incentive-settings/{id}', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'destroy'])->name('loyalty-incentive-settings.destroy');
            Route::post('/{plantilla}/dismiss-loyalty', [StepIncrementController::class, 'dismissLoyalty'])->name('dismiss-loyalty');
            Route::post('/{plantilla}/restore-loyalty', [StepIncrementController::class, 'restoreLoyalty'])->name('restore-loyalty');
        });

    // ── Salary Grade Management (salary_admin + super_admin) ─────────────
    Route::middleware('role:super_admin,salary_admin')
        ->prefix('salary-grades')
        ->name('salary-grades.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\SalaryGradeController::class, 'index'])->name('index');
            Route::post('/update-all', [\App\Http\Controllers\SalaryGradeController::class, 'updateAll'])->name('update-all');
        });

    // ── Salary Schedule (SSL Tranche) Management (salary_admin + super_admin) ─
    Route::middleware('role:super_admin,salary_admin')
        ->prefix('salary-schedules')
        ->name('salary-schedules.')
        ->group(function () {
            Route::get('/', [SalaryScheduleController::class, 'index'])->name('index');
            Route::get('/create', [SalaryScheduleController::class, 'create'])->name('create');
            Route::post('/', [SalaryScheduleController::class, 'store'])->name('store');
            Route::get('/{salarySchedule}/edit', [SalaryScheduleController::class, 'edit'])->name('edit');
            Route::put('/{salarySchedule}', [SalaryScheduleController::class, 'update'])->name('update');
            Route::post('/{schedule}/set-active', [SalaryScheduleController::class, 'setActive'])->name('set-active');
            Route::post('/{schedule}/apply-to-plantilla', [SalaryScheduleController::class, 'applyToPlantilla'])->name('apply-to-plantilla');
            Route::delete('/{schedule}', [SalaryScheduleController::class, 'destroy'])->name('destroy');
        });

    // ── Import (super_admin only) ─────────────────────────────────────────
    Route::middleware('role:super_admin')
        ->prefix('imports')
        ->name('imports.')
        ->group(function () {
            Route::get('/', [ImportController::class, 'index'])->name('index');
            Route::get('/template', [ImportController::class, 'template'])->name('template');
            Route::get('/history', [ImportController::class, 'history'])->name('history');
            Route::get('/export-errors', [ImportController::class, 'exportErrors'])->name('export-errors');
            Route::get('/preview-errors', [ImportController::class, 'exportPreviewErrors'])->name('preview-errors');
            Route::post('/back-to-map', [ImportController::class, 'backToMap'])->name('back-to-map');
            Route::post('/read-headers', [ImportController::class, 'readHeaders'])->name('read-headers');
            Route::post('/map', [ImportController::class, 'map'])->name('map');
            Route::post('/preview', [ImportController::class, 'preview'])->name('preview');
            Route::post('/execute', [ImportController::class, 'execute'])
                ->middleware('throttle:10,1')
                ->name('execute');
            Route::delete('/{id}/undo', [ImportController::class, 'undoImport'])->name('undo');
        });

    // ── Employee Code Bulk Generation (super_admin only) ─────────────────
    Route::middleware('role:super_admin')
        ->post('/employee-codes/generate-all', [EmployeeCodeController::class, 'generateAll'])
        ->name('employee-codes.generate-all');

    // ── User Management (super_admin + inventory_admin) ───────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->prefix('users')
        ->name('users.')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/role-matrix', [RolePermissionController::class, 'index'])->name('role-matrix');
            Route::post('/role-matrix', [RolePermissionController::class, 'store'])->name('role-matrix.store');
            Route::get('/role-matrix/user-overrides', [RolePermissionController::class, 'userOverrides'])->name('role-matrix.user-overrides');
            Route::post('/role-matrix/user-overrides/{user}', [RolePermissionController::class, 'storeUserOverrides'])->name('role-matrix.user-overrides.store');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::post('/{user}/approve', [UserController::class, 'approve'])->name('approve');
            Route::delete('/{user}/reject', [UserController::class, 'reject'])->name('reject');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

    // ── Administration Page (super_admin + inventory_admin) ───────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->get('/administration', function () {
            return view('administration.index');
        })->name('administration.index');

    // ── Archives / Recycle Bin (super_admin + inventory_admin) ───────────
    Route::middleware('role:super_admin,inventory_admin')
        ->prefix('archives')
        ->name('archives.')
        ->group(function () {
            Route::get('/', [ArchiveController::class, 'index'])->name('index');
            Route::post('/{type}/{id}/restore', [ArchiveController::class, 'restore'])->name('restore');
            Route::delete('/{type}/{id}/force-delete', [ArchiveController::class, 'forceDelete'])->name('force-delete');
        });

    // ── Archive routes ────────────────────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->post('all-data/{allDatum}/archive', [ArchiveController::class, 'archivePlantilla'])
        ->name('all-data.archive');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('job-orders/{jobOrder}/archive', [ArchiveController::class, 'archiveJobOrder'])
        ->name('job-orders.archive');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('casual/{casual}/archive', [ArchiveController::class, 'archiveCasual'])
        ->name('casual.archive');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('permanent/{record}/archive', [ArchiveController::class, 'archivePermanent'])
        ->name('permanent.archive');

    // ── Bulk Archive / Force Delete ───────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->post('all-data/bulk-archive', [ArchiveController::class, 'bulkArchive'])
        ->name('all-data.bulk-archive');
    Route::middleware('role:super_admin')
        ->post('all-data/bulk-force-delete', [ArchiveController::class, 'bulkForceDelete'])
        ->name('all-data.bulk-force-delete');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('casual/bulk-archive', [ArchiveController::class, 'bulkArchive'])
        ->name('casual.bulk-archive');
    Route::middleware('role:super_admin')
        ->post('casual/bulk-force-delete', [ArchiveController::class, 'bulkForceDelete'])
        ->name('casual.bulk-force-delete');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('job-orders/bulk-archive', [ArchiveController::class, 'bulkArchive'])
        ->name('job-orders.bulk-archive');
    Route::middleware('role:super_admin')
        ->post('job-orders/bulk-force-delete', [ArchiveController::class, 'bulkForceDelete'])
        ->name('job-orders.bulk-force-delete');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('permanent/bulk-archive', [ArchiveController::class, 'bulkArchive'])
        ->name('permanent.bulk-archive');
    Route::middleware('role:super_admin')
        ->post('permanent/bulk-force-delete', [ArchiveController::class, 'bulkForceDelete'])
        ->name('permanent.bulk-force-delete');

    // ── Contract Status Dashboard ─────────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin,appointment_admin,viewer')->group(function () {
        Route::get('contract-status', [\App\Http\Controllers\ContractStatusController::class, 'index'])
            ->name('contract-status.index');
        Route::get('contract-status/drilldown', [\App\Http\Controllers\ContractStatusController::class, 'drilldown'])
            ->name('contract-status.drilldown');
    });

    // ── Batch Contract Renewal ────────────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin,appointment_admin')->group(function () {
        Route::get('batch-renewal', [\App\Http\Controllers\BatchRenewalController::class, 'index'])
            ->name('batch-renewal.index');
        Route::post('batch-renewal/validate', [\App\Http\Controllers\BatchRenewalController::class, 'validate'])
            ->name('batch-renewal.validate');
        Route::post('batch-renewal/process', [\App\Http\Controllers\BatchRenewalController::class, 'process'])
            ->name('batch-renewal.process');
    });
    // Request-to-renew (Module 1A.8) is open to any authenticated session
    // that can reach a plantilla record — it never commits, only signals.
    Route::post('plantilla/{plantillaRecord}/request-renewal', [\App\Http\Controllers\BatchRenewalController::class, 'requestRenewal'])
        ->name('plantilla.request-renewal');
    // Individual renewal clearance (Module 1.2/1A.6) — the same commit
    // service as Batch Renewal, scoped to one record; RBAC-gated inside.
    Route::post('plantilla/{plantilla}/renew', [\App\Http\Controllers\PlantillaController::class, 'renewIndividual'])
        ->name('plantilla.renew-individual');

    // ── Global Search ──────────────────────────────────────────────────────
    Route::get('/search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])
        ->name('search');

    // ── Employee 201 Profile ───────────────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin,viewer')
        ->get('employees/{type}/{id}/profile', [\App\Http\Controllers\EmployeeProfileController::class, 'show'])
        ->name('employees.profile');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('employees/{type}/{id}/profile/picture', [\App\Http\Controllers\EmployeeProfileController::class, 'updatePicture'])
        ->name('employees.profile.picture');
    Route::middleware('role:super_admin,inventory_admin')
        ->delete('employees/{type}/{id}/profile/picture', [\App\Http\Controllers\EmployeeProfileController::class, 'removePicture'])
        ->name('employees.profile.picture.remove');

    // ── Performance (IPCR) Module ─────────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin,performance_admin,viewer')
        ->prefix('performance')
        ->name('performance.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\PerformanceController::class, 'index'])->name('index');
            Route::get('/employees', [\App\Http\Controllers\PerformanceController::class, 'getEmployees'])->name('employees');
            Route::post('/save', [\App\Http\Controllers\PerformanceController::class, 'saveRating'])->middleware('role:super_admin,inventory_admin,performance_admin')->name('save');
            Route::post('/batch-save', [\App\Http\Controllers\PerformanceController::class, 'batchSave'])->middleware('role:super_admin,inventory_admin,performance_admin')->name('batch-save');
            Route::post('/settings', [\App\Http\Controllers\PerformanceController::class, 'saveSettings'])->middleware('role:super_admin,inventory_admin,performance_admin')->name('settings.save');
            Route::post('/export', [\App\Http\Controllers\PerformanceController::class, 'export'])->name('export');
            Route::post('/import-template', [\App\Http\Controllers\PerformanceController::class, 'downloadTemplate'])->middleware('role:super_admin,inventory_admin,performance_admin')->name('import-template');
            Route::post('/import', [\App\Http\Controllers\PerformanceController::class, 'importRatings'])->middleware('role:super_admin,inventory_admin,performance_admin')->name('import');
        });

        // ── Recruitment Module ──
        Route::prefix('recruitment')->name('recruitment.')->group(function () {
            
            // Encoder & Admin Routes
            Route::middleware('role:super_admin,inventory_admin,appointment_admin,appointment_encoder')->group(function () {
                Route::get('/', [\App\Http\Controllers\RecruitmentController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\RecruitmentController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\RecruitmentController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [\App\Http\Controllers\RecruitmentController::class, 'edit'])->name('edit');
                Route::put('/{id}', [\App\Http\Controllers\RecruitmentController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\RecruitmentController::class, 'destroy'])->name('destroy');
            });

            // Admin Only Routes
            Route::middleware('role:super_admin,inventory_admin,appointment_admin')->group(function () {
                Route::post('/report', [\App\Http\Controllers\RecruitmentController::class, 'generateReport'])->name('report');
                Route::post('/import', [\App\Http\Controllers\RecruitmentController::class, 'importCsv'])->name('import');
                Route::get('/import-excel', [\App\Http\Controllers\RecruitmentController::class, 'importExcelForm'])->name('import-excel');
                Route::post('/import-excel/map', [\App\Http\Controllers\RecruitmentController::class, 'importExcelMap'])->name('import-excel.map');
                Route::post('/import-excel/process', [\App\Http\Controllers\RecruitmentController::class, 'importExcelProcess'])->name('import-excel.process');
                Route::post('/{id}/evaluate', [\App\Http\Controllers\RecruitmentController::class, 'saveEvaluation'])->name('evaluate');
            });

        });

        // ── Recruitment Module – HRMPSB & shared (SA/IA manage; encoders view records) ──
        Route::prefix('recruitment')->name('recruitment.')->group(function () {

            // HRMPSB admin-only routes: SA/IA
            Route::middleware('role:super_admin,inventory_admin')->group(function () {
                Route::get('/hrmpsb/interview', [\App\Http\Controllers\InterviewEvaluationController::class, 'index'])->name('hrmpsb.interview.index');
                Route::get('/hrmpsb/comparative-report', [\App\Http\Controllers\InterviewEvaluationController::class, 'comparativeReport'])->name('hrmpsb.comparative_report');
                Route::get('/hrmpsb-settings', [\App\Http\Controllers\HrmpsbScoreController::class, 'settings'])->name('hrmpsb.settings');
                Route::post('/hrmpsb-settings', [\App\Http\Controllers\HrmpsbScoreController::class, 'storeScale'])->name('hrmpsb.settings.store');
                Route::delete('/hrmpsb-settings/{scale}', [\App\Http\Controllers\HrmpsbScoreController::class, 'destroyScale'])->name('hrmpsb.settings.destroy');
                Route::get('/hrmpsb/signatories', [\App\Http\Controllers\InterviewEvaluationController::class, 'signatories'])->name('hrmpsb.signatories');
                Route::post('/hrmpsb/signatories', [\App\Http\Controllers\InterviewEvaluationController::class, 'storeSignatories'])->name('hrmpsb.signatories.store');
                Route::post('/hrmpsb/weights', [\App\Http\Controllers\InterviewEvaluationController::class, 'storeWeights'])->name('hrmpsb.weights.store');
                Route::post('/hrmpsb/twg-weights', [\App\Http\Controllers\HrmpsbScoreController::class, 'storeTwgWeights'])->name('hrmpsb.twg_weights.store');
            });

            // HRMPSB interview evaluation (scoring): SA, IA, Appointment Admin.
            // Module 4.1 — Appointment Encoder sessions never render the
            // HRMPSB Deliberation interface/voting portals; excluded here,
            // not just hidden in the sidebar (defense in depth).
            Route::middleware('role:super_admin,inventory_admin,appointment_admin')->group(function () {
                Route::get('/hrmpsb/interview/create', [\App\Http\Controllers\InterviewEvaluationController::class, 'create'])->name('hrmpsb.interview.create');
                Route::post('/hrmpsb/interview', [\App\Http\Controllers\InterviewEvaluationController::class, 'store'])->name('hrmpsb.interview.store');
                Route::post('/hrmpsb/interview/copy', [\App\Http\Controllers\InterviewEvaluationController::class, 'copyEvaluation'])->name('hrmpsb.interview.copy');
                Route::get('/hrmpsb/interview/{applicant_id}/matrix', [\App\Http\Controllers\InterviewEvaluationController::class, 'matrix'])->name('hrmpsb.interview.matrix');
                Route::get('/hrmpsb/interview/{applicant_id}/detail-report', [\App\Http\Controllers\InterviewEvaluationController::class, 'detailReport'])->name('hrmpsb.interview.detail_report');
            });

            // TWG scoring: SA, IA, Appointment Admin only.
            // Module 4.1 — the TWG Detailed Scoring Matrix is hidden/unrendered
            // for Appointment Encoder sessions.
            Route::middleware('role:super_admin,inventory_admin,appointment_admin')->group(function () {
                Route::get('/hrmpsb-twg', [\App\Http\Controllers\HrmpsbScoreController::class, 'create'])->name('hrmpsb.twg.create');
                Route::post('/hrmpsb-twg', [\App\Http\Controllers\HrmpsbScoreController::class, 'store'])->name('hrmpsb.twg.store');
                Route::post('/hrmpsb-twg/copy-score', [\App\Http\Controllers\HrmpsbScoreController::class, 'copyScore'])->name('hrmpsb.twg.copy_score');
                Route::get('/hrmpsb/applicant/{id}/psb-score', [\App\Http\Controllers\HrmpsbScoreController::class, 'psbScoreJson'])->name('hrmpsb.psb_score_json');
                Route::post('/{id}/hrmpsb-score', [\App\Http\Controllers\HrmpsbScoreController::class, 'saveScore'])->name('hrmpsb.save_score');
            });

            // Module 6.1 — dynamic, criterion-driven TWG scoring engine (additive
            // alongside hrmpsb-twg above). Same AE exclusion as Module 4.1.
            Route::middleware('role:super_admin,inventory_admin,appointment_admin')->group(function () {
                Route::get('/hrmpsb-twg-dynamic', [\App\Http\Controllers\TwgDynamicScoreController::class, 'create'])->name('hrmpsb.twg_dynamic.create');
                Route::post('/hrmpsb-twg-dynamic/{applicant}', [\App\Http\Controllers\TwgDynamicScoreController::class, 'store'])->name('hrmpsb.twg_dynamic.store');
                Route::post('/hrmpsb-twg-dynamic/{applicant}/submit', [\App\Http\Controllers\TwgDynamicScoreController::class, 'submit'])->name('hrmpsb.twg_dynamic.submit');
                Route::post('/hrmpsb-twg-dynamic/{applicant}/unlock', [\App\Http\Controllers\TwgDynamicScoreController::class, 'unlock'])->name('hrmpsb.twg_dynamic.unlock');
            });

            // Module 5 — split-screen deliberation workspace. Same AE exclusion.
            Route::middleware('role:super_admin,inventory_admin,appointment_admin')->group(function () {
                // Agenda Prep
                Route::get('/deliberation/agenda/create', [\App\Http\Controllers\DeliberationAgendaController::class, 'create'])->name('deliberation.agenda.create');
                Route::post('/deliberation/agenda/store', [\App\Http\Controllers\DeliberationAgendaController::class, 'store'])->name('deliberation.agenda.store');

                Route::get('/deliberation/{applicant}', [\App\Http\Controllers\DeliberationController::class, 'show'])->name('deliberation.show');
                Route::post('/deliberation/{applicant}/phase', [\App\Http\Controllers\DeliberationController::class, 'updatePhase'])->name('deliberation.phase');
                Route::get('/deliberation/{applicant}/monitoring', [\App\Http\Controllers\DeliberationMonitoringController::class, 'status'])->name('deliberation.monitoring');
                
                // Module 8 Exports
                Route::get('/deliberation/{applicant}/export/layout-a', [\App\Http\Controllers\DeliberationExportController::class, 'layoutA'])->name('deliberation.export.layout-a');
                Route::get('/deliberation/{applicant}/export/cer', [\App\Http\Controllers\DeliberationExportController::class, 'exportCer'])->name('deliberation.export.cer');
                Route::get('/deliberation/{applicant}/export/layout-c', [\App\Http\Controllers\DeliberationExportController::class, 'layoutC'])->name('deliberation.export.layout-c');
                Route::get('/deliberation/export/layout-d/{position}', [\App\Http\Controllers\DeliberationExportController::class, 'layoutD'])->name('deliberation.export.layout-d');

                // Module 7 Photo Management
                Route::post('/deliberation/{applicant}/photo/upload', [\App\Http\Controllers\ApplicantPhotoController::class, 'upload'])->name('deliberation.photo.upload');
                Route::post('/deliberation/{applicant}/photo/import', [\App\Http\Controllers\ApplicantPhotoController::class, 'import'])->name('deliberation.photo.import');
                Route::post('/deliberation/{applicant}/photo/confirm', [\App\Http\Controllers\ApplicantPhotoController::class, 'confirm'])->name('deliberation.photo.confirm');
            });

            // Module 5.4 — examination routing engine. Same AE exclusion.
            Route::middleware('role:super_admin,inventory_admin,appointment_admin')->group(function () {
                Route::get('/exam-routing', [\App\Http\Controllers\ExamRoutingController::class, 'index'])->name('exam-routing.index');
                Route::post('/exam-routing/generate', [\App\Http\Controllers\ExamRoutingController::class, 'generate'])->name('exam-routing.generate');
                Route::post('/exam-routing/{applicant}/toggle-exempt', [\App\Http\Controllers\ExamRoutingController::class, 'toggleExempt'])->name('exam-routing.toggle-exempt');
            });

            // Shared View Routes – SA, IA, Appointment Admin, Appointment Encoder
            Route::middleware('role:super_admin,inventory_admin,appointment_admin,appointment_encoder')->group(function () {
                Route::get('/{id}', [\App\Http\Controllers\RecruitmentController::class, 'show'])->name('show');
                Route::get('/{id}/receipt', [\App\Http\Controllers\RecruitmentController::class, 'printReceipt'])->name('receipt');
                Route::post('/{id}/sync-ipcr', [\App\Http\Controllers\RecruitmentController::class, 'syncIpcr'])->name('sync-ipcr');
            });
        });


    // ── GAD Analytics Engine (super_admin, inventory_admin, viewer) ──────
    // Module 4A RBAC cutover — live-DB check confirmed "Personnel Records"
    // (inventory_admin), "Viewer", "Appointment", and "Welfare & Benefits"
    // all already hold "view GAD Analytics"; both routes here are GET/read
    // (index + CSV export), so this group converts cleanly with no
    // exclusions (super_admin still passes everything via the Gate::before
    // bypass in AppServiceProvider regardless of the permission check).
    Route::prefix('gad')->name('gad.')->middleware('permission:view GAD Analytics')->group(function () {
        Route::get('/', [GadAnalyticsController::class, 'index'])->name('index');
        Route::get('/export/outstanding-csv', [GadAnalyticsController::class, 'exportOutstandingCsv'])->name('outstanding-csv');
    });

    // ── Backup & Recovery (super_admin only) ─────────────────────────────
    Route::prefix('backup')->name('backup.')->middleware('role:super_admin')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'create'])->name('create');
        Route::get('/{backup}/download', [BackupController::class, 'download'])->name('download');
        Route::get('/{backup}/restore', [BackupController::class, 'restoreConfirm'])->name('restore-confirm');
        Route::post('/{backup}/restore', [BackupController::class, 'restore'])->name('restore');
        Route::delete('/{backup}/file', [BackupController::class, 'deleteFile'])->name('delete-file');
    });

    // ── Profile (all roles) ───────────────────────────────────────────────
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // ── Panel Member Management (super_admin / inventory_admin only) ────────
    Route::prefix('panel-members')->name('panel-members.')->middleware('role:super_admin,inventory_admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\PanelMemberController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\PanelMemberController::class, 'store'])->name('store');
        Route::patch('/{panelMember}/toggle-active', [\App\Http\Controllers\PanelMemberController::class, 'toggleActive'])->name('toggle-active');
        Route::patch('/{panelMember}/reset-password', [\App\Http\Controllers\PanelMemberController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{panelMember}', [\App\Http\Controllers\PanelMemberController::class, 'destroy'])->name('destroy');
    });

    // ── Forced first-login password change ───────────────────────────────
    Route::get('/change-password', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/change-password', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])->name('password.force-change.update');

    // ── Learning & Development / Training (Module 10B) ──────────────────────
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TrainingController::class, 'index'])
            ->middleware('permission:view Training & Certificates')->name('index');
        Route::post('/', [\App\Http\Controllers\TrainingController::class, 'store'])
            ->middleware('permission:add Training & Certificates')->name('store');
        Route::get('/{training}', [\App\Http\Controllers\TrainingController::class, 'show'])
            ->middleware('permission:view Training & Certificates')->name('show');
        Route::post('/{training}/participants', [\App\Http\Controllers\TrainingController::class, 'addParticipant'])
            ->middleware('permission:edit Training & Certificates')->name('participants.store');
        Route::post('/{training}/certificates/batch', [\App\Http\Controllers\TrainingController::class, 'issueBatchCertificates'])
            ->middleware('permission:edit Training & Certificates')->name('certificates.batch');
        Route::post('/participants/{participant}/certificate', [\App\Http\Controllers\TrainingController::class, 'issueCertificate'])
            ->middleware('permission:edit Training & Certificates')->name('participants.certificate');
        Route::get('/history/{plantilla}', [\App\Http\Controllers\TrainingController::class, 'history'])
            ->middleware('permission:view Training & Certificates')->name('history');
    });

    // ── MFA for RACCS-Confidential access (Module 9 Stage 5 / 10-M3) ────────
    Route::prefix('mfa')->name('mfa.')->group(function () {
        Route::get('/setup', [\App\Http\Controllers\MfaController::class, 'setup'])->name('setup');
        Route::post('/enable', [\App\Http\Controllers\MfaController::class, 'enable'])->name('enable');
        Route::get('/challenge', [\App\Http\Controllers\MfaController::class, 'challenge'])->name('challenge');
        Route::post('/verify', [\App\Http\Controllers\MfaController::class, 'verify'])->name('verify');
    });

    // ── RACCS Disciplinary Cases (Module 10-M3) ─────────────────────────────
    Route::prefix('disciplinary')->name('disciplinary.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DisciplinaryCaseController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\DisciplinaryCaseController::class, 'store'])->name('store');
        Route::get('/{disciplinary}', [\App\Http\Controllers\DisciplinaryCaseController::class, 'show'])->name('show');
        Route::post('/{disciplinary}/status', [\App\Http\Controllers\DisciplinaryCaseController::class, 'updateStatus'])->name('update-status');
    });

    // ── Incident Reports (Module 3A) ────────────────────────────────────────
    Route::prefix('incidents')->name('incidents.')->group(function () {
        Route::get('/', [\App\Http\Controllers\IncidentReportController::class, 'index'])
            ->middleware('permission:view Incident Reports')->name('index');
        Route::post('/', [\App\Http\Controllers\IncidentReportController::class, 'store'])
            ->middleware('permission:add Incident Reports')->name('store');
        Route::get('/{incident}', [\App\Http\Controllers\IncidentReportController::class, 'show'])
            ->middleware('permission:view Incident Reports')->name('show');
        Route::post('/{incident}/advisory', [\App\Http\Controllers\IncidentReportController::class, 'generateAdvisory'])
            ->name('advisory');
        Route::post('/{incident}/finalize', [\App\Http\Controllers\IncidentReportController::class, 'finalize'])
            ->name('finalize');
    });

    // ── LGU Documents & Service Requests (Module 10-M5) ────────────────────
    Route::prefix('lgu')->name('lgu.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LguDocumentController::class, 'index'])
            ->middleware('permission:view LGU Documents')
            ->name('index');
        Route::post('/documents', [\App\Http\Controllers\LguDocumentController::class, 'storeDocument'])
            ->middleware('permission:add LGU Documents')
            ->name('documents.store');
        Route::post('/requests', [\App\Http\Controllers\LguDocumentController::class, 'storeRequest'])
            ->middleware('permission:add LGU Documents')
            ->name('requests.store');
        Route::post('/requests/{serviceRequest}/complete', [\App\Http\Controllers\LguDocumentController::class, 'completeRequest'])
            ->middleware('permission:edit LGU Documents')
            ->name('requests.complete');
    });

    // ── Leave (Module 10-M2) ────────────────────────────────────────────────
    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LeaveController::class, 'index'])
            ->middleware('permission:view Leave Application')
            ->name('index');
        Route::post('/', [\App\Http\Controllers\LeaveController::class, 'store'])
            ->middleware('permission:add Leave Application')
            ->name('store');
        Route::post('/{leaveApplication}/approve', [\App\Http\Controllers\LeaveController::class, 'approve'])
            ->middleware('permission:edit Leave Application')
            ->name('approve');
        Route::post('/{leaveApplication}/disapprove', [\App\Http\Controllers\LeaveController::class, 'disapprove'])
            ->middleware('permission:edit Leave Application')
            ->name('disapprove');
        Route::get('/balances/{plantilla}', [\App\Http\Controllers\LeaveController::class, 'balances'])
            ->middleware('permission:view Leave Application')
            ->name('balances');
    });

    // ── Leave Violation Monitoring & Formal Letters (Module 2B) ───────────────
    Route::prefix('leave-violations')->name('leave-violations.')->group(function () {
        // Redirect old index to new recorded-entries
        Route::redirect('/', '/leave-violations/recorded-entries')->name('index');
        
        // LWOP & Recorded Entries
        Route::get('/recorded-entries', [\App\Http\Controllers\LeaveViolationController::class, 'recordedEntries'])
            ->middleware('permission:view Leave Violations')->name('recorded-entries');
        Route::get('/recorded-entries/create', [\App\Http\Controllers\LeaveViolationController::class, 'createRecord'])
            ->middleware('permission:add Leave Violations')->name('recorded-entries.create');
        Route::post('/recorded-entries', [\App\Http\Controllers\LeaveViolationController::class, 'storeRecord'])
            ->middleware('permission:add Leave Violations')->name('recorded-entries.store');
        Route::post('/recorded-entries/generate-report', [\App\Http\Controllers\LeaveViolationController::class, 'generateReportPdf'])
            ->middleware('permission:view Leave Violations')->name('recorded-entries.generate-report');
        Route::get('/recorded-entries/export-pdf', [\App\Http\Controllers\LeaveViolationController::class, 'exportRecordedEntriesPdf'])
            ->middleware('permission:view Leave Violations')->name('recorded-entries.export-pdf');
        Route::post('/scan/{plantilla}', [\App\Http\Controllers\LeaveViolationController::class, 'scan'])
            ->name('scan');
        Route::post('/{violation}/resolve', [\App\Http\Controllers\LeaveViolationController::class, 'markResolved'])
            ->name('resolve');
        Route::post('/{violation}/issue-notice', [\App\Http\Controllers\LeaveViolationController::class, 'issueNotice'])
            ->name('issue-notice');
        Route::get('/{violation}/pdf', [\App\Http\Controllers\LeaveViolationController::class, 'downloadPdf'])
            ->name('download-pdf');
            
        // Generated Letters
        Route::get('/generated-letters', [\App\Http\Controllers\LeaveViolationController::class, 'generatedLetters'])
            ->middleware('permission:view Leave Violations')->name('generated-letters');
        Route::get('/letters/create-tardy', [\App\Http\Controllers\LeaveViolationController::class, 'createTardy'])
            ->middleware('permission:add Leave Violations')->name('create-tardy');
        Route::get('/letters/create-undertime', [\App\Http\Controllers\LeaveViolationController::class, 'createUndertime'])
            ->middleware('permission:add Leave Violations')->name('create-undertime');
        Route::post('/letters/store', [\App\Http\Controllers\LeaveViolationController::class, 'storeLetter'])
            ->middleware('permission:add Leave Violations')->name('store-letter');
        Route::get('/letters/create-reprimand', [\App\Http\Controllers\LeaveViolationController::class, 'createReprimand'])
            ->middleware('permission:add Leave Violations')->name('create-reprimand');
        Route::post('/letters/store-reprimand', [\App\Http\Controllers\LeaveViolationController::class, 'storeReprimand'])
            ->middleware('permission:add Leave Violations')->name('store-reprimand');
        Route::get('/letters/{violation}/edit', [\App\Http\Controllers\LeaveViolationController::class, 'editLetter'])
            ->middleware('permission:edit Leave Violations')->name('edit-letter');
        Route::put('/letters/{violation}', [\App\Http\Controllers\LeaveViolationController::class, 'updateLetter'])
            ->middleware('permission:edit Leave Violations')->name('update-letter');
        Route::delete('/letters/{violation}', [\App\Http\Controllers\LeaveViolationController::class, 'destroyLetter'])
            ->middleware('permission:edit Leave Violations')->name('destroy-letter');
    });

    // ── System Activity Logs ────────────────────────────────────────────────
    Route::get('/activity-logs', function () {
        $logs = \App\Models\ActivityLog::with('user')->latest()->paginate(50);
        return view('activity-logs.index', compact('logs'));
    })->middleware('auth')->name('activity-logs.index');

    // ── Records Retention & Disposal Matrix (Module 1B.5) ──────────────────
    Route::prefix('retention-schedule')->name('retention-schedule.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RetentionScheduleController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\RetentionScheduleController::class, 'store'])->name('store');
        Route::put('/{retentionSchedule}', [\App\Http\Controllers\RetentionScheduleController::class, 'update'])->name('update');
        Route::get('/export/csv', [\App\Http\Controllers\RetentionScheduleController::class, 'exportCsv'])->name('export-csv');
    });

    // ── IDCC — Intelligent Document Capture & Classification (Module 9/9A) ─
    Route::prefix('idcc')->name('idcc.')->group(function () {
        Route::get('/', [\App\Http\Controllers\IdccController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\IdccController::class, 'store'])
            ->middleware('permission:view Document Ingestion & Capture')
            ->name('store');
        // Literal segments must be registered before the {document} wildcard below.
        Route::get('/print', [\App\Http\Controllers\IdccController::class, 'print'])->name('print');
        Route::post('/bulk-delete', [\App\Http\Controllers\IdccController::class, 'bulkDelete'])
            ->middleware('permission:delete Document Ingestion & Capture')
            ->name('bulk-delete');
        Route::post('/bulk-rotate', [\App\Http\Controllers\IdccController::class, 'bulkRotate'])
            ->middleware('permission:edit Document Ingestion & Capture')
            ->name('bulk-rotate');
        Route::get('/{document}', [\App\Http\Controllers\IdccController::class, 'show'])->name('show');
        Route::get('/{document}/download', [\App\Http\Controllers\IdccController::class, 'download'])->name('download');
        Route::get('/{document}/preview', [\App\Http\Controllers\IdccController::class, 'preview'])->name('preview');
        Route::post('/{document}/relate', [\App\Http\Controllers\IdccController::class, 'relate'])->name('relate');
        Route::post('/{document}/reprocess', [\App\Http\Controllers\IdccController::class, 'reprocess'])
            ->middleware('permission:view Document Ingestion & Capture')
            ->name('reprocess');
        Route::patch('/{document}/keyword', [\App\Http\Controllers\IdccController::class, 'updateKeyword'])
            ->name('update-keyword');
    });
});

// ============================================
// Authentication Routes
// ============================================
require __DIR__ . '/auth.php';

// ============================================
// Panel Portal (HRMPSB / TWG Evaluators)
// ============================================
use App\Http\Controllers\Panel\PanelAuthController;
use App\Http\Controllers\Panel\PanelPortalController;

Route::prefix('panel')->name('panel.')->group(function () {
    // Public panel login
    Route::get('/login', [PanelAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PanelAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [PanelAuthController::class, 'logout'])->name('logout');

    // Protected panel portal
    Route::middleware('auth:panel')->group(function () {
        Route::get('/', [PanelPortalController::class, 'home'])->name('home');
        Route::get('/interview', [PanelPortalController::class, 'interviewForm'])->name('interview.form');
        Route::post('/interview', [PanelPortalController::class, 'interviewStore'])->name('interview.store');
        Route::get('/twg', [PanelPortalController::class, 'twgForm'])->name('twg.form');
        Route::post('/twg', [PanelPortalController::class, 'twgStore'])->name('twg.store');
    });
});

// Dev-only convenience login — blocked in production
if (app()->isLocal()) {
    Route::get('/auto-login', function () {
        Auth::loginUsingId(1);
        return redirect('/dashboard');
    });
}

