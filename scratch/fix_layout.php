<?php
$files = [
    'resources/views/casual/index.blade.php',
    'resources/views/job-orders/index.blade.php',
    'resources/views/all-data/index.blade.php',
    'resources/views/permanent/index.blade.php',
    'resources/views/plantilla/index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Find the whole <form> element
    preg_match('/<form[^>]*class="[^"]*filter[^"]*"[^>]*>.*?<\/form>/s', $content, $matches);
    if (!$matches) {
        preg_match('/<form[^>]*id="[^"]*-search-form"[^>]*>.*?<\/form>/s', $content, $matches);
    }
    if (!$matches) continue;
    
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
    
    // The previous structure might have had divs, we rebuild it.
    // Replace the form's inner HTML (everything between <form ...> and </form>)
    
    // The <form ...> tag
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
    $content = str_replace('ALL REGULAR Data', 'ALL Regular Data', $content);
    $content = str_replace('EVERY</strong> REGULAR', 'EVERY</strong> Regular', $content);
    
    file_put_contents($file, $content);
    echo "Fixed layout for $file\n";
}

// Also rename in layouts
$layout = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/layouts/dashboard-app.blade.php';
if(file_exists($layout)) {
    $c = file_get_contents($layout);
    $c = str_replace('<span>REGULAR</span>', '<span>Regular</span>', $c);
    file_put_contents($layout, $c);
    echo "Fixed layout $layout\n";
}
$componentsLayout = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/components/dashboard-app.blade.php';
if(file_exists($componentsLayout)) {
    $c = file_get_contents($componentsLayout);
    $c = str_replace('<span>REGULAR</span>', '<span>Regular</span>', $c);
    file_put_contents($componentsLayout, $c);
    echo "Fixed componentsLayout $componentsLayout\n";
}
$dashboard = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/dashboard.blade.php';
if(file_exists($dashboard)) {
    $c = file_get_contents($dashboard);
    $c = str_replace('<span>REGULAR</span>', '<span>Regular</span>', $c);
    file_put_contents($dashboard, $c);
    echo "Fixed dashboard $dashboard\n";
}
