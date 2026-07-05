<?php

namespace App\Support\Idcc;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

/**
 * Rotates raster image documents (jpg/png/tiff) in place via GD, which is
 * already installed. PDFs/docx/xlsx/txt are not supported — rotating a PDF
 * page needs a PDF-writing library (FPDI+TCPDF or similar), not installed
 * in this app; that's a real gap, not silently skipped (see supports()).
 */
class ImageRotationService
{
    private const SUPPORTED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/tiff'];

    public function __construct(private readonly DocumentEncryptor $encryptor)
    {
    }

    public function supports(Document $document): bool
    {
        return in_array($document->mime_type, self::SUPPORTED_MIME_TYPES, true);
    }

    /** $degrees is clockwise; GD's imagerotate() rotates counter-clockwise, so it's negated. */
    public function rotate(Document $document, int $degrees): void
    {
        if (!$this->supports($document)) {
            throw new \RuntimeException("Rotation is not supported for {$document->mime_type} documents.");
        }

        $stored = Storage::disk('local')->get($document->storage_path);
        $bytes = $document->encrypted ? $this->encryptor->decrypt($stored) : $stored;

        $image = imagecreatefromstring($bytes);
        if ($image === false) {
            throw new \RuntimeException('Could not decode image data for rotation.');
        }

        $rotated = imagerotate($image, -$degrees, 0);
        imagedestroy($image);

        // GD has no TIFF encoder, so a rotated TIFF is re-saved as PNG (lossless) —
        // mime_type must be updated to match, or the stored bytes and the
        // Content-Type served back to viewers would disagree.
        $newMimeType = $document->mime_type === 'image/tiff' ? 'image/png' : $document->mime_type;

        ob_start();
        match ($newMimeType) {
            'image/png' => imagepng($rotated),
            default => imagejpeg($rotated, null, 92),
        };
        $newBytes = ob_get_clean();
        imagedestroy($rotated);

        $toStore = $document->encrypted ? $this->encryptor->encrypt($newBytes) : $newBytes;
        Storage::disk('local')->put($document->storage_path, $toStore);

        $document->update(['size_bytes' => strlen($newBytes), 'mime_type' => $newMimeType]);
    }
}
