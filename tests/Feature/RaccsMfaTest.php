<?php

use App\Models\DisciplinaryCase;
use App\Models\PlantillaRecord;
use App\Models\User;
use App\Support\Raccs\RaccsMfaGate;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Discipline Committee', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);

    $this->authority = User::factory()->create();
    $this->authority->assignRole('Discipline Committee');

    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('Viewer');
});

test('a non-RACCS role is denied outright, regardless of MFA state', function () {
    $this->actingAs($this->viewer)->get(route('disciplinary.index'))->assertForbidden();
});

test('a RACCS-authorized role without a verified MFA challenge is redirected to the MFA challenge', function () {
    $response = $this->actingAs($this->authority)->get(route('disciplinary.index'));

    $response->assertRedirect(route('mfa.challenge'));
});

test('setting up MFA generates a secret and a valid code enables it', function () {
    $this->actingAs($this->authority)->get(route('mfa.setup'))->assertOk();

    $this->authority->refresh();
    expect($this->authority->google2fa_secret)->not->toBeNull();

    $validCode = Google2FA::getCurrentOtp($this->authority->google2fa_secret);

    $this->actingAs($this->authority)->post(route('mfa.enable'), ['one_time_password' => $validCode])
        ->assertRedirect(route('disciplinary.index'));

    expect($this->authority->fresh()->google2fa_enabled)->toBeTrue();
});

test('after a verified MFA challenge, a RACCS-authorized role can access the disciplinary registry', function () {
    $this->actingAs($this->authority)->get(route('mfa.setup'));
    $this->authority->refresh();
    $validCode = Google2FA::getCurrentOtp($this->authority->google2fa_secret);
    $this->actingAs($this->authority)->post(route('mfa.enable'), ['one_time_password' => $validCode]);

    $response = $this->actingAs($this->authority)->get(route('disciplinary.index'));
    $response->assertOk();
});

test('registering a disciplinary case requires MFA verification too, and is audit-logged', function () {
    $this->actingAs($this->authority)->get(route('mfa.setup'));
    $this->authority->refresh();
    $validCode = Google2FA::getCurrentOtp($this->authority->google2fa_secret);
    $this->actingAs($this->authority)->post(route('mfa.enable'), ['one_time_password' => $validCode]);

    $record = PlantillaRecord::factory()->create();

    $response = $this->actingAs($this->authority)->post(route('disciplinary.store'), [
        'personnel_id' => $record->id,
        'formal_charge' => 'Alleged grave misconduct.',
        'offense_classification' => 'grave',
    ]);

    $response->assertRedirect();
    expect(DisciplinaryCase::count())->toBe(1);
    expect(\App\Models\RaccsAccessLog::where('outcome', 'granted')->count())->toBeGreaterThan(0);
});

test('a super admin bypasses the RACCS role check but still needs MFA verification', function () {
    $admin = User::factory()->create();
    $admin->assignRole('System & Administration');

    $this->actingAs($admin)->get(route('disciplinary.index'))->assertRedirect(route('mfa.challenge'));
});
