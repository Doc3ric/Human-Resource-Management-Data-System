<?php

function updateControllerUnified($filePath) {
    if (!file_exists($filePath)) return;
    $content = file_get_contents($filePath);

    // 1. Unified session store in index method
    // Remove previous session keys if they exist
    $content = preg_replace("/\s*session\(\['(jo|cas|perm|plan|ad)_index_url' => request\(\)->fullUrl\(\)\]\);\n/", "\n", $content);
    
    if (strpos($content, "session(['last_index_url' => request()->fullUrl()]);") === false) {
        $content = preg_replace('/(public function index\(Request \$request\)\s*\{)/', "$1\n        session(['last_index_url' => request()->fullUrl()]);\n", $content);
    }

    // 2. Fix the redirects
    // Replace the ones we did earlier
    $content = preg_replace("/return redirect\(session\('(jo|cas|perm|plan|ad)_index_url', route\('(.*?)'\)\)\)/", "return redirect(session('last_index_url', route('$2')))", $content);
    
    // Also fix PlantillaController and AllDataController which had $returnUrl
    $content = preg_replace('/\$returnUrl = \$request->input\(\'return_url\'\) \?: route\(\'(.*?)\'\);/', "\$returnUrl = session('last_index_url', route('$1'));", $content);
    
    file_put_contents($filePath, $content);
    echo "Unified $filePath\n";
}

$controllers = [
    'JobOrderController.php',
    'CasualController.php',
    'PermanentController.php',
    'PlantillaController.php',
    'AllDataController.php'
];

foreach ($controllers as $c) {
    updateControllerUnified('c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/' . $c);
}
