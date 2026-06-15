<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\StepIncrementController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\UserController;
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

// ============================================
// Authenticated Routes
// ============================================
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

    // ── Inventory of Personnel (inventory_admin + super_admin) ────────────
    Route::middleware('role:super_admin,inventory_admin')->group(function () {
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
        Route::post('plantilla/{plantilla}/attachments', [EmployeeAttachmentController::class, 'store'])->name('plantilla.attachments.store');
        Route::get('plantilla/attachments/{attachment}/download', [EmployeeAttachmentController::class, 'download'])->name('plantilla.attachments.download');
        Route::get('plantilla/attachments/{attachment}/view', [EmployeeAttachmentController::class, 'view'])->name('plantilla.attachments.view');
        Route::delete('plantilla/attachments/{attachment}', [EmployeeAttachmentController::class, 'destroy'])->name('plantilla.attachments.destroy');
        
        // Promotion Module
        Route::get('plantilla/{plantilla}/promote', [PlantillaController::class, 'promoteForm'])->name('plantilla.promote.form');
        Route::post('plantilla/{plantilla}/promote', [PlantillaController::class, 'promoteSubmit'])->name('plantilla.promote.submit');

        // Quick Add Office/Position
        Route::get('plantilla/quick-add/office-position', [PlantillaController::class, 'quickAddForm'])->name('plantilla.quick-add.form');
        Route::post('plantilla/quick-add/office-position', [PlantillaController::class, 'quickAddSubmit'])->name('plantilla.quick-add.submit');

        Route::get('plantilla/item-details', [PlantillaController::class, 'getItemDetails'])->name('plantilla.item-details');
        Route::resource('plantilla', PlantillaController::class);
    });

    // ── Job Order Inventory (inventory_admin + super_admin) ───────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->prefix('job-orders')
        ->name('job-orders.')
        ->group(function () {
            Route::get('/export/pdf', [JobOrderController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [JobOrderController::class, 'exportExcel'])->name('export.excel');
            Route::get('/template', [JobOrderController::class, 'downloadTemplate'])->name('template');
            Route::get('/import-history', [JobOrderController::class, 'importHistory'])->name('import.history');
            Route::delete('/import-history/{id}/undo', [JobOrderController::class, 'undoImport'])->name('import.undo');
            Route::post('/import', [JobOrderController::class, 'importExcel'])->name('import');
            Route::delete('/delete-all', [JobOrderController::class, 'deleteAll'])->middleware('role:super_admin')->name('delete-all');
            // JO document attachments
            Route::post('/{jobOrder}/attachments', [EmployeeAttachmentController::class, 'storeForJobOrder'])->name('attachments.store');
            Route::get('/attachments/{attachment}/download', [EmployeeAttachmentController::class, 'download'])->name('attachments.download');
            Route::get('/attachments/{attachment}/view', [EmployeeAttachmentController::class, 'view'])->name('attachments.view');
            Route::delete('/attachments/{attachment}', [EmployeeAttachmentController::class, 'destroy'])->name('attachments.destroy');
            Route::get('/', [JobOrderController::class, 'index'])->name('index');
            Route::get('/create', [JobOrderController::class, 'create'])->name('create');
            Route::post('/', [JobOrderController::class, 'store'])->name('store');
            Route::get('/{jobOrder}/edit', [JobOrderController::class, 'edit'])->name('edit');
            Route::put('/{jobOrder}', [JobOrderController::class, 'update'])->name('update');
            Route::delete('/{jobOrder}', [JobOrderController::class, 'destroy'])->name('destroy');
        });

    // ── Casual Employees Inventory (inventory_admin + super_admin) ───────────
    Route::middleware('role:super_admin,inventory_admin')
        ->prefix('casual')
        ->name('casual.')
        ->group(function () {
            Route::get('/export/pdf', [CasualController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [CasualController::class, 'exportExcel'])->name('export.excel');
            Route::get('/template', [CasualController::class, 'downloadTemplate'])->name('template');
            Route::get('/import-history', [CasualController::class, 'importHistory'])->name('import.history');
            Route::delete('/import-history/{id}/undo', [CasualController::class, 'undoImport'])->name('import.undo');
            Route::post('/import', [CasualController::class, 'importExcel'])->name('import');
            Route::delete('/delete-all', [CasualController::class, 'deleteAll'])->name('delete-all');
            // Casual document attachments
            Route::post('/{casual}/attachments', [EmployeeAttachmentController::class, 'storeForCasual'])->name('attachments.store');
            Route::get('/attachments/{attachment}/download', [EmployeeAttachmentController::class, 'download'])->name('attachments.download');
            Route::get('/attachments/{attachment}/view', [EmployeeAttachmentController::class, 'view'])->name('attachments.view');
            Route::delete('/attachments/{attachment}', [EmployeeAttachmentController::class, 'destroy'])->name('attachments.destroy');
            Route::get('/', [CasualController::class, 'index'])->name('index');
            Route::get('/create', [CasualController::class, 'create'])->name('create');
            Route::post('/', [CasualController::class, 'store'])->name('store');
            Route::get('/{casual}/edit', [CasualController::class, 'edit'])->name('edit');
            Route::put('/{casual}', [CasualController::class, 'update'])->name('update');
            Route::delete('/{casual}', [CasualController::class, 'destroy'])->name('destroy');
        });

    // ── Permanent Employees (inventory_admin + super_admin) ───────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->prefix('permanent')
        ->name('permanent.')
        ->group(function () {
            Route::get('/export/excel', [PermanentController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf',   [PermanentController::class, 'exportPdf'])->name('export.pdf');
            Route::delete('/delete-all', [PermanentController::class, 'deleteAll'])->middleware('role:super_admin')->name('delete-all');
            Route::get('/', [PermanentController::class, 'index'])->name('index');
        });

    // ── Retirement Management (inventory_admin + super_admin) ──────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->prefix('retirement')
        ->name('retirement.')
        ->group(function () {
            Route::get('/', [RetirementController::class, 'index'])->name('index');
            Route::get('/history', [RetirementController::class, 'history'])->name('history');
            Route::put('/{plantilla}/history', [RetirementController::class, 'updateHistory'])->name('update-history');
            Route::get('/export/pdf', [RetirementController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [RetirementController::class, 'exportExcel'])->name('export.excel');
            Route::post('/process-all', [RetirementController::class, 'processAll'])->name('process-all');
            Route::post('/{plantilla}', [RetirementController::class, 'process'])->name('process');
        });

    // ── All Data – flat table view (inventory_admin + super_admin) ────────
    Route::middleware('role:super_admin,inventory_admin')
        ->prefix('all-data')
        ->name('all-data.')
        ->group(function () {
            Route::post('/sync-salaries', [AllDataController::class, 'syncSalaries'])->name('sync-salaries');
            Route::get('/salary/{grade}/{step}', [AllDataController::class, 'getSalary'])->name('salary');
            Route::get('/items-by-office', [AllDataController::class, 'getItemsByOffice'])->name('items-by-office');
            Route::get('/', [AllDataController::class, 'index'])->name('index');
            Route::get('/create', [AllDataController::class, 'create'])->name('create');
            Route::post('/', [AllDataController::class, 'store'])->name('store');
            Route::get('/export/excel', [AllDataController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [AllDataController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/{allDatum}/edit', [AllDataController::class, 'edit'])->name('edit');
            Route::put('/{allDatum}', [AllDataController::class, 'update'])->name('update');
            Route::delete('/{allDatum}', [AllDataController::class, 'destroy'])->name('destroy');
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

            // ── Loyalty Incentive Background Settings ──────────────────────────
            Route::get('/loyalty-incentive-settings',         [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'index'])->name('loyalty-incentive-settings.index');
            Route::post('/loyalty-incentive-settings/upload', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'upload'])->name('loyalty-incentive-settings.upload');
            Route::post('/loyalty-incentive-settings/{id}/set-active', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'setActive'])->name('loyalty-incentive-settings.set-active');
            Route::post('/loyalty-incentive-settings/set-none', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'setNone'])->name('loyalty-incentive-settings.set-none');
            Route::delete('/loyalty-incentive-settings/{id}', [\App\Http\Controllers\LoyaltyIncentiveSettingController::class, 'destroy'])->name('loyalty-incentive-settings.destroy');
            Route::post('/{plantilla}/dismiss-loyalty', [StepIncrementController::class, 'dismissLoyalty'])->name('dismiss-loyalty');
            Route::post('/{plantilla}/restore-loyalty', [StepIncrementController::class, 'restoreLoyalty'])->name('restore-loyalty');
        });

    // ------------- Salary Grade Management (salary_admin + super_admin) ----------
    Route::middleware('role:super_admin,salary_admin')
        ->prefix('salary-grades')
        ->name('salary-grades.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\SalaryGradeController::class, 'index'])->name('index');
            Route::post('/update-all', [\App\Http\Controllers\SalaryGradeController::class, 'updateAll'])->name('update-all');
        });

    // ------------- Salary Schedule (SSL Tranche) Management (salary_admin + super_admin) ----------
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
            // New: download error rows discovered during the preview stage (before executing)
            Route::get('/preview-errors', [ImportController::class, 'exportPreviewErrors'])->name('preview-errors');
            // New: return to the column-mapping step with the already-uploaded file
            Route::post('/back-to-map', [ImportController::class, 'backToMap'])->name('back-to-map');
            Route::post('/read-headers', [ImportController::class, 'readHeaders'])->name('read-headers');
            Route::post('/map', [ImportController::class, 'map'])->name('map');
            Route::post('/preview', [ImportController::class, 'preview'])->name('preview');
            Route::post('/execute', [ImportController::class, 'execute'])
                ->middleware('throttle:10,1')  // max 10 import executions per minute
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

    // ── Archive a full plantilla slot (from all-data page) ───────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->post('all-data/{allDatum}/archive', [ArchiveController::class, 'archivePlantilla'])
        ->name('all-data.archive');

    // ── Archive a Job Order record ─────────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->post('job-orders/{jobOrder}/archive', [ArchiveController::class, 'archiveJobOrder'])
        ->name('job-orders.archive');

    // ── Archive a Casual Employee record ───────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->post('casual/{casual}/archive', [ArchiveController::class, 'archiveCasual'])
        ->name('casual.archive');

    // ── Archive a Permanent Employee record ────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->post('permanent/{record}/archive', [ArchiveController::class, 'archivePermanent'])
        ->name('permanent.archive');

    // ── Bulk Archive routes ────────────────────────────────────────────────
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

    // ── Global Search ──────────────────────────────────────────────────────
    Route::get('/search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])
        ->name('search');

    // ── Employee 201 Profile ───────────────────────────────────────────────
    Route::middleware('role:super_admin,inventory_admin')
        ->get('employees/{type}/{id}/profile', [\App\Http\Controllers\EmployeeProfileController::class, 'show'])
        ->name('employees.profile');
    Route::middleware('role:super_admin,inventory_admin')
        ->post('employees/{type}/{id}/profile/picture', [\App\Http\Controllers\EmployeeProfileController::class, 'updatePicture'])
        ->name('employees.profile.picture');
    Route::middleware('role:super_admin,inventory_admin')
        ->delete('employees/{type}/{id}/profile/picture', [\App\Http\Controllers\EmployeeProfileController::class, 'removePicture'])
        ->name('employees.profile.picture.remove');


    // ── Profile (all roles) ───────────────────────────────────────────────
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

// ============================================
// Authentication Routes
// ============================================
require __DIR__ . '/auth.php';

Route::get('/auto-login', function () {
    Auth::loginUsingId(1);
    return redirect('/dashboard'); });
