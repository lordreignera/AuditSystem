<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking email verification status...\n\n";

$auditor = \App\Models\User::where('email', 'auditor@audit.com')->first();
$superAdmin = \App\Models\User::where('email', 'superadmin@audit.com')->first();

echo "=== AUDITOR ===\n";
echo "Email: " . $auditor->email . "\n";
echo "Email verified at: " . ($auditor->email_verified_at ? $auditor->email_verified_at : 'NOT VERIFIED') . "\n";
echo "Has verified email: " . ($auditor->hasVerifiedEmail() ? 'YES' : 'NO') . "\n\n";

echo "=== SUPER ADMIN ===\n";
echo "Email: " . $superAdmin->email . "\n";
echo "Email verified at: " . ($superAdmin->email_verified_at ? $superAdmin->email_verified_at : 'NOT VERIFIED') . "\n";
echo "Has verified email: " . ($superAdmin->hasVerifiedEmail() ? 'YES' : 'NO') . "\n\n";

echo "This is likely the issue! The 'verified' middleware requires email verification.\n";
