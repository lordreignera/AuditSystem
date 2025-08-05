<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING AUDITOR DASHBOARD ACCESS ===\n";

// Find the auditor user
$auditor = App\Models\User::where('email', 'norahnakamatte05@gmail.com')->first();
if (!$auditor) {
    echo "Auditor not found!\n";
    exit;
}

echo "Testing dashboard for: " . $auditor->name . " (" . $auditor->email . ")\n";
echo "Roles: " . $auditor->roles->pluck('name')->join(', ') . "\n\n";

// Simulate the dashboard route logic
echo "=== DASHBOARD SIMULATION ===\n";

$totalAudits = App\Models\Audit::count();
$activeAudits = App\Models\Audit::where('end_date', '>', now())->count();
$completedAudits = App\Models\Audit::where('end_date', '<=', now())->count();
$totalUsers = App\Models\User::count();

$myAssignedAudits = $auditor->hasRole('Auditor')
    ? $auditor->assignedAudits()->count()
    : 0;
$myCompletedAudits = $auditor->hasRole('Auditor')
    ? $auditor->assignedAudits()->where('end_date', '<=', now())->count()
    : 0;

// Recent audits for the table - filter for auditors to show only assigned audits
if ($auditor->hasRole('Auditor')) {
    $recentAudits = $auditor->assignedAudits()->with('country')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();
} else {
    $recentAudits = App\Models\Audit::with('country')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();
}

echo "Stats that auditor sees:\n";
echo "- Total Audits (system-wide): $totalAudits\n";
echo "- Active Audits (system-wide): $activeAudits\n";
echo "- Completed Audits (system-wide): $completedAudits\n";
echo "- Total Users (system-wide): $totalUsers\n";
echo "- MY Assigned Audits: $myAssignedAudits\n";
echo "- MY Completed Audits: $myCompletedAudits\n\n";

echo "Recent Audits (filtered for auditor):\n";
foreach($recentAudits as $audit) {
    echo "- " . $audit->name . " (" . $audit->country->name . ")\n";
    echo "  Status: " . ($audit->end_date && $audit->end_date->isPast() ? 'Completed' : 'Active') . "\n";
    echo "  Period: " . $audit->start_date . " to " . $audit->end_date . "\n\n";
}

// Test audit index access (what auditor sees in audit list)
echo "=== AUDIT INDEX SIMULATION ===\n";
if ($auditor->hasRole('Auditor')) {
    $auditsInIndex = $auditor->assignedAudits()->with('country')->get();
} else {
    $auditsInIndex = App\Models\Audit::with('country')->get();
}

echo "Audits visible in audit index page:\n";
foreach($auditsInIndex as $audit) {
    echo "- " . $audit->name . "\n";
}

// Test individual audit access
echo "\n=== AUDIT ACCESS TEST ===\n";
$testAudit = App\Models\Audit::first();
$hasAccess = $auditor->assignedAudits()->where('audits.id', $testAudit->id)->exists();
echo "Can access '" . $testAudit->name . "': " . ($hasAccess ? 'YES' : 'NO') . "\n";

$assignedAudit = $auditor->assignedAudits()->first();
if ($assignedAudit) {
    $hasAccessToAssigned = $auditor->assignedAudits()->where('audits.id', $assignedAudit->id)->exists();
    echo "Can access assigned audit '" . $assignedAudit->name . "': " . ($hasAccessToAssigned ? 'YES' : 'NO') . "\n";
}
