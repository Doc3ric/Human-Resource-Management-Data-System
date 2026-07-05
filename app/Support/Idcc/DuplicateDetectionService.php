<?php

namespace App\Support\Idcc;

use App\Models\Document;
use App\Models\DocumentDuplicateResolution;
use App\Models\User;

/**
 * Module 9A.10 — exact-hash duplicate detection and resolution.
 */
class DuplicateDetectionService
{
    public function __construct(private readonly DocumentAccessPolicy $accessPolicy)
    {
    }

    public function findExisting(string $sha256): ?Document
    {
        return Document::where('sha256_hash', $sha256)->first();
    }

    /**
     * RBAC-aware description of where an existing document lives, for the
     * "This file has already been uploaded" notice.
     */
    public function describeLocation(Document $existing, User $viewer): array
    {
        if (!$this->accessPolicy->canView($viewer, $existing)) {
            return [
                'visible' => false,
                'message' => $this->accessPolicy->restrictedLocationMessage($existing),
            ];
        }

        return [
            'visible' => true,
            'document_id' => $existing->id,
            'attachment_field' => $existing->attachment_field,
            'doc_type_code' => $existing->doc_type_code,
            'ingested_at' => optional($existing->created_at)->toDateTimeString(),
            'ingested_by' => optional($existing->ingestedBy)->name,
        ];
    }

    public function logBlocked(Document $existing, User $attemptedBy): DocumentDuplicateResolution
    {
        return DocumentDuplicateResolution::create([
            'document_id' => $existing->id,
            'resolved_by' => $attemptedBy->id,
            'action' => 'blocked',
        ]);
    }
}
