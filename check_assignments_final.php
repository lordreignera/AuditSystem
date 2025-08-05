<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ALL USERS ===\n";
$users = App\Models\User::all();
foreach ($users as $user) {
    echo $user->id . ': ' . $user->name . ' (' . $user->email . ")\n";
}

echo "\n=== ALL USER AUDIT ASSIGNMENTS ===\n";
$assignments = DB::table('user_audit_assignments')->get();

foreach ($assignments as $assignment) {
    $user = App\Models\User::find($assignment->user_id);
    $audit = App\Models\Audit::find($assignment->audit_id);
    
    echo "User: " . ($user ? $user->name : 'Unknown') . " (ID: " . $assignment->user_id . ")\n";
    echo "Audit: " . ($audit ? $audit->name : 'Unknown') . " (ID: " . $assignment->audit_id . ")\n";
    echo "---\n";
}
