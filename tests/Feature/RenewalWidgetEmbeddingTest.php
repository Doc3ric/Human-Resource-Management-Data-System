<?php

/**
 * Module 1A.5 — the shared Renewal Status Widget existed and was unit-tested
 * in isolation (RenewalGateTest) but was never actually embedded in any real
 * page. These tests confirm it's now wired into the two server-rendered
 * pages where it makes sense (Plantilla show is one record per page;
 * Batch Renewal already had its own equivalent row-level treatment before
 * this pass, so it's left alone) and that Performance/IPCR's IPCR-target
 * submission now emits the CORROBORATING signal Module 1A.4 requires
 * without ever setting is_renewed itself.
 */

use App\Http\Controllers\BatchRenewalController;
use App\Models\PlantillaRecord;
use App\Models\RenewalSignal;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');
});

test('the plantilla show page renders the renewal status widget for an unrenewed record', function () {
    $record = PlantillaRecord::factory()->create([
        'employment_status' => 'JO',
        'is_vacant' => false,
        'is_renewed' => false,
    ]);

    $response = $this->actingAs($this->admin)->get(route('plantilla.show', $record));

    $response->assertOk();
    $response->assertSee('Not renewed');
    $response->assertSee('Request Renewal');
});

test('the plantilla show page does not render the renewal warning for an already-renewed record', function () {
    $record = PlantillaRecord::factory()->create([
        'employment_status' => 'JO',
        'is_vacant' => false,
        'is_renewed' => true,
    ]);

    $response = $this->actingAs($this->admin)->get(route('plantilla.show', $record));

    $response->assertOk();
    $response->assertDontSee('Not renewed');
});

test('submitting an IPCR target for an unrenewed employee writes a corroborating signal, never sets is_renewed', function () {
    $record = PlantillaRecord::factory()->create([
        'employment_status' => 'JO',
        'is_vacant' => false,
        'is_renewed' => false,
    ]);

    $this->actingAs($this->admin)->post(route('performance.save'), [
        'plantilla_record_id' => $record->id,
        'period_type' => 'jan-jun',
        'year' => now()->year,
        'target_submitted' => true,
        'target_submission_date' => now()->toDateString(),
    ])->assertOk();

    $record->refresh();
    expect($record->is_renewed)->toBeFalse();
    expect(RenewalSignal::where('plantilla_record_id', $record->id)
        ->where('signal_type', 'IPCR_TARGET_SUBMITTED')
        ->where('signal_strength', RenewalSignal::STRENGTH_CORROBORATING)
        ->exists())->toBeTrue();
});

test('submitting an IPCR target for an already-renewed employee writes no signal at all', function () {
    $record = PlantillaRecord::factory()->create([
        'employment_status' => 'JO',
        'is_vacant' => false,
        'is_renewed' => true,
    ]);

    $this->actingAs($this->admin)->post(route('performance.save'), [
        'plantilla_record_id' => $record->id,
        'period_type' => 'jan-jun',
        'year' => now()->year,
        'target_submitted' => true,
        'target_submission_date' => now()->toDateString(),
    ])->assertOk();

    expect(RenewalSignal::where('plantilla_record_id', $record->id)->exists())->toBeFalse();
});

test('the performance employees endpoint surfaces is_renewed so the tracker can flag it', function () {
    $record = PlantillaRecord::factory()->create([
        'employment_status' => 'JO',
        'is_vacant' => false,
        'is_renewed' => false,
        'office_department' => 'PHRMO TEST OFFICE',
    ]);

    $response = $this->actingAs($this->admin)->get(route('performance.employees', [
        'office' => 'PHRMO TEST OFFICE',
        'year' => now()->year,
        'period_type' => 'jan-jun',
    ]));

    $response->assertOk();
    $found = collect($response->json())->firstWhere('id', $record->id);
    expect($found)->not->toBeNull();
    expect($found['is_renewed'])->toBeFalse();
});
