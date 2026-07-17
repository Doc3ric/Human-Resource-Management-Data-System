<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Reverses ExportEncryptionKey — decrypts a key-export file back into
 * .env.restored and google-sa-key.restored.json. Deliberately does NOT
 * overwrite a live .env automatically: on a freshly provisioned machine
 * there usually isn't one yet, and on an existing machine you want to
 * review the restored values before activating them.
 */
class ImportEncryptionKey extends Command
{
    protected $signature = 'backup:import-key {path : Path to the .enc file created by backup:export-key} {--output-dir= : Directory to write the restored files into (default: current directory)}';

    protected $description = 'Decrypt a key export back into .env.restored and google-sa-key.restored.json';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $password = $this->secret('Enter the passphrase used to create this export');
        if (!$password) {
            $this->error('A passphrase is required.');
            return self::FAILURE;
        }

        try {
            $payload = json_decode($this->decryptWithPassword(file_get_contents($path), $password), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $this->error('Decryption failed — wrong passphrase, or the file is corrupt.');
            return self::FAILURE;
        }

        $outputDir = rtrim($this->option('output-dir') ?? getcwd(), '/\\');
        @mkdir($outputDir, 0700, true);

        $envOut = $outputDir . DIRECTORY_SEPARATOR . '.env.restored';
        file_put_contents($envOut, $payload['env_contents'] ?? '');
        $this->info("Restored: {$envOut}");

        if (!empty($payload['google_sa_key'])) {
            $keyOut = $outputDir . DIRECTORY_SEPARATOR . 'google-sa-key.restored.json';
            file_put_contents($keyOut, $payload['google_sa_key']);
            $this->info("Restored: {$keyOut}");
        }

        $this->line('');
        $this->line('<fg=yellow>This export was created: ' . ($payload['exported_at'] ?? 'unknown date') . '</>');
        $this->line('Review both files, then manually:');
        $this->line('  - rename .env.restored to .env (and update DB_HOST/paths for the new machine if needed)');
        $this->line('  - move google-sa-key.restored.json to the path your new .env\'s GOOGLE_SA_KEY_PATH points to');

        return self::SUCCESS;
    }

    private function decryptWithPassword(string $encoded, string $password): string
    {
        $raw    = base64_decode($encoded);
        $salt   = substr($raw, 0, 16);
        $iv     = substr($raw, 16, 12);
        $tag    = substr($raw, 28, 16);
        $cipher = substr($raw, 44);
        $key    = hash_pbkdf2('sha256', $password, $salt, 100000, 32, true);

        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new \RuntimeException('Decryption failed.');
        }
        return $plain;
    }
}
