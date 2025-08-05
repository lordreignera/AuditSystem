<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Super Admin vs Auditor access...\n\n";

// Check Super Admin
$superAdmin = \App\Models\User::where('email', 'superadmin@audit.com')->first();
echo "=== SUPER ADMIN ===\n";
echo "User: " . $superAdmin->name . "\n";
echo "Has view audits permission: " . ($superAdmin->can('view audits') ? 'YES' : 'NO') . "\n";
echo "All permissions: " . $superAdmin->getAllPermissions()->pluck('name')->join(', ') . "\n";
echo "All roles: " . $superAdmin->roles->pluck('name')->join(', ') . "\n\n";

// Check Auditor
$auditor = \App\Models\User::where('email', 'auditor@audit.com')->first();
echo "=== AUDITOR ===\n";
echo "User: " . $auditor->name . "\n";
echo "Has view audits permission: " . ($auditor->can('view audits') ? 'YES' : 'NO') . "\n";
echo "All permissions: " . $auditor->getAllPermissions()->pluck('name')->join(', ') . "\n";
echo "All roles: " . $auditor->roles->pluck('name')->join(', ') . "\n\n";

echo "=== ROUTE MIDDLEWARE CHECK ===\n";
$router = app('router');
$routes = $router->getRoutes();
$auditIndexRoute = $routes->getByName('admin.audits.index');

if ($auditIndexRoute) {
    echo "Route found: " . $auditIndexRoute->uri() . "\n";
    echo "Route middleware: " . implode(', ', $auditIndexRoute->middleware()) . "\n";
    echo "Route action: " . $auditIndexRoute->getActionName() . "\n";
} else {
    echo "Route admin.audits.index not found!\n";
}
