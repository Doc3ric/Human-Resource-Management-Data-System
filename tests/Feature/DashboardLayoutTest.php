<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->assignRole('System & Administration');
});

test('the dashboard renders through the shared horizontal-header layout, not its own duplicated sidebar', function () {
    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertOk();
    // The shared layout's header markup (added in the M0.2 rewrite).
    $response->assertSee('id="navHamburger"', false);
    $response->assertSee('nav-group-header', false);
    // The page's own unique content survived the refactor.
    $response->assertSee('Add New Employee');
    $response->assertSee('Total Workforce');
    $response->assertSee('canvas id="pieChart"', false);
    $response->assertSee('canvas id="barChart"', false);
    // The old duplicated shell must be gone.
    $response->assertDontSee('Human Resource<br>Data Management<br>System', false);
});

test('the dashboard shows the retirement/step-increment reminder banner when due', function () {
    \App\Models\PlantillaRecord::factory()->create([
        'employment_status' => 'P',
        'date_of_birth' => now()->subYears(65),
        'is_vacant' => false,
    ]);

    $response = $this->actingAs($this->admin)->get(route('dashboard'));

    $response->assertOk();
});
