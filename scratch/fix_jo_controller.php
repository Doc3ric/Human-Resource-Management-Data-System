<?php

$file = 'c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/JobOrderController.php';
$content = file_get_contents($file);

$controllerArray = "
        \$allCols = [
            'charges'                  => 'CHARGES',
            'last_name'                => 'LAST NAME',
            'first_name'               => 'FIRST NAME',
            'middle_initial'           => 'MIDDLE INITIAL',
            'name_extension'           => 'NAME EXTENSION',
            'position_title'           => 'POSITION TITLE',
            'nature_of_work'           => 'NATURE OF WORK',
            'nature_of_work_detail'    => 'NATURE OF WORK DETAIL',
            'office'                   => 'OFFICE',
            'rate_per_day'             => 'RATE PER DAY',
            'first_day_of_service'     => 'FIRST DAY OF SERVICE',
            'birthdate'                => 'BIRTHDATE',
            'civil_status'             => 'CIVIL STATUS',
            'address'                  => 'ADDRESS',
            'eligibility'              => 'ELIGIBILITY',
            'gender'                   => 'GENDER',
            'level'                    => 'LEVEL',
            'first_level_eligibility'  => 'FIRST LEVEL ELIGIBILITY',
            'second_level_eligibility' => 'SECOND LEVEL ELIGIBILITY',
            'ip_community_membership'  => 'IP COMMUNITY MEMBERSHIP',
            'solo_parent'              => 'SOLO PARENT',
            'reemployment'             => 'REEMPLOYMENT',
            'remarks'                  => 'REMARKS',
            'employee_code'            => 'EMPLOYEE CODE',
        ];";

$content = preg_replace('/\$allCols\s*=\s*\[.*?\];/s', ltrim($controllerArray), $content);

// Now update the switch case
$switchReplacement = "
                    case 'charges': \$val = \$jo->charges; break;
                    case 'last_name': \$val = strtoupper(\$jo->last_name); break;
                    case 'first_name': \$val = \$jo->first_name; break;
                    case 'middle_initial': \$val = \$jo->middle_initial; break;
                    case 'name_extension': \$val = \$jo->name_extension; break;
                    case 'position_title': \$val = \$jo->position_title; break;
                    case 'nature_of_work': \$val = \$jo->nature_of_work; break;
                    case 'nature_of_work_detail': \$val = \$jo->nature_of_work_detail; break;
                    case 'office': \$val = \$jo->office; break;
                    case 'rate_per_day': \$val = \$jo->rate_per_day; break;
                    case 'first_day_of_service': \$val = \$jo->first_day_of_service ? \$jo->first_day_of_service->format('Y-m-d') : ''; break;
                    case 'birthdate': \$val = \$jo->birthdate ? \$jo->birthdate->format('Y-m-d') : ''; break;
                    case 'civil_status': \$val = \$jo->civil_status; break;
                    case 'address': \$val = \$jo->address; break;
                    case 'eligibility': \$val = \$jo->eligibility; break;
                    case 'gender': \$val = \$jo->gender; break;
                    case 'level': \$val = \$jo->level; break;
                    case 'first_level_eligibility': \$val = \$jo->first_level_eligibility ? 'Y' : 'N'; break;
                    case 'second_level_eligibility': \$val = \$jo->second_level_eligibility ? 'Y' : 'N'; break;
                    case 'ip_community_membership': \$val = \$jo->ip_community_membership; break;
                    case 'solo_parent': \$val = \$jo->solo_parent ? 'Y' : 'N'; break;
                    case 'reemployment': \$val = \$jo->reemployment ? 'Y' : 'N'; break;
                    case 'remarks': \$val = \$jo->remarks; break;
                    case 'employee_code': \$val = \$jo->employee_code; break;
";

$content = preg_replace('/case \'charges\':.*?case \'employee_code\':\s*\$val\s*=\s*\$jo->employee_code;\s*break;/s', trim($switchReplacement), $content);

// Also fix the rate_day -> rate_per_day check
$content = str_replace("if (\$key === 'rate_day') {", "if (\$key === 'rate_per_day') {", $content);

file_put_contents($file, $content);
echo "Controller updated.\n";
