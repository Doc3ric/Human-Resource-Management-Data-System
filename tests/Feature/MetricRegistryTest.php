<?php

use App\Models\IncidentReport;
use App\Models\LeaveViolation;
use App\Models\PlantillaRecord;
use App\Models\ServiceRequest;
use App\Models\Training;
use App\Models\User;
use App\Support\Metrics\MetricRegistry;
use Illuminate\Support\Facades\Blade;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');
});

test('the renewal metric counts renewed vs not-renewed casual/JO personnel only', function () {
    PlantillaRecord::factory()->create(['employment_status' => 'JO', 'is_renewed' => false]);
    PlantillaRecord::factory()->create(['employment_status' => 'JO', 'is_renewed' => true]);
    PlantillaRecord::factory()->create(['employment_status' => 'P', 'is_renewed' => false]); // irrelevant to this metric

    $metric = MetricRegistry::get('renewal_active_not_renewed', $this->admin);

    expect($metric['value'])->toBe(1);
    expect($metric['owner'])->toBe('Renewal & Active Status (1A)');
});

test('an unregistered metric key returns null rather than throwing', function () {
    expect(MetricRegistry::get('not_a_real_metric', $this->admin))->toBeNull();
});

test('the leave violation metric reflects an encouraging empty state when there are none', function () {
    $metric = MetricRegistry::get('leave_violation_counts', $this->admin);

    expect($metric['value'])->toBe(0);
    expect($metric['context'])->toContain('all clear');
});

test('the leave violation metric counts real violations by status', function () {
    $record = PlantillaRecord::factory()->create();
    LeaveViolation::create([
        'plantilla_record_id' => $record->id, 'violation_type' => 'AWOL', 'offense_tier' => 1,
        'rating_period' => '2026_1st_semester', 'details' => [], 'status' => 'detected',
    ]);

    $metric = MetricRegistry::get('leave_violation_counts', $this->admin);
    expect($metric['value'])->toBe(1);
});

test('the incident metric counts only finalized incidents with a track, never narrative content', function () {
    $record = PlantillaRecord::factory()->create();
    IncidentReport::create([
        'reference_no' => 'INC-M12-1', 'incident_datetime' => now(), 'reported_at' => now(),
        'category' => 'conduct', 'narrative' => 'Some sensitive narrative text.',
        'reported_by' => $this->admin->id, 'status' => 'finalized', 'final_track' => 'counseling',
    ]);

    $metric = MetricRegistry::get('incident_counts_by_track', $this->admin);

    expect($metric['value'])->toBe(1);
    expect($metric['context'])->toContain('counseling');
    expect($metric['context'])->not->toContain('sensitive narrative');
});

test('the LGU overdue metric flags requests past the Citizens Charter window', function () {
    ServiceRequest::create([
        'request_type' => 'COE', 'requester_name' => 'Test', 'date_requested' => now()->subDays(10),
        'due_at' => now()->subDays(3), 'status' => 'pending',
    ]);

    $metric = MetricRegistry::get('lgu_service_requests_overdue', $this->admin);
    expect($metric['value'])->toBe(1);
    expect($metric['context'])->toContain("Citizen's Charter window");
});

test('the shared scoreboard-tile component renders a valid metric and shows a link to its owner', function () {
    $record = PlantillaRecord::factory()->create();

    $this->actingAs($this->admin);
    $html = Blade::render('<x-scoreboard-tile metric-key="training_hours_delivered" />');

    expect($html)->toContain('Training Hours Delivered');
    expect($html)->toContain('Learning &amp; Development (10B)');
});

test('the scoreboard-tile component renders nothing for an unknown metric key rather than erroring', function () {
    $this->actingAs($this->admin);
    $html = Blade::render('<x-scoreboard-tile metric-key="unknown_metric" />');

    expect(trim($html))->toBe('');
});

test('Module 12.3: workforce_headcount_by_sex is gated by PlantillaRecord::filled(), excluding abolished/separated/vacant rows', function () {
    PlantillaRecord::factory()->create(['sex' => 'M', 'employment_status' => 'P']);
    PlantillaRecord::factory()->create(['sex' => 'F', 'employment_status' => 'P']);
    PlantillaRecord::factory()->create(['sex' => 'M', 'employment_status' => 'P', 'is_vacant' => true]); // excluded
    PlantillaRecord::factory()->create(['sex' => 'M', 'employment_status' => 'P', 'lifecycle_status' => 'RETIRED']); // excluded

    $metric = MetricRegistry::get('workforce_headcount_by_sex', $this->admin);

    expect($metric['value'])->toBe(2);
    expect($metric['owner'])->toBe('GAD & Workforce / Analytics');
});

test('Module 12.3: workforce_pwd_count reflects the R.A. 10524 1% quota check', function () {
    PlantillaRecord::factory()->create(['is_pwd' => true, 'employment_status' => 'P']);
    PlantillaRecord::factory()->count(9)->create(['is_pwd' => false, 'employment_status' => 'P']);

    $metric = MetricRegistry::get('workforce_pwd_count', $this->admin);

    expect($metric['value'])->toBe(1);
    expect($metric['insight'])->toContain('Meets the 1%');
});

test('Module 12.3: workforce_solo_parent_count treats solo_parent as a populated ID string, not a boolean', function () {
    PlantillaRecord::factory()->create(['solo_parent' => 'SP-2026-001', 'employment_status' => 'P']);
    PlantillaRecord::factory()->create(['solo_parent' => null, 'employment_status' => 'P']);
    PlantillaRecord::factory()->create(['solo_parent' => '-', 'employment_status' => 'P']);

    $metric = MetricRegistry::get('workforce_solo_parent_count', $this->admin);

    expect($metric['value'])->toBe(1);
});
