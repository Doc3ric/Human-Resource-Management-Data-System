<?php

use App\Http\Controllers\BatchRenewalController;
use App\Models\LeaveViolation;
use App\Models\PlantillaRecord;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Module 2B.3/2B.4(e)/2B.7 — closes the gaps found while auditing Module 2B:
 * a real watchlist covering every violation type (not just the manual
 * LWOP_TARDINESS ledger), CSV export, a Show-Cause letter, and the missing
 * "responded" step in the due-process status flow.
 */
beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['view Leave Violations', 'add Leave Violations', 'edit Leave Violations'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo([
        'view Leave Violations', 'add Leave Violations', 'edit Leave Violations',
    ]);
    $this->hrmo = User::factory()->create();
    $this->hrmo->assignRole('Personnel Records');
});

test('Module 2B.3: the watchlist lists every violation type, not just the manual LWOP ledger', function () {
    $record = PlantillaRecord::factory()->create(['office_department' => 'PHRMO']);
    LeaveViolation::create([
        'plantilla_record_id' => $record->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);
    LeaveViolation::create([
        'plantilla_record_id' => $record->id, 'violation_type' => 'HABITUAL_ABSENTEEISM', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);
    LeaveViolation::create([
        'plantilla_record_id' => $record->id, 'violation_type' => 'LATE_FILING_VACATION_LEAVE', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);

    $response = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist'));

    $response->assertOk();
    $response->assertSee('AWOL');
    $response->assertSee('HABITUAL_ABSENTEEISM');
    $response->assertSee('LATE_FILING_VACATION_LEAVE');
});

test('Module 2B.3: the watchlist filters by office, violation type, offense tier, rating period, and status', function () {
    $phrmo = PlantillaRecord::factory()->create(['office_department' => 'PHRMO', 'last_name' => 'PHRMOEMP']);
    $accounting = PlantillaRecord::factory()->create(['office_department' => 'Accounting', 'last_name' => 'ACCTEMP']);

    LeaveViolation::create([
        'plantilla_record_id' => $phrmo->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);
    LeaveViolation::create([
        'plantilla_record_id' => $accounting->id, 'violation_type' => 'LWOP', 'offense_tier' => 2,
        'rating_period' => '2026_2nd_semester', 'details' => [], 'status' => 'resolved',
    ]);

    $byOffice = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist', ['office' => 'PHRMO']));
    $byOffice->assertSee('PHRMOEMP');
    $byOffice->assertDontSee('ACCTEMP');

    $byType = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist', ['violation_type' => 'LWOP']));
    $byType->assertSee('ACCTEMP');
    $byType->assertDontSee('PHRMOEMP');

    $byTier = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist', ['offense_tier' => 2]));
    $byTier->assertSee('ACCTEMP');
    $byTier->assertDontSee('PHRMOEMP');

    $byPeriod = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist', ['rating_period' => '2026_1st_semester']));
    $byPeriod->assertSee('PHRMOEMP');
    $byPeriod->assertDontSee('ACCTEMP');

    $byStatus = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist', ['status' => 'resolved']));
    $byStatus->assertSee('ACCTEMP');
    $byStatus->assertDontSee('PHRMOEMP');
});

test('Module 2B.3: the watchlist sorts grave offenses (AWOL/Habitual Absenteeism) before lighter/procedural ones', function () {
    $record = PlantillaRecord::factory()->create();
    $filing = LeaveViolation::create([
        'plantilla_record_id' => $record->id, 'violation_type' => 'LATE_FILING_SICK_LEAVE', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected', 'created_at' => now()->subDay(),
    ]);
    $awol = LeaveViolation::create([
        'plantilla_record_id' => $record->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected', 'created_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist'));

    $body = $response->getContent();
    expect(strpos($body, 'AWOL'))->toBeLessThan(strpos($body, 'LATE_FILING_SICK_LEAVE'));
});

test('Module 2B.3: CSV export streams the filtered watchlist with legal citations', function () {
    $record = PlantillaRecord::factory()->create(['last_name' => 'CSVEXPORTEMP']);
    LeaveViolation::create([
        'plantilla_record_id' => $record->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);

    $response = $this->actingAs($this->hrmo)->get(route('leave-violations.watchlist.export-csv'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
    $csv = $response->streamedContent();
    expect($csv)->toContain('CSVEXPORTEMP');
    expect($csv)->toContain('2025 RACCS Rule 10');
});

test('Module 2B.4(e): a Show-Cause order can be issued and is recorded via IDCC', function () {
    $record = PlantillaRecord::factory()->create();

    $response = $this->actingAs($this->hrmo)->post(route('leave-violations.store-show-cause'), [
        'plantilla_record_id' => $record->id,
        'grounds' => 'Repeated unauthorized absences documented in the attached report.',
        'signatory_name' => 'AIDA B. LOVERES',
        'signatory_position' => 'PHRMO',
    ]);

    $response->assertRedirect();
    $violation = LeaveViolation::where('violation_type', 'SHOW_CAUSE')->first();
    expect($violation)->not->toBeNull();
    expect($violation->status)->toBe('notice_pending');
    expect($violation->document_id)->not->toBeNull();
});

test('Module 2B.7: resolving a violation is blocked until a notice has actually been issued', function () {
    $violation = LeaveViolation::create([
        'plantilla_record_id' => PlantillaRecord::factory()->create()->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);

    $this->actingAs($this->hrmo)->post(route('leave-violations.resolve', $violation))->assertStatus(422);
    expect($violation->fresh()->status)->toBe('detected');
});

test('Module 2B.7: the response window (Responded) must be recorded before resolution proceeds from a served notice', function () {
    $violation = LeaveViolation::create([
        'plantilla_record_id' => PlantillaRecord::factory()->create()->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => ['total_unauthorized_days' => 35], 'status' => 'notice_pending',
    ]);

    $this->actingAs($this->hrmo)->post(route('leave-violations.respond', $violation), [
        'response_notes' => 'Employee submitted a written explanation citing a medical emergency.',
    ])->assertRedirect();

    $violation->refresh();
    expect($violation->status)->toBe('responded');
    expect($violation->details['response_notes'])->toContain('medical emergency');

    $this->actingAs($this->hrmo)->post(route('leave-violations.resolve', $violation))->assertRedirect();
    expect($violation->fresh()->status)->toBe('resolved');
});

test('Module 2B.7: a violation cannot be marked responded before a notice has been issued', function () {
    $violation = LeaveViolation::create([
        'plantilla_record_id' => PlantillaRecord::factory()->create()->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);

    $this->actingAs($this->hrmo)->post(route('leave-violations.respond', $violation))->assertStatus(422);
});

test('a user without Leave Violations permission cannot access the watchlist', function () {
    Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
    $viewer = User::factory()->create();
    $viewer->assignRole('Viewer');

    $this->actingAs($viewer)->get(route('leave-violations.watchlist'))->assertForbidden();
});
