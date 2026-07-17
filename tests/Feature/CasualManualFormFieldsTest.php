<?php

use App\Models\CasualEmployee;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Regression coverage for a bug where CasualController's manual create/edit
 * form used friendly-alias field names (office, sg_current, step_current,
 * salary_current, current_rate, eligibility, annotation) that don't match
 * any fillable column on PlantillaRecord, so Eloquent's mass assignment
 * silently dropped them. The Excel import path used the correct names and
 * worked fine — only the manual Add/Edit form and Excel *export* were
 * affected.
 */
beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');
});

test('creating a casual employee via the manual form saves all previously-dropped fields', function () {
    $response = $this->actingAs($this->admin)->post(route('casual.store'), [
        'office_department' => 'TEST OFFICE',
        'item_no_old' => '1',
        'item_no_new' => '1',
        'position_title' => 'Administrative Aide I',
        'last_name' => 'Dela Cruz',
        'first_name' => 'Juan',
        'sex' => 'M',
        'salary_grade' => 3,
        'step' => 1,
        'authorized_annual_salary' => 190224,
        'base_salary_amount' => 15852,
        'civil_service_eligibility' => 'No Eligibility',
        'remarks_annotation' => 'Test annotation',
    ]);

    $response->assertRedirect();
    $casual = CasualEmployee::where('last_name', 'Dela Cruz')->first();

    expect($casual)->not->toBeNull();
    expect($casual->office_department)->toBe('TEST OFFICE');
    expect((int) $casual->salary_grade)->toBe(3);
    expect((int) $casual->step)->toBe(1);
    expect((float) $casual->authorized_annual_salary)->toBe(190224.0);
    expect((float) $casual->base_salary_amount)->toBe(15852.0);
    expect($casual->civil_service_eligibility)->toBe('No Eligibility');
    expect($casual->remarks_annotation)->toBe('Test annotation');
});

test('editing a casual employee via the manual form updates all previously-dropped fields', function () {
    $casual = CasualEmployee::create([
        'office_department' => 'OLD OFFICE',
        'position_title' => 'Administrative Aide I',
        'last_name' => 'Santos',
        'first_name' => 'Maria',
        'sex' => 'F',
    ]);

    $response = $this->actingAs($this->admin)->put(route('casual.update', $casual), [
        'office_department' => 'NEW OFFICE',
        'position_title' => 'Administrative Aide I',
        'last_name' => 'Santos',
        'first_name' => 'Maria',
        'sex' => 'F',
        'salary_grade' => 5,
        'step' => 2,
        'authorized_annual_salary' => 214392,
        'base_salary_amount' => 17866,
        'civil_service_eligibility' => 'CS Professional',
        'remarks_annotation' => 'Updated annotation',
    ]);

    $response->assertRedirect();
    $casual->refresh();

    expect($casual->office_department)->toBe('NEW OFFICE');
    expect((int) $casual->salary_grade)->toBe(5);
    expect((int) $casual->step)->toBe(2);
    expect((float) $casual->authorized_annual_salary)->toBe(214392.0);
    expect((float) $casual->base_salary_amount)->toBe(17866.0);
    expect($casual->civil_service_eligibility)->toBe('CS Professional');
    expect($casual->remarks_annotation)->toBe('Updated annotation');
});
