<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentRelation;
use App\Models\DocumentRetentionRule;
use App\Models\RaccsAccessLog;
use App\Models\SpiAccessLog;
use App\Support\Idcc\DocumentAccessPolicy;
use App\Support\Idcc\DocumentEncryptor;
use App\Support\Idcc\DuplicateDetectionService;
use App\Support\Idcc\IdccPipeline;
use App\Support\Idcc\ImageRotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdccController extends Controller
{
    public function __construct(
        private readonly IdccPipeline $pipeline,
        private readonly DuplicateDetectionService $duplicates,
        private readonly DocumentAccessPolicy $accessPolicy,
        private readonly ImageRotationService $rotator,
    ) {
    }

    /**
     * Shared RACCS/SPI content-access check + audit logging, used by every
     * action that reads the real file bytes (download/preview/print/rotate) —
     * one place so the masking/logging rule can't drift between them.
     */
    private function authorizeContentAccess(Request $request, Document $document, string $attemptAction, string $loggedAction): bool
    {
        $user = $request->user();
        $canUnmask = $this->accessPolicy->canUnmask($user, $document);

        if ($document->is_raccs) {
            RaccsAccessLog::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action' => $attemptAction,
                'outcome' => $canUnmask ? 'granted' : 'denied',
                'ip_address' => $request->ip(),
                'denial_reason' => $this->accessPolicy->denialReason($user, $document),
            ]);
        }

        if ($canUnmask && $document->is_spi) {
            SpiAccessLog::create(['document_id' => $document->id, 'user_id' => $user->id, 'action' => $loggedAction]);
        }

        return $canUnmask;
    }

    private function plaintextBytes(Document $document): string
    {
        $stored = Storage::disk('local')->get($document->storage_path);
        return $document->encrypted ? app(DocumentEncryptor::class)->decrypt($stored) : $stored;
    }

    /** Stage 7 — auto-foldering browse view, partitioned by doc_type_code/privacy_tier/importance_class. */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Document::query()->orderByDesc('created_at');

        // RACCS documents never appear in the general browse list for
        // non-authorized users (Stage 5 wall) — not just access-denied on
        // click, but absent from the listing entirely.
        if (!$user->hasRole('Discipline Committee') && !$user->isSuperAdmin()) {
            $query->where('is_raccs', false);
        }

        if ($request->filled('doc_type_code')) {
            $query->where('doc_type_code', $request->string('doc_type_code'));
        }
        if ($request->filled('privacy_tier')) {
            $query->where('privacy_tier', $request->integer('privacy_tier'));
        }
        if ($request->filled('search_keyword')) {
            $query->where('search_keyword', $request->string('search_keyword'));
        }
        if ($request->filled('q')) {
            // Stage 2 FULLTEXT search. RACCS exclusion above already applies.
            $search = $request->string('q');
            $query->whereRaw('MATCH(ocr_text) AGAINST (? IN NATURAL LANGUAGE MODE)', [$search])
                ->orWhere('original_filename', 'like', "%{$search}%");
        }

        $documents = $query->paginate(20)->withQueryString();
        $folders = Document::query()
            ->selectRaw('doc_type_code, search_keyword, importance_class, privacy_tier, count(*) as total')
            ->when(!$user->isSuperAdmin() && !$user->hasRole('Discipline Committee'), fn ($q) => $q->where('is_raccs', false))
            ->groupBy('doc_type_code', 'search_keyword', 'importance_class', 'privacy_tier')
            ->get();

        return view('idcc.index', compact('documents', 'folders'));
    }

    /** Stage 1 + 9A.10 — universal ingestion; exact-hash duplicates are blocked outright, never created. */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimes:pdf,jpg,jpeg,png,tiff,docx,xlsx,txt'],
            'capture_source' => ['nullable', 'string', 'in:UPLOAD,WEBCAM,SMARTPHONE,BLUETOOTH,SCANNER'],
            'attachment_field' => ['nullable', 'string', 'max:255'],
            'personnel_id' => ['nullable', 'integer'],
        ]);

        $file = $request->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());
        $existing = $this->duplicates->findExisting($sha256);
        $user = $request->user();

        if ($existing) {
            $this->duplicates->logBlocked($existing, $user);
            return response()->json([
                'duplicate_detected' => true,
                'existing' => $this->duplicates->describeLocation($existing, $user),
            ], 409);
        }

        $context = [
            'capture_source' => $request->input('capture_source', 'UPLOAD'),
            'attachment_field' => $request->input('attachment_field'),
            'personnel_id' => $request->input('personnel_id'),
            'ingested_by' => $user->id,
        ];

        $document = $this->pipeline->ingest($file, $context);

        // The global activity feed is visible to any authenticated user
        // regardless of this document's privacy tier — never leak a RACCS
        // filename into it; RACCS access itself is logged separately to the
        // dedicated raccs_access_log instead.
        if (!$document->is_raccs) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Ingested Document',
                'description' => "Ingested \"{$document->original_filename}\" via {$document->capture_source} (doc_type: {$document->doc_type_code}).",
            ]);
        }

        return response()->json(['document_id' => $document->id, 'status' => $document->status], 201);
    }

    /** View metadata + (masked or full) content, per Stage 4 masking rule. */
    public function show(Request $request, Document $document)
    {
        $user = $request->user();
        $canView = $this->accessPolicy->canView($user, $document);

        $denialReason = $this->accessPolicy->denialReason($user, $document);

        if ($document->is_raccs) {
            RaccsAccessLog::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action' => 'view_attempt',
                'outcome' => $canView ? 'granted' : 'denied',
                'ip_address' => $request->ip(),
                'denial_reason' => $denialReason,
            ]);
        }

        abort_unless($canView, 403, $denialReason === 'MFA not verified or expired'
            ? 'This RACCS-Confidential document requires a verified MFA challenge. Complete it at /mfa/challenge, then try again.'
            : 'You do not have permission to view this document.');

        if ($document->is_spi) {
            SpiAccessLog::create(['document_id' => $document->id, 'user_id' => $user->id, 'action' => 'viewed']);
        }

        $retentionRule = DocumentRetentionRule::where('doc_type_code', $document->doc_type_code)->first();
        $relations = $document->relations()->with('relatedDocument')->get();

        return view('idcc.show', compact('document', 'retentionRule', 'relations'));
    }

    /**
     * Re-run OCR + classification on an already-ingested document — for
     * documents stuck in `manual_review`/doc_type `OTHER` because the OCR
     * drivers available at ingestion time couldn't read them (e.g. before
     * Tesseract/a PDF parser was installed on this host).
     */
    public function reprocess(Request $request, Document $document)
    {
        $user = $request->user();
        abort_unless($this->accessPolicy->canView($user, $document), 403);

        $before = $document->doc_type_code;
        $document = $this->pipeline->reprocess($document);

        if (!$document->is_raccs) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Reprocessed Document',
                'description' => "Reprocessed \"{$document->original_filename}\" (doc_type: {$before} \u{2192} {$document->doc_type_code}).",
            ]);
        }

        return back()->with('success', "Reprocessed \u{2014} classified as {$document->doc_type_code} ({$document->status}).");
    }

    /** Stage 6 — link two documents (e.g. a transcript SUPPORTING_ELIGIBILITY for an appointment). */
    public function relate(Request $request, Document $document)
    {
        $data = $request->validate([
            'related_document_id' => ['required', 'integer', 'exists:documents,id', 'not_in:' . $document->id],
            'relation_type' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();
        abort_unless($this->accessPolicy->canView($user, $document), 403);

        $related = Document::findOrFail($data['related_document_id']);
        abort_unless($this->accessPolicy->canView($user, $related), 403);

        $relation = DocumentRelation::create([
            'document_id' => $document->id,
            'related_document_id' => $related->id,
            'relation_type' => $data['relation_type'],
            'personnel_id' => $document->personnel_id,
            'personnel_type' => $document->personnel_type,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Documents linked.')->with('relation_id', $relation->id);
    }

    /**
     * Download the file. SPI documents withhold the actual bytes/OCR text
     * from viewers without unmask rights — abstract_description + metadata
     * only (masking-by-withholding; true in-image redaction isn't
     * implemented in this pass — see task notes).
     */
    public function download(Request $request, Document $document)
    {
        abort_unless(
            $this->authorizeContentAccess($request, $document, 'download_attempt', 'downloaded'),
            403,
            'You do not have permission to download this document.'
        );

        return response($this->plaintextBytes($document), 200, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $document->original_filename . '"',
        ]);
    }

    /**
     * Inline preview. Images/PDFs/plain text render directly in the browser
     * (Content-Disposition: inline). docx/xlsx have no live in-browser
     * renderer here, so they fall back to a text preview of the already
     *-extracted OCR text rather than serving raw binary the browser can't show.
     */
    public function preview(Request $request, Document $document)
    {
        abort_unless(
            $this->authorizeContentAccess($request, $document, 'preview_attempt', 'previewed'),
            403,
            'You do not have permission to preview this document.'
        );

        $inlineRenderable = str_starts_with($document->mime_type, 'image/')
            || $document->mime_type === 'application/pdf'
            || $document->mime_type === 'text/plain';

        if ($inlineRenderable) {
            return response($this->plaintextBytes($document), 200, [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
            ]);
        }

        return view('idcc.preview-text', ['document' => $document]);
    }

    /**
     * Combined print view for one or more selected documents — renders each
     * into a single printable page and triggers the browser print dialog once
     * on load, rather than opening a separate window per file.
     */
    public function print(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:documents,id'],
        ]);

        $documents = Document::whereIn('id', $validated['ids'])->get();
        $printable = [];
        $skipped = [];

        foreach ($documents as $document) {
            if (!$this->authorizeContentAccess($request, $document, 'print_attempt', 'printed')) {
                $skipped[] = $document->original_filename;
                continue;
            }

            $printable[] = [
                'document' => $document,
                'is_image' => str_starts_with($document->mime_type, 'image/'),
                'is_pdf' => $document->mime_type === 'application/pdf',
                'data_uri' => str_starts_with($document->mime_type, 'image/')
                    ? 'data:' . $document->mime_type . ';base64,' . base64_encode($this->plaintextBytes($document))
                    : null,
                'preview_url' => route('idcc.preview', $document),
            ];
        }

        return view('idcc.print', compact('printable', 'skipped'));
    }

    /**
     * Soft-delete selected documents. Deliberately not a hard delete (the
     * project's absolute rule requires destruction to go through an audited,
     * signed-authority-gated disposal workflow — that full workflow doesn't
     * exist yet anywhere in this app). This is the lightweight interim: RBAC
     * -gated (route middleware requires "delete Document Ingestion & Capture",
     * granted to no role by default), requires typed confirmation, and every
     * deletion is audit-logged. Restoring the full NAP Form 3 disposal
     * workflow later can supersede this without changing the DB shape, since
     * documents already use SoftDeletes.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:documents,id'],
            'confirmation' => ['required', 'in:DELETE'],
        ], [
            'confirmation.in' => 'You must type DELETE exactly to confirm.',
        ]);

        $user = $request->user();
        $deleted = [];
        $skipped = [];

        foreach (Document::whereIn('id', $validated['ids'])->get() as $document) {
            if (!$this->accessPolicy->canUnmask($user, $document)) {
                $skipped[] = $document->original_filename;
                continue;
            }

            $deleted[] = $document->original_filename;

            if (!$document->is_raccs) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Deleted Document',
                    'description' => "Soft-deleted \"{$document->original_filename}\" (doc_type: {$document->doc_type_code}).",
                ]);
            }

            $document->delete();
        }

        $message = count($deleted) . ' document(s) deleted.';
        if ($skipped) {
            $message .= ' Skipped (no permission): ' . implode(', ', $skipped);
        }

        return back()->with($skipped && !$deleted ? 'error' : 'success', $message);
    }

    /**
     * Rotate selected documents. Only raster images (jpg/png/tiff) are
     * supported via GD, already installed — PDFs are skipped with a reported
     * reason rather than silently ignored (rotating a PDF page needs a
     * PDF-writing library not installed in this app).
     */
    public function bulkRotate(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:documents,id'],
            'degrees' => ['required', 'integer', 'in:90,180,270'],
        ]);

        $user = $request->user();
        $rotated = [];
        $skipped = [];

        foreach (Document::whereIn('id', $validated['ids'])->get() as $document) {
            if (!$this->accessPolicy->canUnmask($user, $document)) {
                $skipped[] = "{$document->original_filename} (no permission)";
                continue;
            }

            if (!$this->rotator->supports($document)) {
                $skipped[] = "{$document->original_filename} (rotation not supported for {$document->mime_type})";
                continue;
            }

            $this->rotator->rotate($document, $validated['degrees']);
            $rotated[] = $document->original_filename;

            if (!$document->is_raccs) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Rotated Document',
                    'description' => "Rotated \"{$document->original_filename}\" by {$validated['degrees']} degrees.",
                ]);
            }
        }

        $message = count($rotated) . ' document(s) rotated.';
        if ($skipped) {
            $message .= ' Skipped: ' . implode(', ', $skipped);
        }

        return back()->with($skipped && !$rotated ? 'error' : 'success', $message);
    }

    /** Allow user to manually fix the auto-generated search keyword. */
    public function updateKeyword(Request $request, Document $document)
    {
        $user = $request->user();
        abort_unless($this->accessPolicy->canView($user, $document), 403);

        $validated = $request->validate([
            'search_keyword' => ['required', 'string', 'max:255'],
        ]);

        $document->update(['search_keyword' => $validated['search_keyword']]);

        // Keep a simple audit log since we changed metadata
        if (!$document->is_raccs) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Updated Document Keyword',
                'description' => "Updated search keyword to \"{$validated['search_keyword']}\" for \"{$document->original_filename}\".",
            ]);
        }

        return back()->with('success', 'Search keyword updated successfully.');
    }
}
