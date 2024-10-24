<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create Roles
        $guestRole = Role::create(['name' => 'Guest']);
        $registeredRole = Role::create(['name' => 'Registered']);
        $adminRole = Role::create(['name' => 'Administrator']);

        // Create Permissions
        $viewPagePermission = Permission::create(['name' => 'View page']);

        // Assign Permissions to Roles
        $guestRole->permissions()->attach($viewPagePermission);
        $registeredRole->permissions()->attach($viewPagePermission);
        $adminRole->permissions()->attach($viewPagePermission);
    }
}
