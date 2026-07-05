<?php

function updateController($filePath, $sessionKey, $routeName) {
    if (!file_exists($filePath)) return;
    $content = file_get_contents($filePath);

    // 1. Add session store in index method
    if (strpos($content, "session(['{$sessionKey}' => request()->fullUrl()]);") === false) {
        $content = preg_replace('/(public function index\(Request \$request\)\s*\{)/', "$1\n        session(['{$sessionKey}' => request()->fullUrl()]);\n", $content);
    }

    // 2. Replace hardcoded redirects to index with session-based ones
    // Only target return redirect()->route('...')
    // We don't want to break with() chains, so we do it carefully.
    $searchPattern = "/return redirect\(\)->route\('{$routeName}'\)/";
    $replaceString = "return redirect(session('{$sessionKey}', route('{$routeName}')))";
    $content = preg_replace($searchPattern, $replaceString, $content);
    
    // Some controllers use return redirect($returnUrl)->with...
    // Let's not touch those if they already exist (e.g. AllDataController, PlantillaController).
    
    file_put_contents($filePath, $content);
    echo "Updated $filePath\n";
}

// 1. Job Order
updateController(
    'c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/JobOrderController.php',
    'jo_index_url',
    'job-orders.index'
);

// 2. Casual
updateController(
    'c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/CasualController.php',
    'cas_index_url',
    'casual.index'
);

// 3. Permanent
updateController(
    'c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/PermanentController.php',
    'perm_index_url',
    'permanent.index'
);

// 4. Plantilla (since permanent uses plantilla edit)
updateController(
    'c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/PlantillaController.php',
    'plan_index_url',
    'plantilla.index'
);

// 5. All Data
updateController(
    'c:/laragon/www/Human-Resource-Management-Data-System/app/Http/Controllers/AllDataController.php',
    'ad_index_url',
    'all-data.index'
);
