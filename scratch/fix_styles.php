<?php

$casualFile = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/casual/index.blade.php';
$casualContent = file_get_contents($casualFile);

$casCss = '';
if (preg_match('/(\/\* Filter bar \*\/\s*\.cas-filter\s*\{.*?\.cas-btn:hover\s*\{.*?\})/s', $casualContent, $cssMatch)) {
    $casCss = $cssMatch[1];
} else if (preg_match('/(\/\* Filter bar \*\/\s*\.cas-filter.*?)(?:\/\*|\<\/style\>)/s', $casualContent, $cssMatch)) {
    $casCss = rtrim($cssMatch[1]);
}

// Fallback if regex fails to grab everything
if (empty($casCss)) {
    $casCss = '
        /* Filter bar */
        .cas-filter { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 18px; margin-bottom: 16px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0, 0, 0, .04); }
        .cas-input, .cas-select { border: 1px solid #e5e7eb; border-radius: 8px; padding: 8px 12px; font-size: 12px; color: #374151; outline: none; background: #fafafa; transition: border .15s, box-shadow .15s; }
        .cas-input:focus, .cas-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .1); background: #fff; }
        .cas-input { flex: 1; min-width: 220px; }
        .cas-select { min-width: 140px; }
        .cas-btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; transition: all .15s; white-space: nowrap; }
        .cas-btn.primary { background: #3b82f6; color: #fff; }
        .cas-btn.primary:hover { background: #2563eb; }
        .cas-btn.reset { background: #f1f5f9; color: #475569; text-decoration: none; }
        .cas-btn.reset:hover { background: #e2e8f0; color: #1e293b; }
        .cas-btn.add { background: #10b981; color: #fff; text-decoration: none; }
        .cas-btn.add:hover { background: #059669; }
        .cas-btn.import { background: #f59e0b; color: #fff; text-decoration: none; }
        .cas-btn.import:hover { background: #d97706; }
        .cas-btn.archives { background: #ffffff; color: #334155; border: 1px solid #cbd5e1; }
        .cas-btn.archives:hover { background: #f8fafc; border-color: #94a3b8; }
        .cas-btn:hover { opacity: 0.9; }
    ';
} else {
   // ensure we have all rules
   $casCss = preg_replace('/(\/\* Table \*\/\s*\.cas-table-wrap.*)/s', '', $casCss);
}

// Ensure .cas-btn.add, etc are present
if (strpos($casCss, '.cas-btn.add') === false) {
    $casCss .= '
        .cas-btn.add { background: #10b981; color: #fff; text-decoration: none; }
        .cas-btn.add:hover { background: #059669; color: #fff; }
        .cas-btn.import { background: #f59e0b; color: #fff; text-decoration: none; }
        .cas-btn.import:hover { background: #d97706; color: #fff; }
    ';
}

$files = [
    'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/job-orders/index.blade.php',
    'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/all-data/index.blade.php',
    'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/permanent/index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Inject CSS
    if (strpos($content, '.cas-filter') === false || strpos($content, '.cas-btn.add') === false) {
        $content = preg_replace('/<style>/', "<style>\n" . $casCss . "\n", $content, 1);
    }
    
    // Fix route error for permanent
    if (strpos($file, 'permanent/index.blade.php') !== false) {
        $content = preg_replace('/@if\(auth\(\)->user\(\)->isSuperAdmin\(\) \|\| auth\(\)->user\(\)->isInventoryAdmin\(\)\)\s*<a href="\{\{ route\(\'permanent\.create\'\) \}\}" class="cas-btn add"><i class="bi bi-plus-lg"><\/i> Add Record<\/a>\s*@endif/s', '', $content);
    }
    
    file_put_contents($file, $content);
    echo "Fixed $file\n";
}
