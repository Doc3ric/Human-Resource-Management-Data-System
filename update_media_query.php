<?php
$dir = new RecursiveDirectoryIterator('resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

foreach ($files as $match) {
    $file = $match[0];
    $content = file_get_contents($file);
    $originalContent = $content;

    // Replace .sidebar-title in media queries
    $content = str_replace('.sidebar-title {', '.sidebar-title, .sidebar-title-container {', $content);

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Updated media query in $file\n";
    }
}
?>