<?php

namespace App\Support\Idcc;

/**
 * AES-256-GCM at rest for SPI/RACCS documents, keyed from APP_KEY —
 * the same proven pattern already used by BackupController for backups.
 */
class DocumentEncryptor
{
    private function key(): string
    {
        return base64_decode(str_replace('base64:', '', config('app.key')));
    }

    public function encrypt(string $plaintext): string
    {
        $key = $this->key();
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);

        return base64_encode($iv . $tag . $cipher);
    }

    public function decrypt(string $encoded): string
    {
        $raw = base64_decode($encoded);
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($plain === false) {
            throw new \RuntimeException('Decryption failed — key mismatch or corrupt file.');
        }

        return $plain;
    }
}
