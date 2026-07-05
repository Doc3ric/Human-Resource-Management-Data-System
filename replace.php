<?php
$replacements = [
    'organizational_unit' => 'office_department',
    'actual_annual_salary' => 'base_salary_amount',
    'comment_annotation' => 'remarks_annotation',
    "->item" => "->item_no_new",
    "'item'" => "'item_no_new'",
    '"item"' => '"item_no_new"',
    'birthdate' => 'date_of_birth',
    'BIRTHDAY' => 'DATE OF BIRTH',
    'GENDER' => 'SEX',
];

$dirs = ['app', 'resources/views'];

foreach ($dirs as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $path = $file->getPathname();
            $content = file_get_contents($path);
            $original = $content;
            
            foreach ($replacements as $k => $v) {
                $content = str_replace($k, $v, $content);
            }
            
            // special cases for gender
            $content = str_replace("name=\"gender\"", "name=\"sex\"", $content);
            $content = str_replace("id=\"f-gender\"", "id=\"f-sex\"", $content);
            $content = str_replace("for=\"f-gender\"", "for=\"f-sex\"", $content);
            $content = str_replace("->gender", "->sex", $content);
            $content = str_replace("['gender']", "['sex']", $content);
            
            if ($content !== $original) {
                file_put_contents($path, $content);
            }
        }
    }
}
echo "Done.\n";
