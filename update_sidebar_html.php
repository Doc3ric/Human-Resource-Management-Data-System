<?php
$dir = new RecursiveDirectoryIterator('resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

$newHtml = <<<HTML
        <div class="sidebar-header">
            <img src="{{ asset('img/logo.png') }}" alt="CSC Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="sidebar-title-container">
                <span class="sidebar-title-main">CSC</span>
                <span class="sidebar-title-sub">Plantilla System</span>
            </div>
        </div>
HTML;

foreach ($files as $match) {
    if (strpos($match[0], 'components\dashboard-app.blade.php') !== false || strpos($match[0], 'components/dashboard-app.blade.php') !== false) {
        continue;
    }
    if (strpos($match[0], 'dashboard.blade.php') !== false) {
        continue; // I think dashboard.blade.php was modified? Let's check below.
    }

    $file = $match[0];
    $content = file_get_contents($file);
    $originalContent = $content;

    // Replace where the logo is missing
    $htmlSearch3 = '/<div class="sidebar-header">\s*<div class="sidebar-title">CSC PLANTILLA<\/div>\s*<\/div>/is';
    $content = preg_replace($htmlSearch3, $newHtml, $content);

    // Replace where the logo might be slightly different
    $htmlSearch4 = '/<div class="sidebar-header">.*?<div class="sidebar-title">CSC PLANTILLA<\/div>\s*<\/div>/is';
    $content = preg_replace($htmlSearch4, $newHtml, $content);

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Updated HTML in $file\n";
    }
}
?>