<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can submit a registration request but are not logged in', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'Str0ng!Passw0rd',
        'password_confirmation' => 'Str0ng!Passw0rd',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('registration.pending'));

    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->is_approved)->toBeFalsy();

    expect(\App\Models\ActivityLog::where('action', 'Pending User Approval')
        ->where('user_id', $user->id)->exists())->toBeTrue();
});

test('a self-registered user cannot log in until a Super Admin approves them', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'Str0ng!Passw0rd',
        'password_confirmation' => 'Str0ng!Passw0rd',
    ]);

    $loginAttempt = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'Str0ng!Passw0rd',
    ]);

    $this->assertGuest();
    $loginAttempt->assertSessionHasErrors();

    $user = \App\Models\User::where('email', 'test@example.com')->first();
    $user->update(['is_approved' => true]);

    $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'Str0ng!Passw0rd',
    ]);

    $this->assertAuthenticated();
});
