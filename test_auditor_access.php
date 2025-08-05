<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING AUDITOR ACCESS ===\n";

// Find the auditor user
$auditor = App\Models\User::where('email', 'norahnakamatte05@gmail.com')->first();
if (!$auditor) {
    echo "Auditor not found!\n";
    exit;
}

echo "Auditor: " . $auditor->name . " (" . $auditor->email . ")\n";
echo "Roles: " . $auditor->roles->pluck('name')->join(', ') . "\n";

// Check assigned audits
$assignedAudits = $auditor->assignedAudits;
echo "\nAssigned Audits (" . $assignedAudits->count() . "):\n";
foreach($assignedAudits as $audit) {
    echo "- ID: " . $audit->id . "\n";
    echo "  Name: " . $audit->name . "\n";
    echo "  Period: " . $audit->start_date . " to " . $audit->end_date . "\n";
    echo "  Created by: " . $audit->created_by . "\n";
    echo "\n";
}

// Simulate what the dashboard would show
echo "=== DASHBOARD SIMULATION ===\n";
if ($auditor->hasRole('Auditor')) {
    $dashboardAudits = $auditor->assignedAudits();
    echo "Dashboard would show: " . $dashboardAudits->count() . " audits\n";
    
    foreach($dashboardAudits->get() as $audit) {
        echo "✓ " . $audit->name . "\n";
    }
} else {
    echo "User is not an auditor\n";
}
