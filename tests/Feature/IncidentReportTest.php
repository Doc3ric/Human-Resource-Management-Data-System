<?php

use App\Models\CounselingRecord;
use App\Models\IncidentReport;
use App\Models\User;
use App\Support\Incident\RulesAdvisoryEngine;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['view Incident Reports', 'add Incident Reports', 'edit Incident Reports', 'delete Incident Reports'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo([
        'view Incident Reports', 'add Incident Reports', 'edit Incident Reports', 'delete Incident Reports',
    ]);
    $this->supervisor = User::factory()->create();
    $this->supervisor->assignRole('Personnel Records');

    $this->hrmo = User::factory()->create();
    $this->hrmo->assignRole('Personnel Records');
});

test('the advisory engine suggests a light/counseling track for tardiness facts', function () {
    $draft = (new RulesAdvisoryEngine())->draft('Employee arrived late three times this week.', 'attendance');

    expect($draft['suggested_track'])->toBe('counseling');
    expect($draft['citations'][0]['provision'])->toContain('Tardiness');
});

test('the advisory engine suggests escalation for grave-offense facts', function () {
    $draft = (new RulesAdvisoryEngine())->draft('Employee was seen falsifying the daily time record.', 'conduct');

    expect($draft['suggested_track'])->toBe('escalated');
});

test('an authorized supervisor can file an incident report with facts only', function () {
    $response = $this->actingAs($this->supervisor)->post(route('incidents.store'), [
        'incident_datetime' => '2026-06-01 09:00',
        'category' => 'attendance',
        'narrative' => 'Employee arrived 45 minutes late without prior notice.',
    ]);

    $response->assertRedirect();
    expect(IncidentReport::count())->toBe(1);
    expect(IncidentReport::first()->status)->toBe('draft');
});

test('generating an advisory draft labels it clearly and never sets a final track', function () {
    $incident = IncidentReport::create([
        'reference_no' => 'INC-TEST-1', 'incident_datetime' => now(), 'reported_at' => now(),
        'category' => 'attendance', 'narrative' => 'Late arrivals repeatedly this month.',
        'reported_by' => $this->supervisor->id, 'status' => 'draft',
    ]);

    $this->actingAs($this->hrmo)->post(route('incidents.advisory', $incident))->assertRedirect();

    $incident->refresh();
    expect($incident->status)->toBe('advisory_ready');
    expect($incident->final_track)->toBeNull(); // advisory never sets a final track
    expect($incident->advisory_citations)->not->toBeEmpty();
});

test('a user cannot finalize an incident report they filed themselves', function () {
    $incident = IncidentReport::create([
        'reference_no' => 'INC-TEST-2', 'incident_datetime' => now(), 'reported_at' => now(),
        'category' => 'conduct', 'narrative' => 'Some conduct concern.',
        'reported_by' => $this->supervisor->id, 'status' => 'advisory_ready',
    ]);

    $this->actingAs($this->supervisor)->post(route('incidents.finalize', $incident), [
        'final_track' => 'counseling',
        'counseling_recommendation' => 'Coaching session.',
    ])->assertForbidden();
});

test('finalizing to the counseling track creates a counseling record and reclassifies the linked document', function () {
    $incident = IncidentReport::create([
        'reference_no' => 'INC-TEST-3', 'incident_datetime' => now(), 'reported_at' => now(),
        'category' => 'attendance', 'narrative' => 'Late arrivals.', 'reported_by' => $this->supervisor->id,
        'status' => 'advisory_ready',
    ]);

    $response = $this->actingAs($this->hrmo)->post(route('incidents.finalize', $incident), [
        'final_track' => 'counseling',
        'counseling_recommendation' => 'Coaching session and improvement plan.',
        'counseling_action_plan' => 'Report on time for 30 days; weekly check-ins.',
    ]);

    $response->assertRedirect();
    $incident->refresh();
    expect($incident->status)->toBe('finalized');
    expect($incident->final_track)->toBe('counseling');
    expect(CounselingRecord::where('incident_report_id', $incident->id)->exists())->toBeTrue();
});

test('finalizing to the escalated track requires the incident_escalate (delete) permission', function () {
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'edit Incident Reports', 'guard_name' => 'web']);
    Role::where('name', 'Viewer')->first()->givePermissionTo('edit Incident Reports');
    $limitedReviewer = User::factory()->create();
    $limitedReviewer->assignRole('Viewer');

    $incident = IncidentReport::create([
        'reference_no' => 'INC-TEST-4', 'incident_datetime' => now(), 'reported_at' => now(),
        'category' => 'conduct', 'narrative' => 'Serious misconduct alleged.',
        'reported_by' => $this->supervisor->id, 'status' => 'advisory_ready',
    ]);

    $this->actingAs($limitedReviewer)->post(route('incidents.finalize', $incident), [
        'final_track' => 'escalated',
    ])->assertForbidden();
});
