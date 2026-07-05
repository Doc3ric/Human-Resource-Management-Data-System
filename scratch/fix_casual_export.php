<?php

$bladeFile = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/casual/index.blade.php';
$bladeContent = file_get_contents($bladeFile);

$bladeArray = "
                                \$exportColumns = [
                                    'office' => 'Office',
                                    'item_no_old' => 'Item No. (Old)',
                                    'item_no_new' => 'Item No. (New)',
                                    'position_title' => 'Position Title',
                                    'is_vacant' => 'Vacant?',
                                    'last_name' => 'Last Name',
                                    'first_name' => 'First Name',
                                    'middle_initial' => 'Middle Initial',
                                    'name_extension' => 'Name Extension',
                                    'legislative_district' => 'Legislative District',
                                    'sg_current' => 'Salary Grade (Current)',
                                    'step_current' => 'Step (Current)',
                                    'salary_current' => 'Annual Salary (Current)',
                                    'sg_proposed' => 'Salary Grade (Proposed)',
                                    'step_proposed' => 'Step (Proposed)',
                                    'salary_proposed' => 'Annual Salary (Proposed)',
                                    'increase_decrease' => 'Increase / Decrease',
                                    'previous_rate' => 'Previous Rate',
                                    'current_rate' => 'Current Rate (Monthly)',
                                    'gender' => 'Gender',
                                    'birthdate' => 'Birthdate',
                                    'first_day_of_service' => 'First Day of Service',
                                    'eligibility' => 'Eligibility',
                                    'annotation' => 'Annotation',
                                    'employee_code' => 'Employee Code',
                                    'address' => 'Address',
                                    'solo_parent' => 'Solo Parent',
                                    'ip_community_membership' => 'IP Community Membership'
                                ];";

$bladeContent = preg_replace('/\$exportColumns\s*=\s*\[.*?\];/s', ltrim($bladeArray), $bladeContent);
file_put_contents($bladeFile, $bladeContent);


$controllerFile = 'c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/CasualController.php';
$controllerContent = file_get_contents($controllerFile);

$controllerArray = "
        \$allCols = [
            'office' => 'OFFICE',
            'item_no_old' => 'ITEM NO. (OLD)',
            'item_no_new' => 'ITEM NO. (NEW)',
            'position_title' => 'POSITION TITLE',
            'is_vacant' => 'VACANT?',
            'last_name' => 'LAST NAME',
            'first_name' => 'FIRST NAME',
            'middle_initial' => 'MIDDLE INITIAL',
            'name_extension' => 'NAME EXTENSION',
            'legislative_district' => 'LEGISLATIVE DISTRICT',
            'sg_current' => 'SALARY GRADE (CURRENT)',
            'step_current' => 'STEP (CURRENT)',
            'salary_current' => 'ANNUAL SALARY (CURRENT)',
            'sg_proposed' => 'SALARY GRADE (PROPOSED)',
            'step_proposed' => 'STEP (PROPOSED)',
            'salary_proposed' => 'ANNUAL SALARY (PROPOSED)',
            'increase_decrease' => 'INCREASE / DECREASE',
            'previous_rate' => 'PREVIOUS RATE',
            'current_rate' => 'CURRENT RATE (MONTHLY)',
            'gender' => 'GENDER',
            'birthdate' => 'BIRTHDATE',
            'first_day_of_service' => 'FIRST DAY OF SERVICE',
            'eligibility' => 'ELIGIBILITY',
            'annotation' => 'ANNOTATION',
            'employee_code' => 'EMPLOYEE CODE',
            'address' => 'ADDRESS',
            'solo_parent' => 'SOLO PARENT',
            'ip_community_membership' => 'IP COMMUNITY MEMBERSHIP'
        ];";

$controllerContent = preg_replace('/\$allCols\s*=\s*\[.*?\];/s', ltrim($controllerArray), $controllerContent);

$switchReplacement = "
                    case 'office': \$val = \$cas->office; break;
                    case 'item_no_old': \$val = \$cas->item_no_old; break;
                    case 'item_no_new': \$val = \$cas->item_no_new; break;
                    case 'position_title': \$val = \$cas->position_title; break;
                    case 'is_vacant': \$val = \$cas->is_vacant ? 'Y' : 'N'; break;
                    case 'last_name': \$val = strtoupper(\$cas->last_name); break;
                    case 'first_name': \$val = \$cas->first_name; break;
                    case 'middle_initial': \$val = \$cas->middle_initial; break;
                    case 'name_extension': \$val = \$cas->name_extension; break;
                    case 'legislative_district': \$val = \$cas->legislative_district; break;
                    case 'sg_current': \$val = \$cas->sg_current; break;
                    case 'step_current': \$val = \$cas->step_current; break;
                    case 'salary_current': \$val = \$cas->salary_current; break;
                    case 'sg_proposed': \$val = \$cas->sg_proposed; break;
                    case 'step_proposed': \$val = \$cas->step_proposed; break;
                    case 'salary_proposed': \$val = \$cas->salary_proposed; break;
                    case 'increase_decrease': \$val = \$cas->increase_decrease; break;
                    case 'previous_rate': \$val = \$cas->previous_rate; break;
                    case 'current_rate': \$val = \$cas->current_rate; break;
                    case 'gender': \$val = \$cas->gender; break;
                    case 'birthdate': \$val = \$cas->birthdate ? \$cas->birthdate->format('Y-m-d') : ''; break;
                    case 'first_day_of_service': \$val = \$cas->first_day_of_service ? \$cas->first_day_of_service->format('Y-m-d') : ''; break;
                    case 'eligibility': \$val = \$cas->eligibility; break;
                    case 'annotation': \$val = \$cas->annotation; break;
                    case 'employee_code': \$val = \$cas->employee_code; break;
                    case 'address': \$val = \$cas->address; break;
                    case 'solo_parent': \$val = \$cas->solo_parent ? 'Y' : 'N'; break;
                    case 'ip_community_membership': \$val = \$cas->ip_community_membership; break;
";

$controllerContent = preg_replace('/case \'office\':.*?case \'employee_code\':\s*\$val\s*=\s*\$cas->employee_code;\s*break;/s', trim($switchReplacement), $controllerContent);

file_put_contents($controllerFile, $controllerContent);
echo "Casual updated.\n";
