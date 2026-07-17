<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Packages .env (APP_KEY, DB credentials, etc.) and the Google Drive service
 * account key into a single file, encrypted with a password the admin
 * chooses here — deliberately NOT with APP_KEY, since the whole point of
 * this export is to survive the loss of APP_KEY itself. Meant to be copied
 * to a USB drive or other offline medium kept separately from the regular
 * (APP_KEY-encrypted) database/file backups: without this, those backups
 * are permanently unreadable if the live machine's disk is lost.
 */
class ExportEncryptionKey extends Command
{
    protected $signature = 'backup:export-key {--output= : Output file path (default: storage/app/private/key-exports/...)}';

    protected $description = 'Export .env and the Google Drive service account key into one password-protected file, for offline/USB storage';

    public function handle(): int
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            $this->error('.env not found at ' . $envPath);
            return self::FAILURE;
        }

        $password = $this->secret('Enter a passphrase to protect this key export (write it down somewhere safe — it is NOT recoverable if lost)');
        if (!$password) {
            $this->error('A passphrase is required.');
            return self::FAILURE;
        }
        $confirm = $this->secret('Confirm passphrase');
        if ($password !== $confirm) {
            $this->error('Passphrases did not match.');
            return self::FAILURE;
        }
        if (strlen($password) < 8) {
            $this->error('Use a passphrase of at least 8 characters.');
            return self::FAILURE;
        }

        $googleSaKeyPath = config('services.google_drive.service_account_path');
        $googleSaKeyContent = ($googleSaKeyPath && file_exists($googleSaKeyPath))
            ? file_get_contents($googleSaKeyPath)
            : null;

        $payload = json_encode([
            'exported_at'      => now()->toIso8601String(),
            'app_name'         => config('app.name'),
            'env_contents'     => file_get_contents($envPath),
            'google_sa_key'    => $googleSaKeyContent,
        ], JSON_UNESCAPED_SLASHES);

        $encrypted = $this->encryptWithPassword($payload, $password);

        $output = $this->option('output')
            ?? storage_path('app/private/key-exports/hrdms_key_export_' . date('Ymd_His') . '.enc');

        @mkdir(dirname($output), 0700, true);
        file_put_contents($output, $encrypted);

        $this->info("Key export written to: {$output}");
        $this->warn('This file is only useful together with the passphrase you just entered.');
        $this->line('');
        $this->line('<fg=yellow>Next steps (do this now, not later):</>');
        $this->line('  1. Copy this file to a USB drive or other medium NOT on this machine.');
        $this->line('  2. Store the passphrase separately from the file itself (e.g., written down in the office safe).');
        $this->line('  3. Re-run this command whenever .env or the Google service account key changes.');
        $this->line('  4. Optionally delete the local copy once safely copied elsewhere: ' . $output);

        return self::SUCCESS;
    }

    private function encryptWithPassword(string $plaintext, string $password): string
    {
        $salt = random_bytes(16);
        $key  = hash_pbkdf2('sha256', $password, $salt, 100000, 32, true);
        $iv   = random_bytes(12);
        $tag  = '';
        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);

        return base64_encode($salt . $iv . $tag . $cipher);
    }
}
