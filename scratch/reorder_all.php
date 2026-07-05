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
    
    // The pattern is generally:
    // <form ... class="...filter"> ... inputs ... buttons ... </form>
    // We will find the start of the <form> and end of </form>
    
    preg_match('/<form[^>]*class="[^"]*filter"[^>]*>.*?<\/form>/s', $content, $matches);
    if (!$matches) {
        preg_match('/<form[^>]*id="[^"]*-search-form"[^>]*>.*?<\/form>/s', $content, $matches);
    }
    
    if (!$matches) continue;
    
    $formHtml = $matches[0];
    
    // Since it's Blade with @if, DOMDocument will fail or mangle Blade syntax.
    // Let's do it with precise regex.
    // Look for <button type="submit" ...> and everything after it until </form> (or before closing div)
    
    $submitPos = strpos($formHtml, '<button type="submit"');
    if ($submitPos === false) $submitPos = strpos($formHtml, '<button class="cas-btn primary"');
    if ($submitPos === false) $submitPos = strpos($formHtml, '<button class="perm-btn primary"');
    
    if ($submitPos !== false) {
        // extract the inputs part (everything before submit button, but after the opening <div class="cas-filter">)
        // Wait, the form has <form><div class="cas-filter">
        preg_match('/<div class="[^"]*filter">/', $formHtml, $divMatch, PREG_OFFSET_CAPTURE);
        if ($divMatch) {
            $divStart = $divMatch[0][1] + strlen($divMatch[0][0]);
            
            $inputsStr = substr($formHtml, $divStart, $submitPos - $divStart);
            $buttonsStr = substr($formHtml, $submitPos);
            
            // Clean up buttonsStr to remove trailing </div> and </form>
            $buttonsStr = preg_replace('/<\/div>\s*<\/form>$/', '', $buttonsStr);
            
            // Wrap buttons in a flex container, and inputs in a flex container
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
        }
    }
}
