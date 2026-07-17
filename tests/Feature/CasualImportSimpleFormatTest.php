<?php

use App\Imports\CasualImport;
use App\Models\CasualEmployee;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Regression coverage for two bugs found in CasualImport:
 *  1. detectFormat() matched the literal substring "ITEM OLD"/"ITEM NEW",
 *     which never appears in the real template header ("ITEM NO. (OLD)"),
 *     so every file from CasualController::downloadTemplate() was silently
 *     misrouted into the Plantilla parser and lost most of its columns.
 *  2. importSimpleFormat()'s $casualData array was missing item_no_old,
 *     sg_proposed, step_proposed, salary_proposed, increase_decrease and
 *     previous_rate; mapped ANNUAL SALARY (PROPOSED) into base_salary_amount
 *     instead of CURRENT RATE (Monthly); mapped FIRST DAY OF SERVICE into
 *     date_original_appointment instead of first_day_of_service; and called
 *     the non-existent $this->sex() instead of $this->gender().
 */
function buildCasualTemplateFile(array $row): string
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'CASUAL EMPLOYEES IMPORT TEMPLATE — CSC Plantilla System');
    $headers = [
        'OFFICE', 'ITEM NO. (OLD)', 'ITEM NO. (NEW)', 'POSITION TITLE', 'LAST NAME', 'FIRST NAME',
        'MIDDLE NAME', 'NAME EXT (Jr./Sr.)', 'VACANT? (Y=Yes)', 'SG (CURRENT)', 'STEP (CURRENT)',
        'ANNUAL SALARY (CURRENT)', 'SG (PROPOSED)', 'STEP (PROPOSED)', 'ANNUAL SALARY (PROPOSED)',
        'INCREASE/DECREASE', 'PREVIOUS RATE (Monthly)', 'CURRENT RATE (Monthly)', 'SEX (M/F)',
        'BIRTHDATE (YYYY-MM-DD)', 'FIRST DAY OF SERVICE', 'ELIGIBILITY',
    ];
    foreach ($headers as $i => $h) {
        $sheet->setCellValueByColumnAndRow($i + 1, 2, $h);
    }
    foreach ($row as $i => $v) {
        $sheet->setCellValueByColumnAndRow($i + 1, 3, $v);
    }

    $path = sys_get_temp_dir() . '/casual_test_' . uniqid() . '.xlsx';
    (new Xlsx($spreadsheet))->save($path);
    return $path;
}

test('a template file is detected as simple format and every column is mapped to the right field', function () {
    $path = buildCasualTemplateFile([
        "PROVINCIAL VICE GOVERNOR'S OFFICE", '1', '1', 'Administrative Aide I (Utility Worker I)',
        'Bongcales', 'Karola Jun', 'T.', '', 'N', '1', '1', '168732',
        '2', '1', '175608', '6876', '14061', '14634', 'F', '1990-05-20', '2015-01-02', 'No Eligibility',
    ]);

    $import = new CasualImport();
    Excel::import($import, $path);
    @unlink($path);

    expect($import->imported)->toBe(1);
    expect(CasualEmployee::count())->toBe(1);

    $c = CasualEmployee::first();
    expect($c->office_department)->toBe("PROVINCIAL VICE GOVERNOR'S OFFICE");
    expect($c->item_no_old)->toBe('1');
    expect($c->item_no_new)->toBe('1');
    expect($c->position_title)->toBe('ADMINISTRATIVE AIDE I (UTILITY WORKER I)'); // PlantillaRecord::setPositionTitleAttribute() uppercases on save
    expect($c->last_name)->toBe('Bongcales');
    expect($c->first_name)->toBe('Karola Jun');
    expect($c->middle_name)->toBe('T.');
    expect((int) $c->salary_grade)->toBe(1);
    expect((int) $c->step)->toBe(1);
    expect((float) $c->authorized_annual_salary)->toBe(168732.0);
    expect((int) $c->sg_proposed)->toBe(2);
    expect((int) $c->step_proposed)->toBe(1);
    expect((float) $c->salary_proposed)->toBe(175608.0);
    expect((float) $c->increase_decrease)->toBe(6876.0);
    expect((float) $c->previous_rate)->toBe(14061.0);
    expect((float) $c->base_salary_amount)->toBe(14634.0);
    expect($c->sex)->toBe('F');
    expect($c->date_of_birth->format('Y-m-d'))->toBe('1990-05-20');
    expect($c->first_day_of_service->format('Y-m-d'))->toBe('2015-01-02');
    expect($c->civil_service_eligibility)->toBe('No Eligibility');
});

test('re-importing the same employee updates the existing record instead of creating a duplicate', function () {
    $path = buildCasualTemplateFile([
        "PROVINCIAL VICE GOVERNOR'S OFFICE", '1', '1', 'Administrative Aide I (Utility Worker I)',
        'Bongcales', 'Karola Jun', 'T.', '', 'N', '1', '1', '168732',
        '1', '1', '168732', '0', '14061', '14061', 'F', '1990-05-20', '2015-01-02', 'No Eligibility',
    ]);
    Excel::import(new CasualImport(), $path);

    $path2 = buildCasualTemplateFile([
        "PROVINCIAL VICE GOVERNOR'S OFFICE", '1', '1', 'Administrative Aide I (Utility Worker I)',
        'Bongcales', 'Karola Jun', 'T.', '', 'N', '2', '1', '175608',
        '2', '1', '175608', '0', '14634', '14634', 'F', '1990-05-20', '2015-01-02', 'No Eligibility',
    ]);
    Excel::import(new CasualImport(), $path2);
    @unlink($path);
    @unlink($path2);

    expect(CasualEmployee::count())->toBe(1);
    expect((int) CasualEmployee::first()->salary_grade)->toBe(2);
});
