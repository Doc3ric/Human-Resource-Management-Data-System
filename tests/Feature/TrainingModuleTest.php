<?php

use App\Models\PlantillaRecord;
use App\Models\Training;
use App\Models\TrainingCertificate;
use App\Models\TrainingParticipant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['view Training & Certificates', 'add Training & Certificates', 'edit Training & Certificates'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo([
        'view Training & Certificates', 'add Training & Certificates', 'edit Training & Certificates',
    ]);
    $this->ldStaff = User::factory()->create();
    $this->ldStaff->assignRole('Personnel Records');
});

test('an authorized L&D staffer can record a training', function () {
    $response = $this->actingAs($this->ldStaff)->post(route('training.store'), [
        'title' => 'Customer Service Excellence Seminar',
        'type' => 'seminar',
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-02',
        'total_hours' => 16,
    ]);

    $response->assertRedirect();
    expect(Training::where('title', 'Customer Service Excellence Seminar')->exists())->toBeTrue();
});

test('a PGB participant registers linked to their plantilla record; a non-PGB participant registers ad-hoc', function () {
    $training = Training::factory()->create();
    $record = PlantillaRecord::factory()->create(['last_name' => 'SANTOS', 'first_name' => 'MARIA']);

    $this->actingAs($this->ldStaff)->post(route('training.participants.store', $training), [
        'personnel_id' => $record->id, 'attendance_status' => 'present',
    ])->assertRedirect();

    $this->actingAs($this->ldStaff)->post(route('training.participants.store', $training), [
        'participant_name' => 'External Guest', 'participant_office' => 'DOLE', 'attendance_status' => 'present',
    ])->assertRedirect();

    expect(TrainingParticipant::where('personnel_id', $record->id)->exists())->toBeTrue();
    expect(TrainingParticipant::where('participant_name', 'External Guest')->exists())->toBeTrue();
});

test('issuing an individual certificate links a document via IDCC and is queryable in the training history', function () {
    $training = Training::factory()->create();
    $record = PlantillaRecord::factory()->create();
    $participant = TrainingParticipant::create([
        'training_id' => $training->id, 'personnel_id' => $record->id, 'attendance_status' => 'present', 'hours_attended' => 16,
    ]);

    $this->actingAs($this->ldStaff)->post(route('training.participants.certificate', $participant))->assertRedirect();

    $cert = TrainingCertificate::where('training_participant_id', $participant->id)->first();
    expect($cert)->not->toBeNull();
    expect($cert->document_id)->not->toBeNull();

    $historyResponse = $this->actingAs($this->ldStaff)->get(route('training.history', $record));
    $historyResponse->assertOk();
    $historyResponse->assertSee($cert->reference_no);
});

test('a certificate cannot be issued for a participant marked absent', function () {
    $training = Training::factory()->create();
    $participant = TrainingParticipant::create([
        'training_id' => $training->id, 'participant_name' => 'No Show', 'attendance_status' => 'absent',
    ]);

    $this->actingAs($this->ldStaff)->post(route('training.participants.certificate', $participant))
        ->assertSessionHas('error');

    expect(TrainingCertificate::where('training_participant_id', $participant->id)->exists())->toBeFalse();
});

test('batch issuance skips absent participants and issues for the rest', function () {
    $training = Training::factory()->create();
    $present = TrainingParticipant::create(['training_id' => $training->id, 'participant_name' => 'Present One', 'attendance_status' => 'present']);
    $absent = TrainingParticipant::create(['training_id' => $training->id, 'participant_name' => 'Absent One', 'attendance_status' => 'absent']);

    $this->actingAs($this->ldStaff)->post(route('training.certificates.batch', $training))->assertRedirect();

    expect(TrainingCertificate::where('training_participant_id', $present->id)->exists())->toBeTrue();
    expect(TrainingCertificate::where('training_participant_id', $absent->id)->exists())->toBeFalse();
});

test('a resource speaker gets the distinct resource-speaker certificate type', function () {
    $training = Training::factory()->create();
    $speaker = TrainingParticipant::create([
        'training_id' => $training->id, 'participant_name' => 'Dr. Reyes', 'is_resource_speaker' => true,
        'topic' => 'Workplace Ethics', 'attendance_status' => 'present',
    ]);

    $this->actingAs($this->ldStaff)->post(route('training.participants.certificate', $speaker))->assertRedirect();

    $cert = TrainingCertificate::where('training_participant_id', $speaker->id)->first();
    expect($cert->certificate_type)->toBe('resource_speaker');
});
