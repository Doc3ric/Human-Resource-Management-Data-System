<?php
$files = [
    'resources/views/all-data/index.blade.php',
    'resources/views/plantilla/index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Find form
    preg_match('/<form[^>]*class="[^"]*filter[^"]*"[^>]*>.*?<\/form>/s', $content, $matches);
    if (!$matches) {
        preg_match('/<form[^>]*id="[^"]*-search-form"[^>]*>.*?<\/form>/s', $content, $matches);
    }
    if (!$matches) {
        preg_match('/<form[^>]*>.*?class="[^"]*filter[^"]*".*?<\/form>/s', $content, $matches);
    }
    
    if (!$matches) {
        echo "No form found in $file\n";
        continue;
    }
    
    $formHtml = $matches[0];
    
    // Extract inputs
    preg_match_all('/<input[^>]*type="text"[^>]*>/s', $formHtml, $inputs);
    preg_match_all('/<select[^>]*>.*?<\/select>/s', $formHtml, $selects);
    
    // Extract buttons
    $btnRegexes = [
        'search' => '/<button[^>]*type="submit"[^>]*>.*?<\/button>/s',
        'reset' => '/<a[^>]*class="[^"]*reset[^"]*"[^>]*>.*?<\/a>/s',
        'add' => '/@if\([^)]*isSuperAdmin[^)]*\)\s*<a[^>]*create[^>]*>.*?<\/a>\s*(?:@endif)?/si',
        'import' => '/(?:@if\([^)]*isSuperAdmin[^)]*\)\s*)?<button[^>]*class="[^"]*import[^"]*"[^>]*>.*?<\/button>\s*(?:@endif)?/si',
        'history' => '/<a[^>]*import\.history[^>]*>.*?<\/a>/si',
        'export' => '/(?:<button[^>]*exportModal[^>]*>.*?<\/button>|<a[^>]*export\.excel[^>]*>.*?<\/a>)/si',
        'select_multiple' => '/@if\([^)]*isSuperAdmin[^)]*\)\s*<button[^>]*toggle-select-multiple[^>]*>.*?<\/button>\s*@endif/si',
        'delete_all' => '/@if\([^)]*isSuperAdmin[^)]*\)\s*<button[^>]*delete-all-btn[^>]*>.*?<\/button>\s*@endif/si',
    ];
    
    $extractedBtns = [];
    $tempFormContent = $formHtml;
    foreach ($btnRegexes as $key => $regex) {
        if (preg_match($regex, $tempFormContent, $match)) {
            $extractedBtns[$key] = trim($match[0]);
            $tempFormContent = str_replace($match[0], '', $tempFormContent);
        } else {
            $extractedBtns[$key] = '';
        }
    }
    
    // For Plantilla, it has different buttons:
    // Generate Vacancy Report etc.
    if (strpos($file, 'plantilla') !== false) {
        preg_match('/(?:<a[^>]*export\.pdf[^>]*>.*?<\/a>)/si', $tempFormContent, $pdfExportMatch);
        if ($pdfExportMatch) {
            $extractedBtns['export'] .= ' ' . $pdfExportMatch[0];
            $tempFormContent = str_replace($pdfExportMatch[0], '', $tempFormContent);
        }
        preg_match('/<button[^>]*class="plan-btn"[^>]*data-bs-target="#exportModal"[^>]*>.*?<\/button>/si', $tempFormContent, $planExportMatch);
        if ($planExportMatch) {
            $extractedBtns['export'] .= ' ' . $planExportMatch[0];
        }
    }
    
    preg_match('/<form[^>]*>/', $formHtml, $formStartMatch);
    $formStart = $formStartMatch[0];
    
    $newInner = '
        <div class="cas-filter" style="display: flex; flex-direction: column; gap: 12px; align-items: stretch; padding: 14px 18px; margin-bottom: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, .04);">
            <!-- Top Row: Inputs + Search + Reset -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                ' . implode("\n                ", $inputs[0]) . '
                ' . implode("\n                ", $selects[0]) . '
                ' . $extractedBtns['search'] . '
                ' . $extractedBtns['reset'] . '
            </div>
            
            <!-- Bottom Row: Actions (Far Left) -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                ' . $extractedBtns['add'] . '
                ' . $extractedBtns['import'] . '
                ' . $extractedBtns['history'] . '
                ' . $extractedBtns['export'] . '
                ' . $extractedBtns['select_multiple'] . '
                ' . $extractedBtns['delete_all'] . '
            </div>
        </div>
    ';
    
    $newFormHtml = $formStart . $newInner . '</form>';
    $content = str_replace($formHtml, $newFormHtml, $content);
    
    // Rename REGULAR to Regular
    $content = str_replace('>REGULAR<', '>Regular<', $content);
    $content = str_replace('REGULAR Employees', 'Regular Employees', $content);
    $content = str_replace('All REGULAR employees', 'All Regular employees', $content);
    
    file_put_contents($file, $content);
    echo "Fixed layout for $file\n";
}
