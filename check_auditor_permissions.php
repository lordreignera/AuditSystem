<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking auditor permissions...\n";

$user = \App\Models\User::where('email', 'auditor@audit.com')->first();

if (!$user) {
    echo "Auditor user not found!\n";
    exit;
}

echo "User: " . $user->name . "\n";
echo "Email: " . $user->email . "\n";
echo "Has view audits permission: " . ($user->can('view audits') ? 'YES' : 'NO') . "\n";
echo "Has Auditor role: " . ($user->hasRole('Auditor') ? 'YES' : 'NO') . "\n";
echo "All permissions: " . $user->getAllPermissions()->pluck('name')->join(', ') . "\n";
echo "All roles: " . $user->roles->pluck('name')->join(', ') . "\n";
echo "Assigned audits count: " . $user->assignedAudits()->count() . "\n";

echo "\nNow testing route access...\n";

// Test if route exists
try {
    $route = route('admin.audits.index');
    echo "Route admin.audits.index exists: " . $route . "\n";
} catch (Exception $e) {
    echo "Route admin.audits.index ERROR: " . $e->getMessage() . "\n";
}
