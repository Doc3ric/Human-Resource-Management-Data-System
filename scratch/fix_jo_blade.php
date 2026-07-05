<?php

$file = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/job-orders/index.blade.php';
$content = file_get_contents($file);

// Replace the $exportColumns array in Blade
$bladeArray = "
                                \$exportColumns = [
                                    'charges' => 'Charges',
                                    'last_name' => 'Last Name',
                                    'first_name' => 'First Name',
                                    'middle_initial' => 'Middle Initial',
                                    'name_extension' => 'Name Extension',
                                    'position_title' => 'Position Title',
                                    'nature_of_work' => 'Nature of Work',
                                    'nature_of_work_detail' => 'Nature of Work Detail',
                                    'office' => 'Office',
                                    'rate_per_day' => 'Rate Per Day',
                                    'first_day_of_service' => 'First Day of Service',
                                    'birthdate' => 'Birthdate',
                                    'civil_status' => 'Civil Status',
                                    'address' => 'Address',
                                    'eligibility' => 'Eligibility',
                                    'gender' => 'Gender',
                                    'level' => 'Level',
                                    'first_level_eligibility' => 'First Level Eligibility',
                                    'second_level_eligibility' => 'Second Level Eligibility',
                                    'ip_community_membership' => 'IP Community Membership',
                                    'solo_parent' => 'Solo Parent',
                                    'reemployment' => 'Reemployment',
                                    'remarks' => 'Remarks',
                                    'employee_code' => 'Employee Code'
                                ];";

$content = preg_replace('/\$exportColumns\s*=\s*\[.*?\];/s', ltrim($bladeArray), $content);
file_put_contents($file, $content);
echo "Blade updated.\n";
