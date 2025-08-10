<?php

// Test script to verify registration functionality
// Run this with: php artisan tinker test-registration.php

echo "🧪 Testing Registration Implementation\n";
echo "=====================================\n\n";

echo "📋 Available Roles:\n";
$roles = \Spatie\Permission\Models\Role::whereNotIn('name', ['Super Admin'])->get();
foreach($roles as $role) {
    echo "  - {$role->name}" . ($role->description ? " ({$role->description})" : "") . "\n";
}

echo "\n📊 Available Audits:\n";
$audits = \App\Models\Audit::with('country')
    ->whereDate('end_date', '>=', now())
    ->orderBy('start_date', 'desc')
    ->take(5)
    ->get();
    
foreach($audits as $audit) {
    $country = $audit->country->name ?? 'Unknown';
    $endDate = $audit->end_date ? $audit->end_date->format('M d, Y') : 'No deadline';
    echo "  - {$audit->name} ({$country}) - Due: {$endDate}\n";
}

echo "\n✅ Registration form should work with these options!\n";
echo "\n📝 Test Instructions:\n";
echo "1. Go to /register\n";
echo "2. Fill in user details\n";
echo "3. Select 'Auditor' role to see audit assignment\n";
echo "4. Select any other role to hide audit assignment\n";
echo "5. Complete registration and test login\n";
