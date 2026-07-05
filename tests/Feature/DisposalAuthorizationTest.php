<?php

/**
 * Module 1B.4 — personnel-records disposal must never be an unconditional
 * hard-delete. Before this fix, ArchiveController::forceDelete()/
 * bulkForceDelete() called $model->forceDelete() with no NAP Form No. 3
 * check at all, gated only by role:super_admin — a direct violation of this
 * project's own Absolute Rule #4 ("destruction is soft-delete routed to an
 * audited disposal workflow gated by signed-authority upload").
 */

use App\Models\DisposalAuthorization;
use App\Models\PlantillaRecord;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('local');
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');
});

test('force-deleting an archived plantilla record without a NAP Form 3 reference is blocked, record survives', function () {
    $record = PlantillaRecord::factory()->create();
    $record->delete(); // archive (soft-delete) first, as the real flow requires

    $response = $this->actingAs($this->admin)
        ->delete(route('archives.force-delete', ['plantilla', $record->id]));

    $response->assertSessionHasErrors('nap_form_reference');
    expect(PlantillaRecord::withTrashed()->find($record->id))->not->toBeNull();
    expect(DisposalAuthorization::count())->toBe(0);
});

test('force-deleting with a NAP Form 3 reference and scan succeeds and records the authorization', function () {
    $record = PlantillaRecord::factory()->create();
    $record->delete();

    $file = UploadedFile::fake()->create('nap-form-3.pdf', 50);

    $response = $this->actingAs($this->admin)
        ->delete(route('archives.force-delete', ['plantilla', $record->id]), [
            'nap_form_reference' => 'NAP-2026-00123',
            'nap_form_file' => $file,
        ]);

    $response->assertRedirect();
    expect(PlantillaRecord::withTrashed()->find($record->id))->toBeNull();

    $auth = DisposalAuthorization::first();
    expect($auth)->not->toBeNull();
    expect($auth->disposable_type)->toBe((new PlantillaRecord())->getMorphClass());
    expect($auth->disposable_id)->toBe($record->id);
    expect($auth->nap_form_reference)->toBe('NAP-2026-00123');
    expect($auth->file_path)->not->toBeNull();
    Storage::disk('local')->assertExists($auth->file_path);
});

test('a record with a prior disposal authorization on file can be deleted without resubmitting it', function () {
    $record = PlantillaRecord::factory()->create();
    $record->delete();

    app(\App\Support\Renewal\DisposalAuthorizationService::class)->authorize(
        $record, 'NAP-2026-00999', null, $this->admin
    );

    $response = $this->actingAs($this->admin)
        ->delete(route('archives.force-delete', ['plantilla', $record->id]));

    $response->assertRedirect();
    expect(PlantillaRecord::withTrashed()->find($record->id))->toBeNull();
});

test('bulk force-delete without a NAP Form 3 reference is blocked, all records survive', function () {
    $records = PlantillaRecord::factory()->count(3)->create();
    $records->each->delete();

    $response = $this->actingAs($this->admin)->post(route('all-data.bulk-force-delete', [
        'ids' => $records->pluck('id')->toArray(),
        'type' => 'plantilla',
    ]));

    $response->assertSessionHasErrors('nap_form_reference');
    foreach ($records as $record) {
        expect(PlantillaRecord::withTrashed()->find($record->id))->not->toBeNull();
    }
});

test('bulk force-delete with a NAP Form 3 reference authorizes and deletes every record in the batch', function () {
    $records = PlantillaRecord::factory()->count(3)->create();
    $records->each->delete();

    $response = $this->actingAs($this->admin)->post(route('all-data.bulk-force-delete', [
        'ids' => $records->pluck('id')->toArray(),
        'type' => 'plantilla',
        'nap_form_reference' => 'NAP-2026-BATCH-01',
    ]));

    $response->assertRedirect();
    foreach ($records as $record) {
        expect(PlantillaRecord::withTrashed()->find($record->id))->toBeNull();
    }
    expect(DisposalAuthorization::where('nap_form_reference', 'NAP-2026-BATCH-01')->count())->toBe(3);
});
