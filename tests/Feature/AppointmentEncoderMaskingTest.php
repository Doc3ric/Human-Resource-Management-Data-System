<?php

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('local');

    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Appointment Encoder', 'guard_name' => 'web']);
    $this->ae = User::factory()->create();
    $this->ae->assignRole('Appointment Encoder');
});

test('Module 4.1: an Appointment Encoder is blocked from TWG scoring routes', function () {
    $this->actingAs($this->ae)->get(route('recruitment.hrmpsb.twg.create'))->assertForbidden();
});

test('Module 4.1: an Appointment Encoder is blocked from the HRMPSB interview evaluation route', function () {
    $this->actingAs($this->ae)->get(route('recruitment.hrmpsb.interview.create'))->assertForbidden();
});

test('Module 4.1: an Appointment Encoder is blocked from Batch Renewal and Contract Status', function () {
    $this->actingAs($this->ae)->get(route('batch-renewal.index'))->assertForbidden();
    $this->actingAs($this->ae)->get(route('contract-status.index'))->assertForbidden();
});

test('Module 4.1: an Appointment Encoder CAN still access basic Recruitment', function () {
    $this->actingAs($this->ae)->get(route('recruitment.index'))->assertOk();
});

test('Module 4.2: saving an applicant without an application letter or override is rejected', function () {
    $response = $this->actingAs($this->ae)->post(route('recruitment.store'), [
        'last_name' => 'DELA CRUZ', 'first_name' => 'JUAN', 'sex' => 'Male',
        'date_of_birth' => '1990-01-01', 'address' => 'Bukidnon',
        'phone_number' => '09171234567', 'email_address' => 'juan1@example.com',
        'position_applied' => 'ADMINISTRATIVE AIDE I', 'office' => 'PHRMO',
        'highest_educational_attainment' => 'College Graduate',
    ]);

    $response->assertSessionHasErrors('application_letter');
    expect(Applicant::count())->toBe(0);
});

test('Module 4.2: override without a reason is rejected', function () {
    $response = $this->actingAs($this->ae)->post(route('recruitment.store'), [
        'last_name' => 'DELA CRUZ', 'first_name' => 'JUAN', 'sex' => 'Male',
        'date_of_birth' => '1990-01-01', 'address' => 'Bukidnon',
        'phone_number' => '09171234567', 'email_address' => 'juan2@example.com',
        'position_applied' => 'ADMINISTRATIVE AIDE I', 'office' => 'PHRMO',
        'highest_educational_attainment' => 'College Graduate',
        'application_letter_override' => '1',
    ]);

    $response->assertSessionHasErrors('application_letter_override_reason');
    expect(Applicant::count())->toBe(0);
});

test('Module 4.2: override with a reason is accepted and no attachment is required', function () {
    $response = $this->actingAs($this->ae)->post(route('recruitment.store'), [
        'last_name' => 'DELA CRUZ', 'first_name' => 'JUAN', 'sex' => 'Male',
        'date_of_birth' => '1990-01-01', 'address' => 'Bukidnon',
        'phone_number' => '09171234567', 'email_address' => 'juan3@example.com',
        'position_applied' => 'ADMINISTRATIVE AIDE I', 'office' => 'PHRMO',
        'highest_educational_attainment' => 'College Graduate',
        'application_letter_override' => '1',
        'application_letter_override_reason' => 'Applicant submitted via walk-in with no digital letter available.',
    ]);

    $response->assertRedirect();
    $applicant = Applicant::where('email_address', 'juan3@example.com')->first();
    expect($applicant)->not->toBeNull();
    expect($applicant->application_letter_override)->toBeTrue();
    expect($applicant->application_letter_document_id)->toBeNull();
});

test('Module 4.2/9A.6: attaching a real application letter ingests it via IDCC and links the document', function () {
    $file = UploadedFile::fake()->create('letter.pdf', 100, 'application/pdf');

    $response = $this->actingAs($this->ae)->post(route('recruitment.store'), [
        'last_name' => 'DELA CRUZ', 'first_name' => 'JUAN', 'sex' => 'Male',
        'date_of_birth' => '1990-01-01', 'address' => 'Bukidnon',
        'phone_number' => '09171234567', 'email_address' => 'juan4@example.com',
        'position_applied' => 'ADMINISTRATIVE AIDE I', 'office' => 'PHRMO',
        'highest_educational_attainment' => 'College Graduate',
        'application_letter' => $file,
    ]);

    $response->assertRedirect();
    $applicant = Applicant::where('email_address', 'juan4@example.com')->first();
    expect($applicant->application_letter_document_id)->not->toBeNull();
    expect($applicant->applicationLetterDocument)->not->toBeNull();
});
