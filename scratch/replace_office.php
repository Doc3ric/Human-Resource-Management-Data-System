<?php

function replaceOfficeAssigned($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (!in_array($ext, ['php'])) continue;

            $path = $file->getPathname();
            $content = file_get_contents($path);
            $original = $content;

            // Replace exact matches
            $content = str_replace('OFFICE ASSIGNED', 'OFFICE', $content);
            $content = str_replace('Office Assigned', 'Office', $content);
            
            // Also if there's any remaining "Organizational Unit" or "Charges/Office" replace to "OFFICE" or "Office" where appropriate
            $content = preg_replace('/>\s*Organizational Unit\s*</i', '>OFFICE<', $content);
            $content = preg_replace('/>\s*Charges\/Office\s*</i', '>OFFICE<', $content);

            if ($original !== $content) {
                file_put_contents($path, $content);
                echo "Replaced OFFICE ASSIGNED in $path\n";
            }
        }
    }
}

replaceOfficeAssigned('c:/laragon/www/Human-Resource-Management-Data-System/resources/views');
replaceOfficeAssigned('c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers');
replaceOfficeAssigned('c:/laragon/www/Human-Resource-Management-Data-System/app/Imports');
replaceOfficeAssigned('c:/laragon/www/Human-Resource-Management-Data-System/app/Exports');
