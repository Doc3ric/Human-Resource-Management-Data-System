<?php

use App\Models\Document;
use App\Models\RaccsAccessLog;
use App\Models\SpiAccessLog;
use App\Models\User;
use App\Support\Raccs\RaccsMfaGate;
use Database\Seeders\IdccRetentionRulesSeeder;
use Database\Seeders\IdccRolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(IdccRolesSeeder::class);
    $this->seed(IdccRetentionRulesSeeder::class);

    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    // capture_use permission for a non-admin ingesting user.
    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view Document Ingestion & Capture', 'guard_name' => 'web']);
    $recordsRole = Role::where('name', 'Personnel Records')->first();
    $recordsRole->givePermissionTo('view Document Ingestion & Capture');

    $this->clerk = User::factory()->create();
    $this->clerk->assignRole('Personnel Records');
});

test('a plain text document is ingested, OCR-extracted natively, and classified', function () {
    $file = UploadedFile::fake()->createWithContent('memo.txt', 'This is a routine office memo about the holiday schedule.');

    $response = $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file]);

    $response->assertCreated();
    $document = Document::first();
    expect($document)->not->toBeNull();
    expect($document->ocr_status)->toBe('completed');
    expect($document->ocr_text)->toContain('holiday schedule');
    expect($document->extraction_tier)->toBe('TIER_1');
    expect($document->status)->toBe('classified');
});

test('an SPI-bearing document is encrypted at rest and flagged privacy tier 1', function () {
    $file = UploadedFile::fake()->createWithContent('info.txt', 'TIN: 123-456-789. Please process accordingly.');

    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();

    $document = Document::first();
    expect($document->is_spi)->toBeTrue();
    expect($document->encrypted)->toBeTrue();
    expect($document->privacy_tier)->toBe(1);

    // Stored bytes on disk must not be the plaintext (encrypted at rest).
    $stored = Storage::disk('local')->get($document->storage_path);
    expect($stored)->not->toContain('123-456-789');
});

test('a disciplinary document is classified as RACCS and hidden from the general browse list', function () {
    $file = UploadedFile::fake()->createWithContent('case.txt', 'Formal Charge - Administrative Case disciplinary complaint affidavit.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();

    $document = Document::first();
    expect($document->is_raccs)->toBeTrue();
    expect($document->doc_type_code)->toBe('DISC-RACCS');

    // Not visible in the clerk's browse listing.
    $response = $this->actingAs($this->clerk)->get(route('idcc.index'));
    $response->assertDontSee('case.txt');

    // Denied on direct access, and the denial is logged.
    $this->actingAs($this->clerk)->get(route('idcc.show', $document))->assertForbidden();
    expect(RaccsAccessLog::where('outcome', 'denied')->count())->toBe(1);
});

test('a RACCS-authorized role without a verified MFA challenge is denied the document, even with the correct role', function () {
    Role::firstOrCreate(['name' => 'Discipline Committee', 'guard_name' => 'web']);
    $authority = User::factory()->create();
    $authority->assignRole('Discipline Committee');

    $file = UploadedFile::fake()->createWithContent('case2b.txt', 'Formal Charge disciplinary administrative case.');
    $this->actingAs($this->admin)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::where('original_filename', 'case2b.txt')->first();

    $this->actingAs($authority)->get(route('idcc.show', $document))->assertForbidden();
    expect(RaccsAccessLog::where('document_id', $document->id)->where('outcome', 'denied')->where('denial_reason', 'MFA not verified or expired')->exists())->toBeTrue();
});

test('a RACCS-authorized role with a verified MFA challenge can view the disciplinary document and the access is logged as granted', function () {
    Role::firstOrCreate(['name' => 'Discipline Committee', 'guard_name' => 'web']);
    $authority = User::factory()->create();
    $authority->assignRole('Discipline Committee');

    $file = UploadedFile::fake()->createWithContent('case2.txt', 'Formal Charge disciplinary administrative case.');
    $this->actingAs($this->admin)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::where('original_filename', 'case2.txt')->first();

    $this->actingAs($authority)->get(route('mfa.setup'));
    $authority->refresh();
    $validCode = Google2FA::getCurrentOtp($authority->google2fa_secret);
    $this->actingAs($authority)->post(route('mfa.enable'), ['one_time_password' => $validCode]);

    $this->actingAs($authority)->get(route('idcc.show', $document))->assertOk();
    expect(RaccsAccessLog::where('document_id', $document->id)->where('outcome', 'granted')->exists())->toBeTrue();
});

test('super admin still needs a verified MFA challenge to view a RACCS document (the usual bypass does not extend here)', function () {
    $file = UploadedFile::fake()->createWithContent('case2c.txt', 'Formal Charge disciplinary administrative case.');
    $this->actingAs($this->admin)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::where('original_filename', 'case2c.txt')->first();

    $this->actingAs($this->admin)->get(route('idcc.show', $document))->assertForbidden();

    $this->actingAs($this->admin)->get(route('mfa.setup'));
    $this->admin->refresh();
    $validCode = Google2FA::getCurrentOtp($this->admin->google2fa_secret);
    $this->actingAs($this->admin)->post(route('mfa.enable'), ['one_time_password' => $validCode]);

    $this->actingAs($this->admin)->get(route('idcc.show', $document))->assertOk();
});

test('viewing an SPI document logs to spi_access_log', function () {
    $file = UploadedFile::fake()->createWithContent('spi.txt', 'SSS: 34-1234567-8 on file.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::first();

    $this->actingAs($this->admin)->get(route('idcc.show', $document))->assertOk();

    expect(SpiAccessLog::where('document_id', $document->id)->where('action', 'viewed')->exists())->toBeTrue();
});

test('uploading the exact same file twice is detected as a duplicate', function () {
    $file1 = UploadedFile::fake()->createWithContent('dup.txt', 'identical content');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file1])->assertCreated();

    $file2 = UploadedFile::fake()->createWithContent('dup-again.txt', 'identical content');
    $response = $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file2]);

    $response->assertStatus(409);
    $response->assertJson(['duplicate_detected' => true]);
    expect(Document::count())->toBe(1);
});

