<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = new \Google\Client();
$client->setAuthConfig(config('services.google_drive.service_account_path'));
$client->addScope(\Google\Service\Drive::DRIVE);

$service = new \Google\Service\Drive($client);
$folderId = '1_EjQ6MsOga98MdwXoChjmFA3fsHtYqCE';

try {
    $folder = $service->files->get($folderId, ['fields' => 'id, name, mimeType']);
    echo "Found! Name: " . $folder->getName() . ", MimeType: " . $folder->getMimeType() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
