<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::where('name', 'like', '%Jeffrey%')->orWhere('name', 'like', '%Cathy%')->with('roles')->get();
foreach($users as $u) {
    echo $u->name . ' - Roles: ' . $u->roles->pluck('name')->join(', ') . "\n";
}
