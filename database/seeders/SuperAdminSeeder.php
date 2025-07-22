<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);

        // Create a Super Admin user
        $user = User::firstOrCreate(
            ['email' => 'superadmin@audit.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@audit.com',
                'password' => Hash::make('SuperAdmin123!'), // Strong default password
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign Super Admin role
        $user->assignRole($superAdminRole);

        // Create sample users for other roles
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@audit.com'],
            [
                'name' => 'System Admin',
                'email' => 'admin@audit.com',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $auditManagerUser = User::firstOrCreate(
            ['email' => 'manager@audit.com'],
            [
                'name' => 'Audit Manager',
                'email' => 'manager@audit.com',
                'password' => Hash::make('Manager123!'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $auditorUser = User::firstOrCreate(
            ['email' => 'auditor@audit.com'],
            [
                'name' => 'Health Auditor',
                'email' => 'auditor@audit.com',
                'password' => Hash::make('Auditor123!'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign roles if they exist
        if (Role::where('name', 'Admin')->exists()) {
            $adminUser->assignRole('Admin');
        }
        if (Role::where('name', 'Audit Manager')->exists()) {
            $auditManagerUser->assignRole('Audit Manager');
        }
        if (Role::where('name', 'Auditor')->exists()) {
            $auditorUser->assignRole('Auditor');
        }

        $this->command->info('Super Admin and sample users created successfully!');
        $this->command->line('Login credentials:');
        $this->command->line('Super Admin: superadmin@audit.com / SuperAdmin123!');
        $this->command->line('Admin: admin@audit.com / Admin123!');
        $this->command->line('Audit Manager: manager@audit.com / Manager123!');
        $this->command->line('Auditor: auditor@audit.com / Auditor123!');
    }
}
