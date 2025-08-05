<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Audit;

// Find the auditor user
$auditor = User::where('email', 'auditor@audit.com')->first();

if (!$auditor) {
    echo "Auditor user not found!\n";
    exit;
}

echo "Found auditor: " . $auditor->name . "\n";

// Get the first audit
$audit = Audit::first();

if (!$audit) {
    echo "No audits found in the system!\n";
    exit;
}

echo "Found audit: " . $audit->name . "\n";

// Check if already assigned
if ($auditor->assignedAudits()->where('audit_id', $audit->id)->exists()) {
    echo "Audit is already assigned to this auditor.\n";
} else {
    // Assign the audit to the auditor
    $auditor->assignedAudits()->attach($audit->id, [
        'assigned_by' => 1, // Assuming admin user ID is 1
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "Successfully assigned audit '{$audit->name}' to auditor '{$auditor->name}'!\n";
}

// Verify the assignment
$count = $auditor->assignedAudits()->count();
echo "Auditor now has {$count} assigned audit(s).\n";

if ($count > 0) {
    echo "Assigned audits:\n";
    foreach ($auditor->assignedAudits as $assignedAudit) {
        echo "- " . $assignedAudit->name . "\n";
    }
}
