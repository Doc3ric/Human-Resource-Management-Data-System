<?php

use App\Http\Controllers\BatchRenewalController;
use App\Models\ContractRenewal;
use App\Models\PlantillaRecord;
use App\Models\RenewalSignal;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Appointment', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'add Batch Renewal', 'guard_name' => 'web']);
    Role::where('name', 'Appointment')->first()->givePermissionTo('add Batch Renewal');

    $this->appointmentOfficer = User::factory()->create();
    $this->appointmentOfficer->assignRole('Appointment');

    // A viewer has no renewal_commit authority.
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $this->viewer = User::factory()->create();
    $this->viewer->assignRole('Viewer');
});

function makeJoRecord(array $overrides = []): PlantillaRecord
{
    return PlantillaRecord::factory()->create(array_merge([
        'employment_status' => 'JO',
        'is_vacant' => false,
        'is_renewed' => true,
        'last_name' => 'DELA CRUZ',
        'first_name' => 'JUAN',
    ], $overrides));
}

test('an unrenewed casual/JO record is excluded from the filled() reporting scope', function () {
    $renewed = makeJoRecord(['is_renewed' => true]);
    $unrenewed = makeJoRecord(['is_renewed' => false]);

    $ids = PlantillaRecord::filled()->pluck('id');

    expect($ids)->toContain($renewed->id);
    expect($ids)->not->toContain($unrenewed->id);
});

test('a permanent employee is never excluded by the is_renewed gate regardless of its value', function () {
    $permanent = PlantillaRecord::factory()->create([
        'employment_status' => 'P',
        'is_vacant' => false,
        'is_renewed' => false, // irrelevant for permanent staff
    ]);

    expect(PlantillaRecord::filled()->pluck('id'))->toContain($permanent->id);
});

test('an authorized officer can batch-extend an already-renewed record for a new period', function () {
    // Batch Renewal is a mass EXTENSION of people already in good standing —
    // it is not the mechanism that clears a blocked/unrenewed record (that's
    // the individual clearance path, tested below).
    $record = makeJoRecord(['is_renewed' => true]);

    $response = $this->actingAs($this->appointmentOfficer)->post(route('batch-renewal.process'), [
        'ids' => [$record->id],
        'contract_start_date' => now()->toDateString(),
        'contract_end_date' => now()->addMonths(6)->toDateString(),
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['succeeded' => 1]);

    $record->refresh();
    expect($record->is_renewed)->toBeTrue();
    expect($record->renewal_period)->toBe(BatchRenewalController::currentRatingPeriod());
    expect(ContractRenewal::where('plantilla_record_id', $record->id)->exists())->toBeTrue();
    expect(RenewalSignal::where('plantilla_record_id', $record->id)->where('signal_strength', 'AUTHORITATIVE')->exists())->toBeTrue();
});

test('an authorized officer can individually clear a blocked/unrenewed record via the same commit service', function () {
    $blocked = makeJoRecord(['is_renewed' => false]);

    $response = $this->actingAs($this->appointmentOfficer)->post(route('plantilla.renew-individual', $blocked), [
        'contract_start_date' => now()->toDateString(),
        'contract_end_date' => now()->addMonths(6)->toDateString(),
    ]);

    $response->assertRedirect();
    $blocked->refresh();
    expect($blocked->is_renewed)->toBeTrue();
    expect(ContractRenewal::where('plantilla_record_id', $blocked->id)->exists())->toBeTrue();
});

test('a user without renewal_commit authority cannot process a batch renewal', function () {
    $record = makeJoRecord(['is_renewed' => false]);

    $this->actingAs($this->viewer)->post(route('batch-renewal.process'), [
        'ids' => [$record->id],
        'contract_start_date' => now()->toDateString(),
        'contract_end_date' => now()->addMonths(6)->toDateString(),
    ])->assertForbidden();

    expect($record->fresh()->is_renewed)->toBeFalse();
});

test('the batch endpoint rejects a forged id for a blocked/unrenewed record — batch never clears a block', function () {
    $blocked = makeJoRecord(['is_renewed' => false]);

    $response = $this->actingAs($this->appointmentOfficer)->post(route('batch-renewal.process'), [
        'ids' => [$blocked->id],
        'contract_start_date' => now()->toDateString(),
        'contract_end_date' => now()->addMonths(6)->toDateString(),
    ]);

    $response->assertStatus(422);
    expect($blocked->fresh()->is_renewed)->toBeFalse();
});

test('a non-authoritative signal never sets is_renewed', function () {
    $record = makeJoRecord(['is_renewed' => false]);

    app(\App\Support\Renewal\RenewalSignalService::class)->corroborate(
        $record,
        BatchRenewalController::currentRatingPeriod(),
        'PERFORMANCE',
        'IPCR_TARGET_SUBMITTED',
    );

    expect($record->fresh()->is_renewed)->toBeFalse();
    expect(RenewalSignal::where('plantilla_record_id', $record->id)->count())->toBe(1);
});

test('request-to-renew records a weak signal without granting commit', function () {
    $record = makeJoRecord(['is_renewed' => false]);

    $this->actingAs($this->viewer)->post(route('plantilla.request-renewal', $record), [
        'notes' => 'Employee is actively reporting for duty.',
    ])->assertRedirect();

    expect($record->fresh()->is_renewed)->toBeFalse();
    expect(RenewalSignal::where('plantilla_record_id', $record->id)->where('signal_type', 'RENEWAL_REQUESTED')->exists())->toBeTrue();
});

test('the synchronized renewal widget reflects the same is_renewed value everywhere it renders', function () {
    $record = makeJoRecord(['is_renewed' => false]);
    app(\App\Support\Renewal\RenewalSignalService::class)->corroborate(
        $record, BatchRenewalController::currentRatingPeriod(), 'PERFORMANCE', 'IPCR_TARGET_SUBMITTED'
    );

    $html = \Illuminate\Support\Facades\Blade::render(
        '<x-renewal-status-widget :record="$record" />',
        ['record' => $record]
    );

    expect($html)->toContain('Not renewed');
    expect($html)->toContain('IPCR_TARGET_SUBMITTED');
});
