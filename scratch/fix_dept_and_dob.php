<?php

function fixDeptAndDob($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (!in_array($ext, ['php'])) continue;

            $path = $file->getPathname();
            $content = file_get_contents($path);
            $original = $content;

            // Casual Office / Dept
            $content = str_replace('Office / Department', 'OFFICE', $content);
            $content = str_replace('OFFICE / DEPARTMENT', 'OFFICE', $content);
            $content = str_replace('Office/Dept', 'OFFICE', $content);
            $content = str_replace('OFFICE/DEPT', 'OFFICE', $content);
            
            // Date of Birth -> Birthday
            $content = str_replace('Date of Birth', 'Birthday', $content);
            $content = str_replace('DATE OF BIRTH', 'BIRTHDAY', $content);

            if ($original !== $content) {
                file_put_contents($path, $content);
                echo "Replaced strings in $path\n";
            }
        }
    }
}

fixDeptAndDob('c:/laragon/www/Human-Resource-Management-Data-System/resources/views');
fixDeptAndDob('c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers');
fixDeptAndDob('c:/laragon/www/Human-Resource-Management-Data-System/app/Imports');
fixDeptAndDob('c:/laragon/www/Human-Resource-Management-Data-System/app/Exports');
