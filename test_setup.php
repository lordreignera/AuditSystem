<?php

// Simple test runner to verify our setup
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing basic Laravel setup...\n";

try {
    // Create Laravel application
    $app = require __DIR__.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "✓ Laravel application boots successfully\n";
    
    // Test User model factory
    $user = \App\Models\User::factory()->make();
    echo "✓ User factory works: " . $user->name . "\n";
    
    // Test Country model
    $country = new \App\Models\Country(['name' => 'Test', 'code' => 'TS', 'is_active' => true]);
    echo "✓ Country model works: " . $country->name . "\n";
    
    // Test Audit model
    $audit = new \App\Models\Audit(['name' => 'Test Audit', 'review_code' => 'TEST001']);
    echo "✓ Audit model works: " . $audit->name . "\n";
    
    echo "\n🎉 All basic tests passed! The Laravel environment is ready.\n";
    echo "Models can be instantiated and factories work.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
