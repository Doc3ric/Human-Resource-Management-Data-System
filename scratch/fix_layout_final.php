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
    
    // Replace "Far Left" with "Far Right" if we used the previous script
    $content = str_replace('justify-content: flex-start;', 'justify-content: flex-end;', $content);
    $content = str_replace('<!-- Bottom Row: Actions (Far Left) -->', '<!-- Bottom Row: Actions (Far Right) -->', $content);
    
    // If it's casual, we already fixed it manually in previous step, so just ensure it's flex-end
    if (strpos($file, 'casual') !== false) {
        file_put_contents($file, $content);
        continue;
    }

    // For job-orders, we want to move the buttons from jo-action-row to jo-filter-bar
    if (strpos($file, 'job-orders') !== false) {
        // Find jo-filter-form
        if (preg_match('/<form[^>]*id="jo-filter-form"[^>]*>(.*?)<\/form>/s', $content, $formMatch)) {
            $formInner = $formMatch[1];
            
            // If we haven't already added the bottom row...
            if (strpos($formInner, 'Bottom Row') === false) {
                // Extract action row buttons
                if (preg_match('/<div class="jo-action-row">\s*<div class="jo-title">.*?<\/div>\s*<div style="display:flex;gap:8px;flex-wrap:wrap;">(.*?)<\/div>\s*<\/div>/s', $content, $actionMatch)) {
                    $buttonsHtml = $actionMatch[1];
                    
                    // Delete buttons from action row
                    $content = str_replace($actionMatch[0], '<div class="jo-action-row">' . "\n" . '            <div class="jo-title">' . "\n" . '                <i class="bi bi-file-earmark-person-fill" style="color:#0369a1;"></i>' . "\n" . '                Job Order Inventory' . "\n" . '                <span style="font-size:12px;font-weight:600;color:#64748b;background:#f1f5f9;padding:3px 10px;border-radius:20px;">{{ $total }} record(s)</span>' . "\n" . '            </div>' . "\n" . '        </div>', $content);
                    
                    // Order buttons properly in form
                    // Add Record, Import Excel, Import History, Export Settings, Select Multiple, Delete All Data
                    preg_match('/@if\([^)]*\)\s*<a[^>]*btn-create[^>]*>.*?<\/a>\s*<button[^>]*btn-open-import[^>]*>.*?<\/button>\s*@endif/s', $buttonsHtml, $m1);
                    preg_match('/<a[^>]*import\.history[^>]*>.*?<\/a>/s', $buttonsHtml, $m2);
                    preg_match('/<button[^>]*exportModal[^>]*>.*?<\/button>/s', $buttonsHtml, $m3);
                    preg_match('/@if\([^)]*\)\s*<button[^>]*toggle-select-multiple[^>]*>.*?<\/button>\s*@endif/s', $buttonsHtml, $m4);
                    preg_match('/@if\([^)]*\)\s*<button[^>]*delete-all-btn[^>]*>.*?<\/button>\s*@endif/s', $buttonsHtml, $m5);
                    
                    $orderedBtns = ($m1[0] ?? '') . "\n" . ($m2[0] ?? '') . "\n" . ($m3[0] ?? '') . "\n" . ($m4[0] ?? '') . "\n" . ($m5[0] ?? '');
                    
                    // Inject into form
                    // the form has div.filter-group elements and div.filter-btn-group
                    // We'll wrap the top row and add a bottom row
                    $newFormInner = '
        <div style="display: flex; flex-direction: column; gap: 12px; align-items: stretch;">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                ' . str_replace('<div class="filter-group">', '<div class="filter-group" style="margin-bottom:0;">', $formInner) . '
            </div>
            <!-- Bottom Row: Actions (Far Right) -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 10px 0;">
                ' . $orderedBtns . '
            </div>
        </div>';
                    $content = str_replace($formInner, $newFormInner, $content);
                }
            } else {
               // We already transformed it, just reorder the buttons
               // Actually we just did git restore, so it should be the original format!
            }
        }
        file_put_contents($file, $content);
        continue;
    }
    
    // For all-data and permanent, we need to extract buttons from the form and order them
    if (preg_match('/<form[^>]*>(.*?)<\/form>/s', $content, $formMatch)) {
        $formInner = $formMatch[1];
        if (strpos($formInner, 'Bottom Row') !== false) {
           // We already modified it with fix_layout2.php !
           // Wait, we restored using git checkout, so we are in the original state.
        }
        
        // original state for permanent:
        if (strpos($file, 'permanent') !== false) {
            // It has <div style="margin-left:auto;display:flex;gap:6px;align-items:center;">
            if (preg_match('/<div style="margin-left:auto;display:flex;gap:6px;align-items:center;">(.*?)<\/div>/s', $formInner, $btnsMatch)) {
                $btnsHtml = $btnsMatch[1];
                
                preg_match('/@if\([^)]*\)\s*<button[^>]*toggle-select-multiple[^>]*>.*?<\/button>\s*@endif/s', $btnsHtml, $m1);
                preg_match('/@if\([^)]*\)\s*<button[^>]*delete-all-btn[^>]*>.*?<\/button>\s*@endif/s', $btnsHtml, $m2);
                preg_match('/<a[^>]*archives\.index[^>]*>.*?<\/a>/s', $btnsHtml, $m3);
                preg_match('/<button[^>]*exportModal[^>]*>.*?<\/button>/s', $btnsHtml, $m4);
                
                $orderedBtns = ($m3[0] ?? '') . "\n" . ($m4[0] ?? '') . "\n" . ($m1[0] ?? '') . "\n" . ($m2[0] ?? '');
                
                // create bottom row
                $content = str_replace($btnsMatch[0], '</div><!-- Bottom Row: Actions (Far Right) --><div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-end; width: 100%; margin-top: 10px;">' . $orderedBtns . '</div>', $content);
            }
        }
        
        // original state for all-data:
        if (strpos($file, 'all-data') !== false) {
            if (preg_match('/<div class="ad-filter">(.*?)<\/div>\s*<\/form>/s', $content, $filterMatch)) {
                $filterInner = $filterMatch[1];
                
                // extract buttons
                $btnRegexes = [
                    'add' => '/@if\([^)]*isSuperAdmin[^)]*\)\s*<a[^>]*create[^>]*>.*?<\/a>\s*(?:@endif)?/si',
                    'import' => '/(?:@if\([^)]*isSuperAdmin[^)]*\)\s*)?<button[^>]*class="[^"]*import[^"]*"[^>]*>.*?<\/button>\s*(?:@endif)?/si',
                    'history' => '/<a[^>]*import\.history[^>]*>.*?<\/a>/si',
                    'export' => '/(?:<button[^>]*exportModal[^>]*>.*?<\/button>|<a[^>]*export\.excel[^>]*>.*?<\/a>)/si',
                    'select_multiple' => '/@if\([^)]*isSuperAdmin[^)]*\)\s*<button[^>]*toggle-select-multiple[^>]*>.*?<\/button>\s*@endif/si',
                    'delete_all' => '/@if\([^)]*isSuperAdmin[^)]*\)\s*<button[^>]*delete-all-btn[^>]*>.*?<\/button>\s*@endif/si',
                ];
                
                $extractedBtns = [];
                $temp = $filterInner;
                foreach ($btnRegexes as $k => $r) {
                    if (preg_match($r, $temp, $m)) {
                        $extractedBtns[$k] = $m[0];
                        $temp = str_replace($m[0], '', $temp);
                    } else {
                        $extractedBtns[$k] = '';
                    }
                }
                
                // Remove the old action container if it exists
                $temp = preg_replace('/<div style="margin-left:auto;[^>]*>.*?<\/div>/s', '', $temp);
                
                $orderedBtns = $extractedBtns['add'] . "\n" . $extractedBtns['import'] . "\n" . $extractedBtns['history'] . "\n" . $extractedBtns['export'] . "\n" . $extractedBtns['select_multiple'] . "\n" . $extractedBtns['delete_all'];
                
                $newFilterInner = '<div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">' . $temp . '</div>' . "\n" . '<!-- Bottom Row: Actions (Far Right) --><div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-end; width: 100%; margin-top: 10px;">' . $orderedBtns . '</div>';
                
                $content = str_replace($filterMatch[1], $newFilterInner, $content);
            }
        }
        
        file_put_contents($file, $content);
    }
}
echo "Done layout reorder";
