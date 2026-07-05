<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseBackupService
{
    // Encryption uses APP_KEY (stored only in .env, never in DB or any file).
    // Method: AES-256-GCM via openssl_encrypt.

    private function encryptionKey(): string
    {
        return base64_decode(str_replace('base64:', '', config('app.key')));
    }

    public function run(string $type, ?int $createdBy, string $driver = 'local'): BackupLog
    {
        $filename = 'hrmds_backup_' . date('Ymd_His') . '_' . Str::random(6) . '.sql.enc';

        $log = BackupLog::create([
            'filename'       => $filename,
            'type'           => $type,
            'status'         => 'pending',
            'storage_driver' => $driver,
            'encrypted'      => true,
            'created_by'     => $createdBy,
        ]);

        try {
            ini_set('memory_limit', '2048M');

            $tempPath = storage_path('app/temp_backup_' . time() . '.sql');
            $this->generateSqlDumpFile($tempPath);

            $plaintext = file_get_contents($tempPath);
            $encrypted = $this->encryptData($plaintext);

            unset($plaintext);
            @unlink($tempPath);

            $storagePath = 'backups/' . $filename;
            Storage::disk('local')->put($storagePath, $encrypted);
            $size = Storage::disk('local')->size($storagePath);
            unset($encrypted);

            if ($driver === 'google_drive') {
                $this->uploadToGoogleDrive($storagePath, $filename);
            }

            DB::table('backup_logs')->where('id', $log->id)->update([
                'status'       => 'success',
                'size_bytes'   => $size,
                'storage_path' => $storagePath,
            ]);

            activity('backup')
                ->causedBy($createdBy ? User::find($createdBy) : null)
                ->withProperties(['filename' => $filename, 'type' => $type, 'size_bytes' => $size])
                ->log('Backup created');

        } catch (\Throwable $e) {
            DB::table('backup_logs')->where('id', $log->id)->update([
                'status' => 'failed',
                'notes'  => substr($e->getMessage(), 0, 500),
            ]);

            activity('backup')
                ->causedBy($createdBy ? User::find($createdBy) : null)
                ->withProperties(['filename' => $filename, 'type' => $type, 'error' => $e->getMessage()])
                ->log('Backup FAILED');

            throw $e;
        }

        return $log->fresh();
    }

    // Keeps the $keep most recent successful local backup files; older ones have
    // their file removed from disk but the log row is preserved (INSERT-only table).
    public function pruneOldBackups(int $keep): int
    {
        $old = BackupLog::where('status', 'success')
            ->where('storage_driver', 'local')
            ->whereNotNull('storage_path')
            ->orderByDesc('created_at')
            ->skip($keep)
            ->take(PHP_INT_MAX)
            ->get();

        $pruned = 0;
        foreach ($old as $backup) {
            if (Storage::disk('local')->exists($backup->storage_path)) {
                Storage::disk('local')->delete($backup->storage_path);
            }
            DB::table('backup_logs')->where('id', $backup->id)->update([
                'storage_path' => null,
                'status'       => 'failed',
                'notes'        => 'Auto-pruned by retention policy (keep last ' . $keep . ')',
            ]);
            $pruned++;
        }

        return $pruned;
    }

    public function decryptData(string $encoded): string
    {
        $raw    = base64_decode($encoded);
        $iv     = substr($raw, 0, 12);
        $tag    = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $key    = $this->encryptionKey();
        $plain  = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new \RuntimeException('Decryption failed — key mismatch or corrupt file.');
        }
        return $plain;
    }

    private function encryptData(string $plaintext): string
    {
        $key    = $this->encryptionKey();
        $iv     = random_bytes(12);
        $tag    = '';
        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        return base64_encode($iv . $tag . $cipher);
    }

    private function generateSqlDumpFile(string $filePath): void
    {
        $config = config('database.connections.mysql');
        $dsn    = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $pdo    = new \PDO($dsn, $config['username'], $config['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        $handle = fopen($filePath, 'w');
        fwrite($handle, "-- HRMDS Backup -- " . now() . " --\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $table) {
            $row = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n" . $row[1] . ";\n\n");

            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $cols = null;
            $chunk = [];
            while ($r = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if (!$cols) {
                    $cols = '`' . implode('`,`', array_keys($r)) . '`';
                }
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), $r);
                $chunk[] = '(' . implode(',', $vals) . ')';
                if (count($chunk) >= 100) {
                    fwrite($handle, "INSERT INTO `{$table}` ({$cols}) VALUES\n" . implode(",\n", $chunk) . ";\n");
                    $chunk = [];
                }
            }
            if ($chunk) {
                fwrite($handle, "INSERT INTO `{$table}` ({$cols}) VALUES\n" . implode(",\n", $chunk) . ";\n");
            }
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function uploadToGoogleDrive(string $localPath, string $filename): void
    {
        $keyPath  = config('services.google_drive.service_account_path');
        $folderId = config('services.google_drive.backup_folder_id');

        if (!$keyPath || !file_exists($keyPath)) {
            throw new \RuntimeException('Google Drive service account key not configured. Set GOOGLE_SA_KEY_PATH in .env');
        }

        $client = new \Google\Client();
        $client->setAuthConfig($keyPath);
        $client->addScope(\Google\Service\Drive::DRIVE_FILE);

        $driveService = new \Google\Service\Drive($client);
        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name'    => $filename,
            'parents' => [$folderId],
        ]);

        $content = Storage::disk('local')->get($localPath);
        $driveService->files->create($fileMetadata, [
            'data'       => $content,
            'mimeType'   => 'application/octet-stream',
            'uploadType' => 'multipart',
        ]);
    }
}
