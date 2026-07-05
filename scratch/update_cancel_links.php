<?php

$files = [
    'job-orders/edit.blade.php' => 'job-orders.index',
    'job-orders/create.blade.php' => 'job-orders.index',
    'casual/edit.blade.php' => 'casual.index',
    'casual/create.blade.php' => 'casual.index',
    'plantilla/edit.blade.php' => 'plantilla.index',
    'plantilla/create.blade.php' => 'plantilla.index',
    'all-data/edit.blade.php' => 'all-data.index',
];

foreach ($files as $file => $route) {
    $path = 'c:/laragon/www/Human-Resource-Management-Data-System/resources/views/' . $file;
    if (!file_exists($path)) continue;
    
    $content = file_get_contents($path);
    
    // Replace <a href="{{ request('return_url', route('X')) }}" ...
    $content = preg_replace("/\{\{\s*request\('return_url',\s*route\('{$route}'\)\)\s*\}\}/", "{{ session('last_index_url', route('{$route}')) }}", $content);
    
    // Replace <a href="{{ route('X') }}" class="btn ...">Cancel</a> (or similar cancel links)
    // We'll just look for route('X') inside an href that is next to Cancel text, but it's easier to just replace all route('X') that are in hrefs EXCEPT if they are not Cancel links?
    // In edit forms, the only link to the index is usually the Cancel/Back button.
    $content = preg_replace("/href=\"\{\{\s*route\('{$route}'\)\s*\}\}\"/", "href=\"{{ session('last_index_url', route('{$route}')) }}\"", $content);
    
    // Remove the <input type="hidden" name="return_url"...> since we don't need it anymore
    $content = preg_replace('/<input\s+type="hidden"\s+name="return_url"\s+value="[^"]*">/', '', $content);
    
    file_put_contents($path, $content);
    echo "Updated Cancel links in $file\n";
}
