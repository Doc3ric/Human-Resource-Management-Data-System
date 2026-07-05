<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$plantillas = DB::table('plantilla_records')
    ->select('id', 'first_name', 'last_name', 'middle_name', 'employment_status')
    ->limit(100)
    ->get();

$bad = [];
foreach ($plantillas as $p) {
    $str = $p->first_name . ' ' . $p->last_name . ' ' . $p->middle_name . ' ' . $p->employment_status;
    // Check for weird characters
    if (preg_match('/[^\x20-\x7E\xA0-\xFF]/', $str)) {
        $bad[] = $p;
    }
}

echo "Found bad records: " . count($bad) . "\n";
foreach (array_slice($bad, 0, 5) as $b) {
    echo json_encode($b, JSON_UNESCAPED_UNICODE) . "\n";
}