test('a duplicate is blocked outright even if a resolution field is forged in the request', function () {
    $file1 = UploadedFile::fake()->createWithContent('dup.txt', 'identical content');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file1])->assertCreated();

    // Simulate a client bypassing the UI and posting the old resolution fields directly —
    // the server must ignore them and still block, since duplication must never be possible.
    foreach (['use_existing', 'overwrite', 'keep_both'] as $forgedResolution) {
        $file2 = UploadedFile::fake()->createWithContent('dup-' . $forgedResolution . '.txt', 'identical content');
        $response = $this->actingAs($this->clerk)->post(route('idcc.store'), [
            'file' => $file2,
            'resolution' => $forgedResolution,
            'justification' => 'irrelevant',
        ]);
        $response->assertStatus(409);
    }

    expect(Document::count())->toBe(1);
});

test('a blocked duplicate attempt is logged to the audit trail', function () {
    $file1 = UploadedFile::fake()->createWithContent('dup.txt', 'identical content');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file1])->assertCreated();
    $original = Document::first();

    $file2 = UploadedFile::fake()->createWithContent('dup2.txt', 'identical content');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file2])->assertStatus(409);

    expect(\App\Models\DocumentDuplicateResolution::where('document_id', $original->id)->where('action', 'blocked')->exists())->toBeTrue();
});

