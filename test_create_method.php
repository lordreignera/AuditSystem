<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing UserController create method...\n";

$controller = new App\Http\Controllers\Admin\UserManagement\UserController();

try {
    // Check if roles exist
    $roles = Spatie\Permission\Models\Role::all();
    echo "Roles found: " . $roles->count() . "\n";
    foreach($roles as $role) {
        echo "- " . $role->name . "\n";
    }
    
    echo "\n";
    
    // Check if audits exist  
    $audits = App\Models\Audit::select('id', 'name')->get();
    echo "Audits found: " . $audits->count() . "\n";
    foreach($audits as $audit) {
        echo "- " . $audit->id . ": " . $audit->name . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
