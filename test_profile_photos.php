<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test profile photo functionality
use App\Models\User;

echo "Testing Profile Photo Functionality\n";
echo "==================================\n\n";

// Get first user
$user = User::first();

if ($user) {
    echo "User: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Profile Photo Path: " . ($user->profile_photo_path ?? 'None') . "\n";
    echo "Profile Photo URL: {$user->profile_photo_url}\n\n";
    
    // Test if HasProfilePhoto trait is working
    echo "HasProfilePhoto trait methods:\n";
    echo "- profilePhotoPath: " . ($user->profilePhotoPath() ?? 'None') . "\n";
    echo "- getProfilePhotoUrlAttribute: {$user->getProfilePhotoUrlAttribute()}\n";
    
} else {
    echo "No users found in database.\n";
}

echo "\nJetstream Configuration:\n";
echo "Profile Photos Enabled: " . (in_array('Laravel\Jetstream\Features::profilePhotos()', config('jetstream.features', [])) ? 'Yes' : 'No') . "\n";
echo "Profile Photo Disk: " . config('jetstream.profile_photo_disk', 'public') . "\n";
