<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$audits = App\Models\Audit::select('id', 'name', 'description', 'start_date', 'end_date')->get();
echo "Available Audits:\n";
foreach($audits as $audit) {
    echo $audit->id . ': ' . $audit->name . "\n";
    echo "   Description: " . substr($audit->description, 0, 50) . "...\n";
    echo "   Period: " . $audit->start_date . " to " . $audit->end_date . "\n\n";
}
