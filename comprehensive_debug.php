<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Comprehensive auditor access debug...\n\n";

$auditor = \App\Models\User::where('email', 'auditor@audit.com')->first();

echo "=== USER STATUS ===\n";
echo "ID: " . $auditor->id . "\n";
echo "Name: " . $auditor->name . "\n";
echo "Email: " . $auditor->email . "\n";
echo "Is Active: " . ($auditor->is_active ? 'YES' : 'NO') . "\n";
echo "Email Verified: " . ($auditor->hasVerifiedEmail() ? 'YES' : 'NO') . "\n";
echo "Created At: " . $auditor->created_at . "\n";
echo "Updated At: " . $auditor->updated_at . "\n\n";

echo "=== AUTHENTICATION ===\n";
// Manually authenticate the user to test middleware
auth()->login($auditor);
echo "User logged in successfully\n";
echo "Auth check: " . (auth()->check() ? 'YES' : 'NO') . "\n";
echo "Auth user ID: " . (auth()->check() ? auth()->id() : 'N/A') . "\n\n";

echo "=== MIDDLEWARE SIMULATION ===\n";
// Test each middleware requirement
echo "Testing 'auth:sanctum' - User authenticated: " . (auth()->check() ? 'PASS' : 'FAIL') . "\n";
echo "Testing 'verified' - Email verified: " . ($auditor->hasVerifiedEmail() ? 'PASS' : 'FAIL') . "\n\n";

echo "=== CONTROLLER MIDDLEWARE ===\n";
echo "Has 'view audits' permission: " . ($auditor->can('view audits') ? 'PASS' : 'FAIL') . "\n\n";

echo "=== TRYING TO ACCESS ROUTE ===\n";
try {
    // Try to simulate the route call
    $request = \Illuminate\Http\Request::create('/admin/audits', 'GET');
    $request->setUserResolver(function () use ($auditor) {
        return $auditor;
    });
    
    // Get the controller instance
    $controller = new \App\Http\Controllers\Admin\AuditController();
    
    echo "Controller instantiated successfully\n";
    
    // Try to call the index method directly
    $response = $controller->index();
    echo "Controller index() method called successfully\n";
    echo "Response type: " . get_class($response) . "\n";
    
} catch (Exception $e) {
    echo "ERROR calling controller: " . $e->getMessage() . "\n";
    echo "Error file: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
