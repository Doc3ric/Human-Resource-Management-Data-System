<?php

use App\Http\Controllers\LguDocumentController;
use App\Models\LguDocument;
use App\Models\ServiceRequest;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['view LGU Documents', 'add LGU Documents', 'edit LGU Documents'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo(['view LGU Documents', 'add LGU Documents', 'edit LGU Documents']);
    $this->clerk = User::factory()->create();
    $this->clerk->assignRole('Personnel Records');
});

test('an authorized clerk can register an LGU document', function () {
    $response = $this->actingAs($this->clerk)->post(route('lgu.documents.store'), [
        'type' => 'EO', 'title' => 'Reorganization of PHRMO', 'date_issued' => '2026-01-15',
    ]);

    $response->assertRedirect();
    expect(LguDocument::where('title', 'Reorganization of PHRMO')->exists())->toBeTrue();
});

test('logging a service request computes a due date per the Citizens Charter window', function () {
    $response = $this->actingAs($this->clerk)->post(route('lgu.requests.store'), [
        'request_type' => 'COE', 'requester_name' => 'JUAN DELA CRUZ', 'date_requested' => '2026-03-02', // Monday
    ]);

    $response->assertRedirect();
    $req = ServiceRequest::first();
    expect($req->due_at->toDateString())->toBe('2026-03-05'); // +3 weekdays from Monday = Thursday
});

test('a service request nearing its deadline gets auto-escalated', function () {
    $req = ServiceRequest::create([
        'request_type' => 'COE', 'requester_name' => 'MARIA SANTOS',
        'date_requested' => now()->subDays(3), 'due_at' => now(), 'status' => 'pending',
    ]);

    $count = (new LguDocumentController())->escalateDueSoon();

    expect($count)->toBe(1);
    expect($req->fresh()->status)->toBe('escalated');
    expect($req->fresh()->escalated_at)->not->toBeNull();
});

test('completing a service request marks it done and records who handled it', function () {
    $req = ServiceRequest::create([
        'request_type' => 'CERTIFICATION', 'requester_name' => 'PEDRO REYES',
        'date_requested' => now(), 'due_at' => now()->addDays(3), 'status' => 'pending',
    ]);

    $this->actingAs($this->clerk)->post(route('lgu.requests.complete', $req))->assertRedirect();

    $req->refresh();
    expect($req->status)->toBe('completed');
    expect($req->handled_by)->toBe($this->clerk->id);
});

test('a user without LGU permissions cannot register a document', function () {
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    $this->actingAs($viewer)->post(route('lgu.documents.store'), [
        'type' => 'MEMO', 'title' => 'Test Memo', 'date_issued' => '2026-01-01',
    ])->assertForbidden();
});
