<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = new \Google\Client();
$client->setAuthConfig(config('services.google_drive.service_account_path'));
$client->addScope(\Google\Service\Drive::DRIVE);

$service = new \Google\Service\Drive($client);

try {
    $fileMetadata = new \Google\Service\Drive\DriveFile([
        'name' => 'HRDMS Backups Folder',
        'mimeType' => 'application/vnd.google-apps.folder'
    ]);
    
    $folder = $service->files->create($fileMetadata, ['fields' => 'id']);
    echo "NEW FOLDER CREATED! ID: " . $folder->id . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
