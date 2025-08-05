<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USERS AND THEIR ROLES ===\n";
$users = App\Models\User::with('roles')->get();

foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    echo $user->name . " (" . $user->email . ") - Roles: " . ($roles ?: 'No roles') . "\n";
}

echo "\n=== USER AUDIT ASSIGNMENTS ===\n";
$assignments = App\Models\UserAuditAssignment::with(['user', 'audit'])->get();

foreach ($assignments as $assignment) {
    $userName = $assignment->user ? $assignment->user->name : 'Unknown User';
    $auditCode = $assignment->audit ? $assignment->audit->audit_code : 'Unknown Audit';
    echo $userName . " assigned to audit: " . $auditCode . "\n";
}

echo "\n=== AUDITORS AND THEIR ASSIGNED AUDITS ===\n";
$auditors = App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'Auditor');
})->with('assignedAudits')->get();

foreach ($auditors as $auditor) {
    echo $auditor->name . " - Assigned audits: " . $auditor->assignedAudits->count() . "\n";
    foreach ($auditor->assignedAudits as $audit) {
        echo "  - " . $audit->audit_code . " (" . $audit->country->name . ")\n";
    }
}
