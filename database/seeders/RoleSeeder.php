<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin Role (all permissions)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Create Admin Role
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([
            'manage review types', 'create review types', 'edit review types', 'delete review types', 'view review types',
            'manage templates', 'create templates', 'edit templates', 'delete templates', 'view templates',
            'manage sections', 'create sections', 'edit sections', 'delete sections', 'view sections',
            'manage questions', 'create questions', 'edit questions', 'delete questions', 'view questions',
            'manage audits', 'create audits', 'edit audits', 'delete audits', 'view audits', 'assign auditors',
            'manage countries', 'create countries', 'edit countries', 'delete countries', 'view countries',
            'view responses', 'view reports', 'export reports', 'generate reports',
            'view dashboard', 'view admin dashboard'
        ]);

        // Create Audit Manager Role
        $auditManager = Role::firstOrCreate(['name' => 'Audit Manager']);
        $auditManager->givePermissionTo([
            'view review types', 'view templates', 'view sections', 'view questions',
            'manage audits', 'create audits', 'edit audits', 'view audits', 'assign auditors',
            'view responses', 'view reports', 'export reports', 'generate reports',
            'view dashboard'
        ]);

        // Create Auditor Role
        $auditor = Role::firstOrCreate(['name' => 'Auditor']);
        $auditor->givePermissionTo([
            'view audits', 'submit responses', 'edit responses', 'view responses',
            'view dashboard'
        ]);

        $this->command->info('Roles and permissions assigned successfully!');
    }
}
