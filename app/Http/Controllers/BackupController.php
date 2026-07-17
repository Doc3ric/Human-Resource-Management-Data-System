<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct(private DatabaseBackupService $backupService)
    {
    }

    // ── Index ──────────────────────────────────────────────────────────────
    public function index()
    {
        $logs = BackupLog::with('creator')->orderByDesc('created_at')->paginate(20);
        $driveConfigured = !empty(config('services.google_drive.service_account_path'));
        return view('backup.index', compact('logs', 'driveConfigured'));
    }

    // ── Create manual backup ───────────────────────────────────────────────
    public function create(Request $request)
    {
        // Auth: super_admin only (route middleware enforces this, double-check here)
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Administrator access required.');

        $driver = $request->input('driver', 'local');
        $scope = $request->input('scope', 'database');

        try {
            $log = $scope === 'files'
                ? $this->backupService->runFiles('manual', Auth::id(), $driver)
                : $this->backupService->run('manual', Auth::id(), $driver);
            return back()->with('success', "Backup created successfully: {$log->filename} (" . $log->formatted_size . ')');
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    // ── Download a backup file ─────────────────────────────────────────────
    public function download(BackupLog $backup)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        abort_unless($backup->storage_driver === 'local' && $backup->storage_path, 404);
        abort_unless(Storage::disk('local')->exists($backup->storage_path), 404);

        activity('backup')
            ->causedBy(Auth::user())
            ->withProperties(['filename' => $backup->filename])
            ->log('Downloaded backup file');

        return Storage::disk('local')->download($backup->storage_path, $backup->filename);
    }

    // ── Restore: show confirmation page ───────────────────────────────────
    public function restoreConfirm(BackupLog $backup)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        return view('backup.restore-confirm', compact('backup'));
    }

    // ── Restore: execute ───────────────────────────────────────────────────
    public function restore(Request $request, BackupLog $backup)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'confirmation' => ['required', 'in:CONFIRM'],
        ], [
            'confirmation.in' => 'You must type CONFIRM exactly to proceed.',
        ]);

        // Log the attempt BEFORE executing (requirement: log regardless of outcome)
        activity('backup')
            ->causedBy(Auth::user())
            ->withProperties(['backup_id' => $backup->id, 'filename' => $backup->filename])
            ->log('Restore initiated');

        DB::table('backup_logs')->insert([
            'filename'       => $backup->filename,
            'type'           => 'restore',
            'status'         => 'pending',
            'storage_driver' => 'local',
            'encrypted'      => true,
            'notes'          => 'Restore from backup ID ' . $backup->id,
            'created_by'     => Auth::id(),
            'created_at'     => now(),
        ]);

        try {
            ini_set('memory_limit', '2048M'); // Temporarily increase memory limit for large restore

            if ($backup->backup_scope === 'files') {
                $this->backupService->restoreFileArchive($backup->storage_path);
            } else {
                $encrypted = Storage::disk('local')->get($backup->storage_path);
                $sql       = $this->backupService->decryptData($encrypted);
                unset($encrypted);

                // Execute SQL statements
                DB::unprepared($sql);
                unset($sql);
            }

            activity('backup')
                ->causedBy(Auth::user())
                ->withProperties(['backup_id' => $backup->id])
                ->log('Restore completed successfully');

            DB::table('backup_logs')->whereRaw('id = (SELECT MAX(id) FROM backup_logs WHERE type = "restore")')->update([
                'status' => 'restored',
                'notes'  => 'Restore from backup ID ' . $backup->id . ' completed.',
            ]);

            $restoredWhat = $backup->backup_scope === 'files' ? 'File storage (IDCC documents/reports)' : 'Database';
            return redirect()->route('backup.index')->with('success', "{$restoredWhat} restored from backup: " . $backup->filename);

        } catch (\Throwable $e) {
            activity('backup')
                ->causedBy(Auth::user())
                ->withProperties(['backup_id' => $backup->id, 'error' => $e->getMessage()])
                ->log('Restore FAILED');

            return redirect()->route('backup.index')->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    // ── Delete local backup file (NOT the log) ────────────────────────────
    public function deleteFile(BackupLog $backup)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
        if ($backup->storage_path && Storage::disk('local')->exists($backup->storage_path)) {
            Storage::disk('local')->delete($backup->storage_path);
        }
        DB::table('backup_logs')->where('id', $backup->id)->update(['storage_path' => null, 'status' => 'failed', 'notes' => 'File deleted by ' . Auth::user()->name]);
        return back()->with('success', 'Backup file deleted from server storage.');
    }

}
