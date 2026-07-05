<?php
require __DIR__.'/bootstrap/app.php';
$kernel = app()->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$p = DB::table('plantilla_records')->where('last_name', 'LIKE', '%Ã%')->first();
if ($p) {
    echo "Original: " . $p->last_name . "\n";
    $decoded1 = utf8_decode($p->last_name);
    echo "utf8_decode: " . $decoded1 . "\n";
    $decoded2 = mb_convert_encoding($p->last_name, 'ISO-8859-1', 'UTF-8');
    echo "mb_convert_encoding: " . $decoded2 . "\n";
} else {
    echo "No Ã found.";
}
