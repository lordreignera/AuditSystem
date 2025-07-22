<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User Management Permissions
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'edit users']);
        Permission::firstOrCreate(['name' => 'delete users']);
        Permission::firstOrCreate(['name' => 'view users']);

        // Role Management Permissions
        Permission::firstOrCreate(['name' => 'manage roles']);
        Permission::firstOrCreate(['name' => 'create roles']);
        Permission::firstOrCreate(['name' => 'edit roles']);
        Permission::firstOrCreate(['name' => 'delete roles']);
        Permission::firstOrCreate(['name' => 'view roles']);
        Permission::firstOrCreate(['name' => 'assign roles']);

        // Permission Management Permissions
        Permission::firstOrCreate(['name' => 'manage permissions']);
        Permission::firstOrCreate(['name' => 'create permissions']);
        Permission::firstOrCreate(['name' => 'edit permissions']);
        Permission::firstOrCreate(['name' => 'delete permissions']);
        Permission::firstOrCreate(['name' => 'view permissions']);

        // Country Management Permissions
        Permission::firstOrCreate(['name' => 'manage countries']);
        Permission::firstOrCreate(['name' => 'create countries']);
        Permission::firstOrCreate(['name' => 'edit countries']);
        Permission::firstOrCreate(['name' => 'delete countries']);
        Permission::firstOrCreate(['name' => 'view countries']);

        // Review Type Management Permissions
        Permission::firstOrCreate(['name' => 'manage review types']);
        Permission::firstOrCreate(['name' => 'create review types']);
        Permission::firstOrCreate(['name' => 'edit review types']);
        Permission::firstOrCreate(['name' => 'delete review types']);
        Permission::firstOrCreate(['name' => 'view review types']);

        // Template Management Permissions
        Permission::firstOrCreate(['name' => 'manage templates']);
        Permission::firstOrCreate(['name' => 'create templates']);
        Permission::firstOrCreate(['name' => 'edit templates']);
        Permission::firstOrCreate(['name' => 'delete templates']);
        Permission::firstOrCreate(['name' => 'view templates']);

        // Section Management Permissions
        Permission::firstOrCreate(['name' => 'manage sections']);
        Permission::firstOrCreate(['name' => 'create sections']);
        Permission::firstOrCreate(['name' => 'edit sections']);
        Permission::firstOrCreate(['name' => 'delete sections']);
        Permission::firstOrCreate(['name' => 'view sections']);

        // Question Management Permissions
        Permission::firstOrCreate(['name' => 'manage questions']);
        Permission::firstOrCreate(['name' => 'create questions']);
        Permission::firstOrCreate(['name' => 'edit questions']);
        Permission::firstOrCreate(['name' => 'delete questions']);
        Permission::firstOrCreate(['name' => 'view questions']);

        // Audit Management Permissions
        Permission::firstOrCreate(['name' => 'manage audits']);
        Permission::firstOrCreate(['name' => 'create audits']);
        Permission::firstOrCreate(['name' => 'edit audits']);
        Permission::firstOrCreate(['name' => 'delete audits']);
        Permission::firstOrCreate(['name' => 'view audits']);
        Permission::firstOrCreate(['name' => 'assign auditors']);

        // Audit Response Permissions
        Permission::firstOrCreate(['name' => 'submit responses']);
        Permission::firstOrCreate(['name' => 'edit responses']);
        Permission::firstOrCreate(['name' => 'view responses']);

        // Report Permissions
        Permission::firstOrCreate(['name' => 'view reports']);
        Permission::firstOrCreate(['name' => 'export reports']);
        Permission::firstOrCreate(['name' => 'generate reports']);

        // Dashboard Permissions
        Permission::firstOrCreate(['name' => 'view dashboard']);
        Permission::firstOrCreate(['name' => 'view admin dashboard']);

        $this->command->info('Permissions created successfully!');
    }
}
