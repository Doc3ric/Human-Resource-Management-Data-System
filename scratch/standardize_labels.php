<?php

function standardizeLabels($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $path = $file->getPathname();
            $content = file_get_contents($path);
            $original = $content;

            // Forms & UI Text
            // Office Assigned
            $content = preg_replace('/>\s*Organizational Unit\s*\/?\s*Charges\s*</i', '>OFFICE ASSIGNED<', $content);
            $content = preg_replace('/>\s*Charges\s*\/?\s*Office\s*</i', '>OFFICE ASSIGNED<', $content);
            $content = preg_replace('/>\s*Office\s*</i', '>OFFICE ASSIGNED<', $content);
            $content = preg_replace('/>\s*Office Assigned\s*</i', '>OFFICE ASSIGNED<', $content);
            $content = preg_replace('/>\s*Charges\s*</i', '>OFFICE ASSIGNED<', $content);

            // Sex
            $content = preg_replace('/>\s*Gender\s*\/?\s*Sex \(M\/F\)\s*</i', '>SEX<', $content);
            $content = preg_replace('/>\s*Gender\s*\/?\s*Sex\s*</i', '>SEX<', $content);
            $content = preg_replace('/>\s*Gender\s*</i', '>SEX<', $content);
            $content = preg_replace('/>\s*Sex\s*</i', '>SEX<', $content);

            // Last Name
            $content = preg_replace('/>\s*Family Name\s*</i', '>LAST NAME<', $content);
            $content = preg_replace('/>\s*Surname\s*</i', '>LAST NAME<', $content);
            $content = preg_replace('/>\s*Last Name\s*</i', '>LAST NAME<', $content);

            // Middle Name
            $content = preg_replace('/>\s*M\.I\.\s*</i', '>MIDDLE NAME<', $content);
            $content = preg_replace('/>\s*Middle Initial\s*</i', '>MIDDLE NAME<', $content);
            $content = preg_replace('/>\s*Middle Name\s*</i', '>MIDDLE NAME<', $content);

            // Suffix
            $content = preg_replace('/>\s*Ext\.\s*\/?\s*Name Extension\s*</i', '>SUFFIX<', $content);
            $content = preg_replace('/>\s*Ext\.\s*</i', '>SUFFIX<', $content);
            $content = preg_replace('/>\s*Name Extension\s*</i', '>SUFFIX<', $content);
            $content = preg_replace('/>\s*Suffix\s*</i', '>SUFFIX<', $content);

            // Birthday
            $content = preg_replace('/>\s*Birthdate\s*\/?\s*Date of Birth\s*</i', '>BIRTHDAY<', $content);
            $content = preg_replace('/>\s*Date of Birth\s*</i', '>BIRTHDAY<', $content);
            $content = preg_replace('/>\s*Birthdate\s*</i', '>BIRTHDAY<', $content);
            $content = preg_replace('/>\s*Birthday\s*</i', '>BIRTHDAY<', $content);

            if ($original !== $content) {
                file_put_contents($path, $content);
                echo "Updated labels in $path\n";
            }
        }
    }
}

standardizeLabels('c:/laragon/www/Human-Resource-Management-Data-System/resources/views/job-orders');
standardizeLabels('c:/laragon/www/Human-Resource-Management-Data-System/resources/views/casual');
standardizeLabels('c:/laragon/www/Human-Resource-Management-Data-System/resources/views/plantilla');
standardizeLabels('c:/laragon/www/Human-Resource-Management-Data-System/resources/views/permanent');
standardizeLabels('c:/laragon/www/Human-Resource-Management-Data-System/resources/views/all-data');
