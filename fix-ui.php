<?php
$files = [
    'resources/views/step-increment/nosa-report.blade.php',
    'resources/views/step-increment/office-report.blade.php',
    'resources/views/step-increment/hub.blade.php',
    'resources/views/step-increment/index.blade.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) continue;

    $c = file_get_contents($path);

    // Replace #fff to var(--color-surface, #fff) if it's for background
    $c = preg_replace('/background:\s*#fff(?:fff)?\s*;/i', 'background: var(--color-surface, #fff);', $c);
    $c = preg_replace('/background-color:\s*#fff(?:fff)?\s*;/i', 'background-color: var(--color-surface, #fff);', $c);
    $c = preg_replace('/background:\s*#fff(?:fff)?(["\'])/i', 'background: var(--color-surface, #fff)$1', $c);
    
    // Borders
    $c = preg_replace('/border:\s*1px solid #e5e7eb/i', 'border: 1px solid var(--color-border, #e5e7eb)', $c);
    $c = preg_replace('/border-top:\s*1px solid #e5e7eb/i', 'border-top: 1px solid var(--color-border, #e5e7eb)', $c);
    $c = preg_replace('/border:\s*2px solid #000/i', 'border: 2px solid var(--color-border, #000)', $c);
    $c = preg_replace('/border:\s*1px solid #000/i', 'border: 1px solid var(--color-border, #000)', $c);
    $c = preg_replace('/border-bottom:\s*2px solid #000/i', 'border-bottom: 2px solid var(--color-border, #000)', $c);
    $c = preg_replace('/border-top:\s*2px solid #000/i', 'border-top: 2px solid var(--color-border, #000)', $c);
    
    // Colors
    $c = preg_replace('/color:\s*#000(?:000)?/i', 'color: var(--color-text-primary, #000)', $c);
    $c = preg_replace('/color:\s*#1e293b/i', 'color: var(--color-text-primary, #1e293b)', $c);
    $c = preg_replace('/color:\s*#475569/i', 'color: var(--color-text-secondary, #475569)', $c);
    $c = preg_replace('/color:\s*#64748b/i', 'color: var(--color-text-muted, #64748b)', $c);
    
    // Page bg
    $c = preg_replace('/background:\s*#f8fafc/i', 'background: var(--color-page-bg, #f8fafc)', $c);

    file_put_contents($path, $c);
    echo "Fixed $file\n";
}
