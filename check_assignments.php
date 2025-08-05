<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USER AUDIT ASSIGNMENTS TABLE ===\n";
$assignments = DB::table('user_audit_assignments')->get();
echo "Total assignments: " . $assignments->count() . "\n";

foreach ($assignments as $assignment) {
    echo "ID: " . $assignment->id . " | User ID: " . $assignment->user_id . " | Audit ID: " . $assignment->audit_id . "\n";
}

echo "\n=== AUDITS TABLE ===\n";
$audits = DB::table('audits')->get();
echo "Total audits: " . $audits->count() . "\n";

if ($audits->count() > 0) {
    $firstAudit = $audits->first();
    echo "First audit columns: " . implode(', ', array_keys((array)$firstAudit)) . "\n";
    
    foreach ($audits->take(5) as $audit) {
        echo "ID: " . $audit->id . " | Name: " . ($audit->name ?? 'NULL') . " | Country ID: " . ($audit->country_id ?? 'NULL') . "\n";
    }
}
