<?php

use App\Models\PlantillaRecord;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PermanentImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'System & Administration', 'guard_name' => 'web']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('System & Administration');

    Role::firstOrCreate(['name' => 'Personnel Records', 'guard_name' => 'web']);
    foreach (['view Permanent Employees', 'add Permanent Employees', 'edit Permanent Employees', 'delete Permanent Employees'] as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    Role::where('name', 'Personnel Records')->first()->givePermissionTo([
        'view Permanent Employees', 'add Permanent Employees', 'edit Permanent Employees', 'delete Permanent Employees',
    ]);
    $this->staff = User::factory()->create();
    $this->staff->assignRole('Personnel Records');
});

test('an authorized user can create a Permanent record with the correct employment status', function () {
    $response = $this->actingAs($this->staff)->post(route('permanent.store'), [
        'employment_status' => 'P',
        'office_department' => 'TEST OFFICE',
        'position_title' => 'Administrative Aide I',
        'last_name' => 'Reyes',
        'first_name' => 'Ana',
        'sex' => 'F',
        'salary_grade' => 1,
        'step' => 1,
        'authorized_annual_salary' => 168732,
    ]);

    $response->assertRedirect();
    $record = PlantillaRecord::where('last_name', 'Reyes')->first();
    expect($record)->not->toBeNull();
    expect($record->employment_status)->toBe('P');
    expect($record->office_department)->toBe('TEST OFFICE');
    expect((float) $record->authorized_annual_salary)->toBe(168732.0);
});

test('a Co-Terminous record can be created via the employment status selector', function () {
    $response = $this->actingAs($this->staff)->post(route('permanent.store'), [
        'employment_status' => 'CT',
        'position_title' => 'Executive Assistant I',
        'last_name' => 'Cruz',
        'first_name' => 'Pedro',
    ]);

    $response->assertRedirect();
    $record = PlantillaRecord::where('last_name', 'Cruz')->first();
    expect($record->employment_status)->toBe('CT');
});

test('importing a simple-template file creates records with employment status from the file', function () {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'PERMANENT IMPORT TEMPLATE');
    $headers = [
        'EMPLOYMENT STATUS (P/CT/E)', 'OFFICE', 'ITEM NO. (OLD)', 'ITEM NO. (NEW)', 'POSITION TITLE',
        'LAST NAME', 'FIRST NAME', 'MIDDLE NAME', 'NAME EXT (Jr./Sr.)', 'VACANT? (Y=Yes)',
        'SG (CURRENT)', 'STEP (CURRENT)', 'ANNUAL SALARY (CURRENT)', 'SG (PROPOSED)', 'STEP (PROPOSED)',
        'ANNUAL SALARY (PROPOSED)', 'INCREASE/DECREASE', 'PREVIOUS RATE (Monthly)', 'CURRENT RATE (Monthly)',
        'SEX (M/F)', 'BIRTHDATE (YYYY-MM-DD)', 'FIRST DAY OF SERVICE', 'ELIGIBILITY',
    ];
    foreach ($headers as $i => $h) {
        $sheet->setCellValueByColumnAndRow($i + 1, 2, $h);
    }
    $row = ['P', "PROVINCIAL GOVERNOR'S OFFICE", '1', '1', 'Executive Assistant III',
        'Cruz', 'Cornelio Melan', 'L.', '', 'N', '20', '1', '755604', '20', '1', '792624',
        '15425', '62967', '66052', 'M', '1990-05-20', '2015-01-02', 'CS Professional'];
    foreach ($row as $i => $v) {
        $sheet->setCellValueByColumnAndRow($i + 1, 3, $v);
    }

    $path = sys_get_temp_dir() . '/perm_test_' . uniqid() . '.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    $import = new PermanentImport();
    Excel::import($import, $path);
    @unlink($path);

    expect($import->imported)->toBe(1);
    $record = PlantillaRecord::where('last_name', 'Cruz')->first();
    expect($record)->not->toBeNull();
    expect($record->employment_status)->toBe('P');
    expect($record->office_department)->toBe("PROVINCIAL GOVERNOR'S OFFICE");
    expect((int) $record->salary_grade)->toBe(20);
    expect((float) $record->authorized_annual_salary)->toBe(755604.0);
    expect((float) $record->salary_proposed)->toBe(792624.0);
    expect($record->sex)->toBe('M');
});

test('the import undo route deletes exactly the records created by that import', function () {
    PlantillaRecord::create([
        'employment_status' => 'P', 'position_title' => 'Nurse I',
        'last_name' => 'Undo Test', 'first_name' => 'One',
    ]);
    $created = PlantillaRecord::where('last_name', 'Undo Test')->pluck('id')->all();

    \App\Models\ActivityLog::create([
        'user_id' => $this->admin->id,
        'action' => 'Imported Permanent Data',
        'description' => json_encode(['created' => count($created), 'skipped' => 0, 'created_ids' => $created]),
    ]);
    $log = \App\Models\ActivityLog::where('action', 'Imported Permanent Data')->first();

    $response = $this->actingAs($this->staff)->delete(route('permanent.import.undo', $log->id));
    $response->assertRedirect();
    expect(PlantillaRecord::whereIn('id', $created)->count())->toBe(0);
});
