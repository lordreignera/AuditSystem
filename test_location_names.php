<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AuditReviewTypeAttachment;

echo "Testing location names:" . PHP_EOL;

try {
    $attachments = AuditReviewTypeAttachment::with('reviewType')->take(5)->get();
    
    foreach ($attachments as $attachment) {
        echo "ID: {$attachment->id} - Location: " . $attachment->getContextualLocationName() . PHP_EOL;
        echo "  Raw location_name: " . ($attachment->location_name ?? 'NULL') . PHP_EOL;
        echo "  Review Type: " . $attachment->reviewType->name . PHP_EOL;
        echo "  Is Master: " . ($attachment->is_master ? 'Yes' : 'No') . PHP_EOL;
        echo "---" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
