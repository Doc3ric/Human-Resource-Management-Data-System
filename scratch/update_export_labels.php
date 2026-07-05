<?php

function fixControllerArrays($filePath) {
    if (!file_exists($filePath)) return;
    $content = file_get_contents($filePath);
    $original = $content;

    // We'll replace the right hand side values in arrays
    $content = preg_replace("/=>\s*'OFFICE',/", "=> 'OFFICE ASSIGNED',", $content);
    $content = preg_replace("/=>\s*'GENDER',/", "=> 'SEX',", $content);
    $content = preg_replace("/=>\s*'BIRTHDATE',/", "=> 'BIRTHDAY',", $content);
    $content = preg_replace("/=>\s*'MIDDLE INITIAL',/", "=> 'MIDDLE NAME',", $content);
    $content = preg_replace("/=>\s*'NAME EXTENSION',/", "=> 'SUFFIX',", $content);

    // Also handle Title Case ones from blade
    $content = preg_replace("/=>\s*'Office',/", "=> 'Office Assigned',", $content);
    $content = preg_replace("/=>\s*'Gender',/", "=> 'Sex',", $content);
    $content = preg_replace("/=>\s*'Birthdate',/", "=> 'Birthday',", $content);
    $content = preg_replace("/=>\s*'Middle Initial',/", "=> 'Middle Name',", $content);
    $content = preg_replace("/=>\s*'Name Extension',/", "=> 'Suffix',", $content);

    if ($original !== $content) {
        file_put_contents($filePath, $content);
        echo "Updated export labels in $filePath\n";
    }
}

$files = [
    'app/Http/Controllers/JobOrderController.php',
    'app/Http/Controllers/CasualController.php',
    'app/Http/Controllers/PlantillaController.php',
    'app/Http/Controllers/AllDataController.php',
    'resources/views/job-orders/index.blade.php',
    'resources/views/casual/index.blade.php',
    'resources/views/plantilla/index.blade.php',
    'resources/views/all-data/index.blade.php',
];

foreach ($files as $file) {
    fixControllerArrays('c:/laragon/www/Human-Resource-Management-Data-System/' . $file);
}
