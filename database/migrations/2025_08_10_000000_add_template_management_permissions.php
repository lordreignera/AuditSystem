<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up()
    {
        // Create template management permissions
        Permission::firstOrCreate(['name' => 'manage templates', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view templates', 'guard_name' => 'web']);

        // Assign to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(['manage templates', 'view templates']);
        }

        // Assign view templates to other roles that should be able to view
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(['view templates']);
        }

        $managerRole = Role::where('name', 'Audit Manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo(['view templates']);
        }
    }

    public function down()
    {
        Permission::where('name', 'manage templates')->delete();
        Permission::where('name', 'view templates')->delete();
    }
};
