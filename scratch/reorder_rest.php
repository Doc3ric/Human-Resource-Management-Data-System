<?php
$files = [
    'resources/views/job-orders/index.blade.php',
    'resources/views/all-data/index.blade.php',
    'resources/views/plantilla/index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    preg_match('/<form[^>]*class="[^"]*filter[^"]*"[^>]*>.*?<\/form>/s', $content, $matches);
    if (!$matches) {
        preg_match('/<form[^>]*id="[^"]*-search-form"[^>]*>.*?<\/form>/s', $content, $matches);
    }
    
    if (!$matches) {
        // try to match <div class="...filter..."> inside a form?
        preg_match('/<form[^>]*>.*?class="[^"]*filter[^"]*".*?<\/form>/s', $content, $matches);
    }
    
    if (!$matches) {
        echo "No form found for $file\n";
        continue;
    }
    
    $formHtml = $matches[0];
    
    // For job-orders, submit button is '<button type="submit" class="jo-btn jo-btn-primary"'
    $submitPos = strpos($formHtml, '<button type="submit"');
    if ($submitPos === false) $submitPos = strpos($formHtml, '<button class="jo-btn');
    if ($submitPos === false) $submitPos = strpos($formHtml, '<button class="ad-btn');
    if ($submitPos === false) $submitPos = strpos($formHtml, '<button class="plan-btn');
    
    if ($submitPos !== false) {
        preg_match('/<div class="[^"]*filter[^"]*">/', $formHtml, $divMatch, PREG_OFFSET_CAPTURE);
        if ($divMatch) {
            $divStart = $divMatch[0][1] + strlen($divMatch[0][0]);
            
            $inputsStr = substr($formHtml, $divStart, $submitPos - $divStart);
            $buttonsStr = substr($formHtml, $submitPos);
            
            $buttonsStr = preg_replace('/<\/div>\s*<\/form>$/', '', $buttonsStr);
            $buttonsStr = preg_replace('/<\/form>$/', '', $buttonsStr);
            
            $newFormHtml = substr($formHtml, 0, $divStart) . "\n" .
                '<div style="width: 100%; display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; align-items: center;">' . "\n" .
                $buttonsStr . "\n" .
                '</div>' . "\n" .
                '<div style="width: 100%; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">' . "\n" .
                $inputsStr . "\n" .
                '</div>' . "\n" .
                '</div></form>';
                
            $content = str_replace($formHtml, $newFormHtml, $content);
            file_put_contents($file, $content);
            echo "Updated $file\n";
        } else {
            echo "divMatch failed for $file\n";
        }
    } else {
        echo "submitPos failed for $file\n";
    }
}
