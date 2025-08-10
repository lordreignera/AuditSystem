<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Response;
use App\Models\AuditReviewTypeAttachment;

echo "Checking for orphaned responses..." . PHP_EOL;

try {
    // Find responses that have attachment_id but the attachment doesn't exist
    $orphanedResponses = Response::whereNotNull('attachment_id')
        ->whereDoesntHave('attachment')
        ->get();
    
    echo "Found " . $orphanedResponses->count() . " orphaned responses" . PHP_EOL;
    
    if ($orphanedResponses->count() > 0) {
        echo "Orphaned response details:" . PHP_EOL;
        foreach ($orphanedResponses as $response) {
            echo "  - Response ID: {$response->id}, Attachment ID: {$response->attachment_id}, Audit ID: {$response->audit_id}" . PHP_EOL;
        }
        
        echo PHP_EOL . "Do you want to delete these orphaned responses? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
            $deleted = $orphanedResponses->count();
            foreach ($orphanedResponses as $response) {
                $response->delete();
            }
            echo "Deleted {$deleted} orphaned responses." . PHP_EOL;
        } else {
            echo "Orphaned responses not deleted." . PHP_EOL;
        }
    }
    
    // Also check for responses without attachment_id (old format)
    $responsesWithoutAttachment = Response::whereNull('attachment_id')->count();
    echo "Found {$responsesWithoutAttachment} responses without attachment_id (legacy format)" . PHP_EOL;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
