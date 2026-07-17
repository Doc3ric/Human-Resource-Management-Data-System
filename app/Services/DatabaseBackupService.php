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
            'backup_scope'   => 'database',
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
            } elseif ($driver === 's3') {
                $stream = Storage::disk('local')->readStream($storagePath);
                Storage::disk('s3')->writeStream($filename, $stream);
                if (is_resource($stream)) fclose($stream);
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
            if (isset($storagePath) && Storage::disk('local')->exists($storagePath)) {
                Storage::disk('local')->delete($storagePath);
            }

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

    // Full snapshot of the file-storage components not covered by the database
    // dump: IDCC documents/RACCS attachments and generated reports. Unlike
    // run(), this streams the encryption chunk-by-chunk rather than holding
    // the whole archive in memory — IDCC storage is already 780MB+ and only
    // grows, and this server has 7.7GB total RAM, so an in-memory encrypt
    // (which briefly needs several multiples of the file size) risks an OOM
    // that can affect the whole box, not just this command.
    public function runFiles(string $type, ?int $createdBy, string $driver = 'local'): BackupLog
    {
        $filename = 'hrmds_files_' . date('Ymd_His') . '_' . Str::random(6) . '.zip.enc';

        $log = BackupLog::create([
            'filename'       => $filename,
            'type'           => $type,
            'backup_scope'   => 'files',
            'status'         => 'pending',
            'storage_driver' => $driver,
            'encrypted'      => true,
            'created_by'     => $createdBy,
        ]);

        try {
            $tempZip = storage_path('app/temp_files_' . time() . '.zip');
            $this->generateFileArchive($tempZip);

            $storagePath = 'backups/' . $filename;
            Storage::disk('local')->makeDirectory('backups');
            $absoluteOutputPath = Storage::disk('local')->path($storagePath);
            $this->streamEncryptFile($tempZip, $absoluteOutputPath);

            @unlink($tempZip);

            $size = Storage::disk('local')->size($storagePath);

            if ($driver === 'google_drive') {
                $this->uploadToGoogleDrive($storagePath, $filename);
            } elseif ($driver === 's3') {
                $stream = Storage::disk('local')->readStream($storagePath);
                Storage::disk('s3')->writeStream($filename, $stream);
                if (is_resource($stream)) fclose($stream);
            }

            DB::table('backup_logs')->where('id', $log->id)->update([
                'status'       => 'success',
                'size_bytes'   => $size,
                'storage_path' => $storagePath,
            ]);

            activity('backup')
                ->causedBy($createdBy ? User::find($createdBy) : null)
                ->withProperties(['filename' => $filename, 'type' => $type, 'size_bytes' => $size])
                ->log('File-storage backup created');

        } catch (\Throwable $e) {
            if (isset($storagePath) && Storage::disk('local')->exists($storagePath)) {
                Storage::disk('local')->delete($storagePath);
            }

            DB::table('backup_logs')->where('id', $log->id)->update([
                'status' => 'failed',
                'notes'  => substr($e->getMessage(), 0, 500),
            ]);

            activity('backup')
                ->causedBy($createdBy ? User::find($createdBy) : null)
                ->withProperties(['filename' => $filename, 'type' => $type, 'error' => $e->getMessage()])
                ->log('File-storage backup FAILED');

            throw $e;
        }

        return $log->fresh();
    }

    // Directories not covered by the database dump: IDCC/RACCS document
    // uploads and generated report PDFs. Zipped as-is (no re-encryption per
    // file) since the whole archive is encrypted afterward in run()/runFiles().
    private function generateFileArchive(string $zipPath): void
    {
        $sources = [
            'idcc'    => storage_path('app/private/idcc'),
            'reports' => storage_path('app/reports'),
        ];

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create file-storage archive.');
        }

        foreach ($sources as $prefix => $source) {
            if (!is_dir($source)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $relative = $prefix . '/' . str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
                $zip->addFile($file->getPathname(), $relative);
            }
        }

        $zip->close();
    }

    // Keeps the $keep most recent successful backup files for the given scope;
    // older ones have their file removed from disk but the log row is
    // preserved (INSERT-only table). Database and file-storage backups are
    // retained independently since file archives are much larger.
    public function pruneOldBackups(int $keep, string $scope = 'database'): int
    {
        $old = BackupLog::where('status', 'success')
            ->where('storage_driver', 'local')
            ->where('backup_scope', $scope)
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

    // Streaming counterpart to encryptData()/decryptData() used only for the
    // (large, ever-growing) file-storage archive — see runFiles() for why.
    // Format: IV(16) + AES-256-CBC ciphertext (streamed in 16-byte-aligned
    // chunks, manually chaining blocks the way CBC requires across calls) +
    // HMAC-SHA256(32) computed over IV+ciphertext (encrypt-then-MAC, since
    // GCM's authentication tag can't be computed incrementally this way).
    private function streamEncryptFile(string $inputPath, string $outputPath): void
    {
        $key = $this->encryptionKey();
        $iv = random_bytes(16);

        $in = fopen($inputPath, 'rb');
        $out = fopen($outputPath, 'wb');
        if (!$in || !$out) {
            throw new \RuntimeException('Could not open files for streaming encryption.');
        }

        fwrite($out, $iv);
        $hmacCtx = hash_init('sha256', HASH_HMAC, $key);
        hash_update($hmacCtx, $iv);

        $chunkSize = 4 * 1024 * 1024; // multiple of the 16-byte AES block size
        $prevBlock = $iv;
        $buffer = '';

        while (!feof($in)) {
            $buffer .= fread($in, $chunkSize);

            if (feof($in)) {
                // Final chunk gets normal PKCS7 padding.
                $cipher = openssl_encrypt($buffer, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $prevBlock);
                fwrite($out, $cipher);
                hash_update($hmacCtx, $cipher);
                break;
            }

            $alignedLen = intdiv(strlen($buffer), 16) * 16;
            if ($alignedLen === 0) {
                continue;
            }
            $toEncrypt = substr($buffer, 0, $alignedLen);
            $buffer = substr($buffer, $alignedLen);
            $cipher = openssl_encrypt($toEncrypt, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $prevBlock);
            fwrite($out, $cipher);
            hash_update($hmacCtx, $cipher);
            $prevBlock = substr($cipher, -16);
        }

        fclose($in);
        fwrite($out, hash_final($hmacCtx, true));
        fclose($out);
    }

    private function streamDecryptFile(string $inputPath, string $outputPath): void
    {
        $key = $this->encryptionKey();
        $size = filesize($inputPath);
        if ($size === false || $size < 16 + 32) {
            throw new \RuntimeException('File is too small to be a valid encrypted archive.');
        }

        $in = fopen($inputPath, 'rb');
        $iv = fread($in, 16);
        $cipherLen = $size - 16 - 32;

        $hmacCtx = hash_init('sha256', HASH_HMAC, $key);
        hash_update($hmacCtx, $iv);

        $out = fopen($outputPath, 'wb');
        $prevBlock = $iv;
        $remaining = $cipherLen;
        $chunkSize = 4 * 1024 * 1024;

        while ($remaining > 0) {
            $chunk = fread($in, min($chunkSize, $remaining));
            hash_update($hmacCtx, $chunk);
            $remaining -= strlen($chunk);
            $isLast = $remaining === 0;

            $plain = $isLast
                ? openssl_decrypt($chunk, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $prevBlock)
                : openssl_decrypt($chunk, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $prevBlock);
            if ($plain === false) {
                fclose($in);
                fclose($out);
                @unlink($outputPath);
                throw new \RuntimeException('Decryption failed — key mismatch or corrupt file.');
            }
            fwrite($out, $plain);
            if (!$isLast) {
                $prevBlock = substr($chunk, -16);
            }
        }

        $storedHmac = fread($in, 32);
        fclose($in);
        fclose($out);

        // Verified after the fact rather than before decrypting each chunk — an
        // acceptable tradeoff here since this only ever runs as a manual,
        // super-admin-triggered restore of the app's own backups, not as a
        // decryption oracle exposed to untrusted input.
        if (!hash_equals(hash_final($hmacCtx, true), $storedHmac)) {
            @unlink($outputPath);
            throw new \RuntimeException('Integrity check failed — the archive may be corrupted or tampered with.');
        }
    }

    // Reverses generateFileArchive(): decrypts the zip, extracts it to a
    // staging directory, then copies each top-level folder back to its real
    // on-disk location (idcc/ and reports/ don't share a common parent, so
    // the whole zip can't be extracted straight into one target).
    public function restoreFileArchive(string $storagePath): void
    {
        $absoluteInputPath = Storage::disk('local')->path($storagePath);
        $tempZip = storage_path('app/temp_restore_' . time() . '.zip');
        $this->streamDecryptFile($absoluteInputPath, $tempZip);

        $tempExtract = storage_path('app/temp_restore_extract_' . time());
        @mkdir($tempExtract, 0755, true);

        $zip = new \ZipArchive();
        if ($zip->open($tempZip) !== true) {
            @unlink($tempZip);
            \Illuminate\Support\Facades\File::deleteDirectory($tempExtract);
            throw new \RuntimeException('Could not open file-storage archive for restore.');
        }
        $zip->extractTo($tempExtract);
        $zip->close();
        @unlink($tempZip);

        $targets = [
            'idcc'    => storage_path('app/private/idcc'),
            'reports' => storage_path('app/reports'),
        ];
        foreach ($targets as $prefix => $target) {
            $source = $tempExtract . DIRECTORY_SEPARATOR . $prefix;
            if (!is_dir($source)) {
                continue;
            }
            @mkdir($target, 0755, true);
            \Illuminate\Support\Facades\File::copyDirectory($source, $target);
        }

        \Illuminate\Support\Facades\File::deleteDirectory($tempExtract);
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
        try {
            $driveService->files->create($fileMetadata, [
                'data'       => $content,
                'mimeType'   => 'application/octet-stream',
                'uploadType' => 'multipart',
                // Required for the folder ID to resolve inside a Shared Drive — a bare
                // service account has no storage quota of its own (Google API limitation),
                // so GOOGLE_DRIVE_FOLDER_ID must point into a Shared Drive the service
                // account has been added to, not a personal "My Drive" folder.
                'supportsAllDrives' => true,
            ]);
        } catch (\Google\Service\Exception $e) {
            $errorData = json_decode($e->getMessage(), true);
            $msg = $errorData['error']['message'] ?? $e->getMessage();
            throw new \RuntimeException('Google Drive Error: ' . $msg, $e->getCode(), $e);
        }
    }
}
