<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing route generation...\n\n";

// Test route generation
try {
    $auditIndexUrl = route('admin.audits.index');
    echo "Generated URL: " . $auditIndexUrl . "\n";
    
    // Parse the URL to see its components
    $parsedUrl = parse_url($auditIndexUrl);
    echo "Scheme: " . ($parsedUrl['scheme'] ?? 'none') . "\n";
    echo "Host: " . ($parsedUrl['host'] ?? 'none') . "\n";
    echo "Port: " . ($parsedUrl['port'] ?? 'none') . "\n";
    echo "Path: " . ($parsedUrl['path'] ?? 'none') . "\n";
    
    echo "\nExpected URL should be: http://localhost:83/admin/audits\n";
    echo "Generated URL matches expected: " . ($auditIndexUrl === 'http://localhost:83/admin/audits' ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "ERROR generating route: " . $e->getMessage() . "\n";
}

echo "\n=== APP URL CONFIG ===\n";
echo "APP_URL from config: " . config('app.url') . "\n";
echo "Current domain: " . request()->getHost() . "\n";
echo "Current port: " . request()->getPort() . "\n";
