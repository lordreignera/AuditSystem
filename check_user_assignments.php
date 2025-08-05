<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RECENT USERS ===\n";
$users = App\Models\User::with('roles')->orderBy('created_at', 'desc')->take(5)->get();
foreach($users as $user) {
    echo $user->id . ': ' . $user->name . ' (' . $user->email . ')' . "\n";
    echo '   Roles: ' . $user->roles->pluck('name')->join(', ') . "\n";
    echo '   Created: ' . $user->created_at . "\n";
    echo "\n";
}

echo "\n=== USER AUDIT ASSIGNMENTS ===\n";
$assignments = DB::table('user_audit_assignments')
    ->join('users', 'user_audit_assignments.user_id', '=', 'users.id')
    ->join('audits', 'user_audit_assignments.audit_id', '=', 'audits.id')
    ->select('users.name as user_name', 'users.email', 'audits.name as audit_name', 'user_audit_assignments.*')
    ->get();

if($assignments->count() > 0) {
    foreach($assignments as $assignment) {
        echo "User: " . $assignment->user_name . " (" . $assignment->email . ")\n";
        echo "Audit: " . $assignment->audit_name . "\n";
        echo "Assigned by: " . $assignment->assigned_by . " at " . $assignment->assigned_at . "\n";
        echo "---\n";
    }
} else {
    echo "No audit assignments found.\n";
}

echo "\n=== AUDITORS WITH THEIR ASSIGNMENTS ===\n";
$auditors = App\Models\User::whereHas('roles', function($query) {
    $query->where('name', 'Auditor');
})->with(['roles', 'assignedAudits'])->get();

foreach($auditors as $auditor) {
    echo "Auditor: " . $auditor->name . " (" . $auditor->email . ")\n";
    echo "Assigned Audits: " . $auditor->assignedAudits->count() . "\n";
    foreach($auditor->assignedAudits as $audit) {
        echo "  - " . $audit->name . "\n";
    }
    echo "---\n";
}
