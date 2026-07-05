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
    
    // Change flex-end back to flex-start
    $content = str_replace('justify-content: flex-end;', 'justify-content: flex-start;', $content);
    $content = str_replace('<!-- Bottom Row: Actions (Far Right) -->', '<!-- Bottom Row: Actions (Far Left) -->', $content);
    
    file_put_contents($file, $content);
    echo "Aligned left for $file\n";
}
