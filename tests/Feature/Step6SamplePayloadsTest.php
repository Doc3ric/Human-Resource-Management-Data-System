<?php

/**
 * HRDMS_PHASE0_FOUNDATION.txt, Step 6 — the four representative validation
 * payloads. (C) and (D) are already exercised thoroughly by
 * IdccPipelineTest (RACCS-forbidden + logged access, and full PDS-212-style
 * ingestion with ocr_confidence/doc_type_code/privacy_tier/abstract/
 * relations/foldering) — not duplicated here. This file covers (A) and (B)
 * specifically, scoped to what Phase 0 actually built:
 *
 * (A) The spec asks for "an appointment with QS deficiency flagging R.A.
 *     7160 Appendix C-1 + PNPKI pending." The PNPKI-pending, non-blocking
 *     e-signature half is real, built, reusable (App\Support\Raccs\
 *     ESignatureService, already polymorphic) — tested below against an
 *     Applicant record. The "QS deficiency flagging against Appendix C-1"
 *     half has no backing schema or validator yet (no is_qualified /
 *     position_level column, no qualification-standards service anywhere
 *     in app/) — that's real Recruitment/Appointment feature work
 *     (Module 2/4/8 per BUILD_ORDER.md), not a Phase 0 schema/pipeline
 *     concern, and isn't fabricated here.
 * (B) "A 201 asset (33-A) PERMANENT retention" — confirmed directly
 *     against the seeded retention rule, in addition to the incidental
 *     UI-level check already in IdccPipelineTest.
 */

use App\Models\Applicant;
use App\Models\DocumentRetentionRule;
use App\Models\User;
use App\Support\Raccs\ESignatureService;
use Database\Seeders\IdccRetentionRulesSeeder;

test('(A) an e-signature captured on an appointment-track record leaves PNPKI pending and non-blocking', function () {
    $applicant = Applicant::factory()->create();
    $signedBy = User::factory()->create();

    $signature = app(ESignatureService::class)->capture(
        signable: $applicant,
        signatoryName: 'HRMO Division Head',
        signatoryPosition: 'Division Head',
        signatureImageBase64: 'data:image/png;base64,iVBORw0KGgo=',
        signedBy: $signedBy,
    );

    // The signature is valid and recorded regardless of PNPKI verification —
    // per ESignatureService's own contract, nothing blocks on this field.
    expect($signature->pnpki_status)->toBe('not_submitted');
    expect($signature->signable_type)->toBe($applicant->getMorphClass());
    expect($signature->signable_id)->toBe($applicant->id);
    expect($signature->exists)->toBeTrue();
});

test('(B) the APPT-33A document type is seeded with PERMANENT retention, never disposable', function () {
    $this->seed(IdccRetentionRulesSeeder::class);

    $rule = DocumentRetentionRule::where('doc_type_code', 'APPT-33A')->first();

    expect($rule)->not->toBeNull();
    expect($rule->is_permanent)->toBeTrue();
    expect($rule->disposal_requires_ledger_verification)->toBeFalse();
});
