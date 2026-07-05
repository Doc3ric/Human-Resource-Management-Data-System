<?php
$files = [
    'resources/views/casual/index.blade.php',
    'resources/views/job-orders/index.blade.php',
    'resources/views/all-data/index.blade.php',
    'resources/views/permanent/index.blade.php',
    'resources/views/plantilla/index.blade.php' // Include Plantilla for consistency
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    $content = str_replace(
        '<!-- Bottom Row: Actions (Far Left) -->', 
        '<!-- Bottom Row: Actions (Far Right) -->', 
        $content
    );
    
    $content = str_replace(
        '<div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">', 
        '<div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; justify-content: flex-end;">', 
        $content
    );
    
    file_put_contents($file, $content);
    echo "Fixed alignment for $file\n";
}
