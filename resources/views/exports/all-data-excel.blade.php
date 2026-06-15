<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body>
    {{-- Header row (rendered as row 3 after the two title rows injected by AfterSheet) --}}
    {{-- Headers match ImportController::legacyHeaderMap() exactly for seamless re-import --}}
    <table>
        <thead>
            <tr>
                @if(empty($columns) || in_array('organizational_unit', $columns))<th>ORGANIZATIONAL UNIT</th>@endif
                @if(empty($columns) || in_array('item', $columns))<th>ITEM</th>@endif
                @if(empty($columns) || in_array('position_title', $columns))<th>POSITION TITLE</th>@endif
                @if(empty($columns) || in_array('salary_grade', $columns))<th>SALARY GRADE</th>@endif
                @if(empty($columns) || in_array('authorized_annual_salary', $columns))<th>AUTHORIZED ANNUAL SALARY</th>@endif
                @if(empty($columns) || in_array('actual_annual_salary', $columns))<th>ACTUAL ANNUAL SALARY</th>@endif
                @if(empty($columns) || in_array('step', $columns))<th>STEP</th>@endif
                @if(empty($columns) || in_array('area_code', $columns))<th>AREA CODE</th>@endif
                @if(empty($columns) || in_array('area_type', $columns))<th>AREA TYPE</th>@endif
                @if(empty($columns) || in_array('level', $columns))<th>LEVEL</th>@endif
                @if(empty($columns) || in_array('last_name', $columns))<th>LAST NAME</th>@endif
                @if(empty($columns) || in_array('first_name', $columns))<th>FIRST NAME</th>@endif
                @if(empty($columns) || in_array('middle_name', $columns))<th>MIDDLE NAME</th>@endif
                @if(empty($columns) || in_array('sex', $columns))<th>SEX</th>@endif
                @if(empty($columns) || in_array('religion', $columns))<th>RELIGION</th>@endif
                @if(empty($columns) || in_array('date_of_birth', $columns))<th>DATE OF BIRTH</th>@endif
                @if(empty($columns) || in_array('tin', $columns))<th>TIN</th>@endif
                @if(empty($columns) || in_array('date_original_appointment', $columns))<th>DATE OF ORIGINAL APPOINTMENT</th>@endif
                @if(empty($columns) || in_array('date_last_promotion', $columns))<th>DATE OF LAST PROMOTION-APPOINTMENT</th>@endif
                @if(empty($columns) || in_array('status', $columns))<th>STATUS</th>@endif
                @if(empty($columns) || in_array('civil_service_eligibility', $columns))<th>CIVIL SERVICE ELIGIBILITY</th>@endif
                @if(empty($columns) || in_array('pwd', $columns))<th>PWD</th>@endif
                @if(empty($columns) || in_array('admin_charges', $columns))<th>COMMENT/ ANNOTATION</th>@endif
                @if(empty($columns) || in_array('nature_of_separation', $columns))<th>TERMINATION</th>@endif
                @if(empty($columns) || in_array('indigenous_people', $columns))<th>INDIGENOUS PEOPLE</th>@endif
                @if(empty($columns) || in_array('solo_parent', $columns))<th>SOLO PARENT</th>@endif
                @if(empty($columns) || in_array('abolished', $columns))<th>ABOLISHED</th>@endif
                @if(empty($columns) || in_array('dissolved', $columns))<th>DISSOLVED</th>@endif
                @if(empty($columns) || in_array('gsis_bp_number', $columns))<th>GSIS BP NUMBER</th>@endif
                @if(empty($columns) || in_array('position_classification', $columns))<th>POSITION CLASSIFICATION</th>@endif
                @if(empty($columns) || in_array('employee_code', $columns))<th>EMPLOYEE NO.</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $r)
                <tr>
                    @if(empty($columns) || in_array('organizational_unit', $columns))<td>{{ $r->organizational_unit }}</td>@endif
                    @if(empty($columns) || in_array('item', $columns))<td>{{ $r->item }}</td>@endif
                    @if(empty($columns) || in_array('position_title', $columns))<td>{{ $r->position_title }}</td>@endif
                    {{-- Plain number — no "SG-" prefix — so importer can parse it directly --}}
                    @if(empty($columns) || in_array('salary_grade', $columns))<td>{{ $r->salary_grade }}</td>@endif
                    {{-- Raw numeric values without formatting commas so importer reads them cleanly --}}
                    @if(empty($columns) || in_array('authorized_annual_salary', $columns))<td>{{ $r->authorized_annual_salary }}</td>@endif
                    @if(empty($columns) || in_array('actual_annual_salary', $columns))<td>{{ $r->actual_annual_salary }}</td>@endif
                    @if(empty($columns) || in_array('step', $columns))<td>{{ $r->step }}</td>@endif
                    @if(empty($columns) || in_array('area_code', $columns))<td>{{ $r->area_code }}</td>@endif
                    @if(empty($columns) || in_array('area_type', $columns))<td>{{ $r->area_type }}</td>@endif
                    @if(empty($columns) || in_array('level', $columns))<td>{{ $r->level }}</td>@endif
                    @if(empty($columns) || in_array('last_name', $columns))<td>{{ $r->is_vacant ? 'VACANT' : strtoupper($r->last_name ?? '') }}</td>@endif
                    @if(empty($columns) || in_array('first_name', $columns))<td>{{ $r->first_name }}</td>@endif
                    @if(empty($columns) || in_array('middle_name', $columns))<td>{{ $r->middle_name }}</td>@endif
                    @if(empty($columns) || in_array('sex', $columns))<td>{{ $r->sex }}</td>@endif
                    @if(empty($columns) || in_array('religion', $columns))<td>{{ $r->religion }}</td>@endif
                    {{-- Y-m-d format so ImportController::parseDate() reads it correctly --}}
                    @if(empty($columns) || in_array('date_of_birth', $columns))<td>{{ $r->date_of_birth?->format('Y-m-d') }}</td>@endif
                    @if(empty($columns) || in_array('tin', $columns))<td>{{ $r->tin }}</td>@endif
                    @if(empty($columns) || in_array('date_original_appointment', $columns))<td>{{ $r->date_original_appointment?->format('Y-m-d') }}</td>@endif
                    @if(empty($columns) || in_array('date_last_promotion', $columns))<td>{{ $r->date_last_promotion?->format('Y-m-d') }}</td>@endif
                    @if(empty($columns) || in_array('status', $columns))<td>{{ $r->employment_status }}</td>@endif
                    @if(empty($columns) || in_array('civil_service_eligibility', $columns))<td>{{ $r->civil_service_eligibility }}</td>@endif
                    {{-- "Y" / "N" so importer (=== 'Y') works correctly; old export used "YES" which broke it --}}
                    @if(empty($columns) || in_array('pwd', $columns))<td>{{ $r->is_pwd ? 'Y' : 'N' }}</td>@endif
                    {{-- Map admin_charges column to comment_annotation which the legacy importer knows --}}
                    @if(empty($columns) || in_array('admin_charges', $columns))<td>{{ $r->comment_annotation }}</td>@endif
                    @if(empty($columns) || in_array('nature_of_separation', $columns))<td>{{ $r->nature_of_separation }}</td>@endif
                    @if(empty($columns) || in_array('indigenous_people', $columns))<td>{{ $r->indigenous_people }}</td>@endif
                    @if(empty($columns) || in_array('solo_parent', $columns))<td>{{ $r->solo_parent }}</td>@endif
                    @if(empty($columns) || in_array('abolished', $columns))<td>{{ $r->abolished ? 'Y' : 'N' }}</td>@endif
                    @if(empty($columns) || in_array('dissolved', $columns))<td>{{ $r->dissolved ? 'Y' : 'N' }}</td>@endif
                    @if(empty($columns) || in_array('gsis_bp_number', $columns))<td>{{ $r->gsis_bp_number }}</td>@endif
                    @if(empty($columns) || in_array('position_classification', $columns))<td>{{ $r->position_classification }}</td>@endif
                    @if(empty($columns) || in_array('employee_code', $columns))<td>{{ $r->employee_code }}</td>@endif
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>