test('two documents can be linked (Stage 6 relationship mapping) and retention rule shows on the detail page', function () {
    $file1 = UploadedFile::fake()->createWithContent('appointment.txt', 'Notice of Appointment CSC Form 33 details.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file1])->assertCreated();
    $doc1 = Document::first();

    $file2 = UploadedFile::fake()->createWithContent('transcript.txt', 'Academic transcript of records.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file2])->assertCreated();
    $doc2 = Document::latest('id')->first();

    $this->actingAs($this->clerk)->post(route('idcc.relate', $doc1), [
        'related_document_id' => $doc2->id,
        'relation_type' => 'SUPPORTING_ELIGIBILITY',
    ])->assertRedirect();

    expect($doc1->relations()->count())->toBe(1);

    $response = $this->actingAs($this->clerk)->get(route('idcc.show', $doc1));
    $response->assertOk();
    $response->assertSee('Permanent'); // APPT-33A retention rule
});

test('a user without the capture permission cannot ingest documents', function () {
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    $file = UploadedFile::fake()->createWithContent('blocked.txt', 'content');
    $this->actingAs($viewer)->post(route('idcc.store'), ['file' => $file])->assertForbidden();
});

test('a text-layer PDF is read by the PDF parser driver and classified, not sent to manual review', function () {
    $pdfBytes = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<p>Application for Leave. CSC Form No. 6. Employee is requesting vacation leave.</p>')
        ->output();
    $file = UploadedFile::fake()->createWithContent('leave.pdf', $pdfBytes);

    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();

    $document = Document::first();
    expect($document->ocr_status)->toBe('completed');
    expect($document->ocr_text)->toContain('vacation leave');
    expect($document->doc_type_code)->toBe('LEAVE-CSC6');
    expect($document->status)->toBe('classified');
});

test('a scanned photo of a document is read by Tesseract now that it is installed on this host', function () {
    skipUnlessTesseractInstalled();

    $image = imagecreatetruecolor(700, 200);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefilledrectangle($image, 0, 0, 700, 200, $white);
    $font = 'C:\\Windows\\Fonts\\arial.ttf';
    imagettftext($image, 28, 0, 20, 60, $black, $font, 'CERTIFICATE OF EMPLOYMENT');
    imagettftext($image, 28, 0, 20, 110, $black, $font, 'COE REQUEST FORM');

    $tmpPath = tempnam(sys_get_temp_dir(), 'idcc_test_').'.png';
    imagepng($image, $tmpPath);
    imagedestroy($image);

    $file = new UploadedFile($tmpPath, 'coe_photo.png', 'image/png', null, true);

    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    @unlink($tmpPath);

    $document = Document::first();
    expect($document->extraction_tier)->toBe('TIER_1');
    expect($document->ocr_status)->toBe('completed');
    expect(strtoupper($document->ocr_text ?? ''))->toContain('CERTIFICATE');
    expect($document->doc_type_code)->toBe('COE-REQ');
});

test('reprocessing an already-ingested document re-runs OCR and classification', function () {
    $file = UploadedFile::fake()->createWithContent('leave2.txt', 'Application for leave. CSC Form 6.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::first();
    expect($document->doc_type_code)->toBe('LEAVE-CSC6');

    // Simulate a document that had originally failed classification.
    $document->update(['doc_type_code' => 'OTHER', 'status' => 'manual_review', 'ocr_status' => 'failed', 'ocr_text' => null]);

    $response = $this->actingAs($this->clerk)->post(route('idcc.reprocess', $document));
    $response->assertRedirect();

    $document->refresh();
    expect($document->doc_type_code)->toBe('LEAVE-CSC6');
    expect($document->status)->toBe('classified');
});

test('reprocessing never decrypts a document that was already stored encrypted', function () {
    $file = UploadedFile::fake()->createWithContent('spi2.txt', 'TIN: 987-654-321.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::first();
    expect($document->encrypted)->toBeTrue();

    app(\App\Support\Idcc\IdccPipeline::class)->reprocess($document);

    $document->refresh();
    expect($document->encrypted)->toBeTrue();
    $stored = Storage::disk('local')->get($document->storage_path);
    expect($stored)->not->toContain('987-654-321');
});

test('a document without delete permission cannot be soft-deleted, and one with permission can', function () {
    $file = UploadedFile::fake()->createWithContent('todelete.txt', 'Nothing sensitive here.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::first();

    // clerk has no delete permission — route middleware blocks it.
    $this->actingAs($this->clerk)->post(route('idcc.bulk-delete'), [
        'ids' => [$document->id],
        'confirmation' => 'DELETE',
    ])->assertForbidden();
    expect(Document::find($document->id))->not->toBeNull();

    Permission::firstOrCreate(['name' => 'delete Document Ingestion & Capture', 'guard_name' => 'web']);
    $this->admin->givePermissionTo('delete Document Ingestion & Capture');

    $response = $this->actingAs($this->admin)->post(route('idcc.bulk-delete'), [
        'ids' => [$document->id],
        'confirmation' => 'DELETE',
    ]);
    $response->assertRedirect();

    expect(Document::find($document->id))->toBeNull(); // hidden from default query (soft-deleted)
    expect(Document::withTrashed()->find($document->id))->not->toBeNull(); // still in DB — not a hard delete
    expect(\App\Models\ActivityLog::where('action', 'Deleted Document')->exists())->toBeTrue();
});

test('bulk-delete requires the confirmation text to match exactly', function () {
    Permission::firstOrCreate(['name' => 'delete Document Ingestion & Capture', 'guard_name' => 'web']);
    $this->admin->givePermissionTo('delete Document Ingestion & Capture');

    $file = UploadedFile::fake()->createWithContent('todelete2.txt', 'Nothing sensitive here.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::first();

    $this->actingAs($this->admin)->post(route('idcc.bulk-delete'), [
        'ids' => [$document->id],
        'confirmation' => 'delete', // wrong case
    ])->assertSessionHasErrors('confirmation');

    expect(Document::find($document->id))->not->toBeNull();
});

test('rotating an image document changes its stored bytes, and unsupported types are skipped and reported', function () {
    Permission::firstOrCreate(['name' => 'edit Document Ingestion & Capture', 'guard_name' => 'web']);
    $this->admin->givePermissionTo('edit Document Ingestion & Capture');

    $image = UploadedFile::fake()->image('scan.jpg', 100, 60);
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $image])->assertCreated();
    $imageDoc = Document::where('original_filename', 'scan.jpg')->first();
    $originalBytes = Storage::disk('local')->get($imageDoc->storage_path);

    $text = UploadedFile::fake()->createWithContent('notes.txt', 'plain text, not rotatable');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $text])->assertCreated();
    $textDoc = Document::where('original_filename', 'notes.txt')->first();

    $response = $this->actingAs($this->admin)->post(route('idcc.bulk-rotate'), [
        'ids' => [$imageDoc->id, $textDoc->id],
        'degrees' => 90,
    ]);
    $response->assertRedirect();
    $response->assertSessionHas('success', function ($message) {
        return str_contains($message, '1 document(s) rotated') && str_contains($message, 'notes.txt');
    });

    $imageDoc->refresh();
    $rotatedBytes = Storage::disk('local')->get($imageDoc->storage_path);
    expect($rotatedBytes)->not->toBe($originalBytes);
});

test('previewing a document serves it inline; a RACCS document is blocked and the attempt is logged', function () {
    $file = UploadedFile::fake()->createWithContent('preview.txt', 'plain content for preview.');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file])->assertCreated();
    $document = Document::first();

    $response = $this->actingAs($this->clerk)->get(route('idcc.preview', $document));
    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toContain('inline');

    $raccsFile = UploadedFile::fake()->createWithContent('raccscase.txt', 'Formal Charge disciplinary administrative case.');
    $this->actingAs($this->admin)->post(route('idcc.store'), ['file' => $raccsFile])->assertCreated();
    $raccsDoc = Document::where('original_filename', 'raccscase.txt')->first();
    expect($raccsDoc->is_raccs)->toBeTrue();

    $this->actingAs($this->clerk)->get(route('idcc.preview', $raccsDoc))->assertForbidden();
    expect(RaccsAccessLog::where('document_id', $raccsDoc->id)->where('action', 'preview_attempt')->where('outcome', 'denied')->exists())->toBeTrue();
});

