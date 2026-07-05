<?php

use App\Models\DisciplinaryCase;
use App\Models\ESignature;
use App\Models\PlantillaRecord;
use App\Models\User;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Discipline Committee', 'guard_name' => 'web']);
    $this->authority = User::factory()->create();
    $this->authority->assignRole('Discipline Committee');

    // Verify MFA so subsequent RACCS calls succeed.
    $this->actingAs($this->authority)->get(route('mfa.setup'));
    $this->authority->refresh();
    $code = Google2FA::getCurrentOtp($this->authority->google2fa_secret);
    $this->actingAs($this->authority)->post(route('mfa.enable'), ['one_time_password' => $code]);
});

test('marking a case as decided without a signature is rejected', function () {
    $record = PlantillaRecord::factory()->create();
    $case = DisciplinaryCase::create([
        'case_no' => 'RACCS-TEST-1', 'personnel_id' => $record->id,
        'formal_charge' => 'Test charge', 'offense_classification' => 'grave', 'status' => 'hearing',
    ]);

    $response = $this->actingAs($this->authority)->post(route('disciplinary.update-status', $case), [
        'status' => 'decided',
        'decision' => 'Found liable.',
    ]);

    $response->assertSessionHasErrors(['signature_image', 'signatory_name']);
    expect(ESignature::count())->toBe(0);
});

test('marking a case as decided with a signature captures it, with PNPKI left non-blocking', function () {
    $record = PlantillaRecord::factory()->create();
    $case = DisciplinaryCase::create([
        'case_no' => 'RACCS-TEST-2', 'personnel_id' => $record->id,
        'formal_charge' => 'Test charge', 'offense_classification' => 'grave', 'status' => 'hearing',
    ]);

    $response = $this->actingAs($this->authority)->post(route('disciplinary.update-status', $case), [
        'status' => 'decided',
        'decision' => 'Found liable.',
        'penalty' => 'Suspension 1 month.',
        'signatory_name' => 'Atty. Dela Cruz',
        'signatory_position' => 'Discipline Committee',
        'signature_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
    ]);

    $response->assertRedirect();
    $case->refresh();
    expect($case->status)->toBe('decided');

    $signature = ESignature::where('signable_type', DisciplinaryCase::class)->where('signable_id', $case->id)->first();
    expect($signature)->not->toBeNull();
    expect($signature->signatory_name)->toBe('Atty. Dela Cruz');
    expect($signature->pnpki_status)->toBe('not_submitted'); // never blocks
});

test('the e-signature is linked via the polymorphic relation on the case', function () {
    $record = PlantillaRecord::factory()->create();
    $case = DisciplinaryCase::create([
        'case_no' => 'RACCS-TEST-3', 'personnel_id' => $record->id,
        'formal_charge' => 'Test charge', 'offense_classification' => 'light', 'status' => 'hearing',
    ]);

    app(\App\Support\Raccs\ESignatureService::class)->capture(
        $case, 'Signatory Name', 'Position', 'data:image/png;base64,abc', $this->authority
    );

    $signature = ESignature::first();
    expect($signature->signable)->toBeInstanceOf(DisciplinaryCase::class);
    expect($signature->signable->id)->toBe($case->id);
});
