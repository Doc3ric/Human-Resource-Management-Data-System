<?php

use App\Models\User;
use App\Support\PasswordPolicy;

test('meetsGate rejects passwords missing complexity requirements', function () {
    expect(PasswordPolicy::meetsGate('short1!'))->toBeFalse(); // too short
    expect(PasswordPolicy::meetsGate('alllowercase1!'))->toBeFalse(); // no uppercase
    expect(PasswordPolicy::meetsGate('ALLUPPERCASE1!'))->toBeFalse(); // no lowercase
    expect(PasswordPolicy::meetsGate('NoDigitsHere!'))->toBeFalse(); // no number
    expect(PasswordPolicy::meetsGate('NoSymbolsHere1'))->toBeFalse(); // no symbol
});

test('meetsGate accepts a fully compliant password', function () {
    expect(PasswordPolicy::meetsGate('Str0ng!Passw0rd'))->toBeTrue();
});

test('registering with a weak password outside local env quarantines the account', function () {
    // The testing environment is not 'local', so the strict rule applies —
    // exactly like production would.
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'weakpass@example.com',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertDatabaseMissing('users', ['email' => 'weakpass@example.com']);
});

test('registering with a compliant password does not quarantine the account', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'username' => 'strongpassuser',
        'email' => 'strongpass@example.com',
        'password' => 'Str0ng!Passw0rd',
        'password_confirmation' => 'Str0ng!Passw0rd',
    ]);

    $user = User::where('email', 'strongpass@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->meets_complexity_gate)->toBeTrue();
    expect($user->must_change_password)->toBeFalse();
});

test('a quarantined user is redirected to the forced password-change screen for any page', function () {
    $user = User::factory()->create([
        'must_change_password' => true,
        'meets_complexity_gate' => false,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('password.force-change'));
});

test('completing the forced password change with a weak password is rejected', function () {
    $user = User::factory()->create([
        'must_change_password' => true,
        'meets_complexity_gate' => false,
    ]);

    $response = $this->actingAs($user)->post('/change-password', [
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    $response->assertSessionHasErrors('password');
    expect($user->fresh()->must_change_password)->toBeTrue();
});

test('completing the forced password change with a compliant password clears quarantine', function () {
    $user = User::factory()->create([
        'must_change_password' => true,
        'meets_complexity_gate' => false,
    ]);

    $response = $this->actingAs($user)->post('/change-password', [
        'password' => 'Str0ng!Passw0rd',
        'password_confirmation' => 'Str0ng!Passw0rd',
    ]);

    $response->assertRedirect(route('dashboard'));
    $user->refresh();
    expect($user->must_change_password)->toBeFalse();
    expect($user->meets_complexity_gate)->toBeTrue();
});