test('print combines authorized documents and reports skipped ones by name', function () {
    $file1 = UploadedFile::fake()->createWithContent('print1.txt', 'first document content');
    $this->actingAs($this->clerk)->post(route('idcc.store'), ['file' => $file1])->assertCreated();
    $doc1 = Document::where('original_filename', 'print1.txt')->first();

    $raccsFile = UploadedFile::fake()->createWithContent('printraccs.txt', 'Formal Charge disciplinary administrative case.');
    $this->actingAs($this->admin)->post(route('idcc.store'), ['file' => $raccsFile])->assertCreated();
    $raccsDoc = Document::where('original_filename', 'printraccs.txt')->first();

    $response = $this->actingAs($this->clerk)->get(route('idcc.print', ['ids' => [$doc1->id, $raccsDoc->id]]));
    $response->assertOk();
    $response->assertSee('print1.txt');
    $response->assertSee('printraccs.txt'); // named in the "skipped" notice, not rendered as content
    expect(RaccsAccessLog::where('document_id', $raccsDoc->id)->where('action', 'print_attempt')->where('outcome', 'denied')->exists())->toBeTrue();
});

function skipUnlessTesseractInstalled(): void
{
    if (!app(\App\Support\Idcc\TesseractOcrDriver::class)->isAvailable()) {
        test()->markTestSkipped('Tesseract is not installed/detectable on this host.');
    }
}
