<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AuthItem;
use App\Models\AuthItemChild;
use App\Models\AuthAssignment;
use App\Models\User;

class RbacSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Roles
        $roles = ['Admin', 'Researcher', 'Staff'];
        foreach ($roles as $role) {
            if (!AuthItem::where('name', $role)->exists()) {
                AuthItem::create(['name' => $role, 'type' => 1, 'description' => $role . ' Role']);
            }
        }

        // 2. Create Permissions
        $modules = ['RdbProject', 'RdbResearcher', 'RdbPublished'];
        $actions = ['View', 'Create', 'Update', 'Delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permName = "{$module}.{$action}";
                if (!AuthItem::where('name', $permName)->exists()) {
                    AuthItem::create(['name' => $permName, 'type' => 2, 'description' => "{$action} {$module}"]);
                }

                // Assign all permissions to Admin
                if (!AuthItemChild::where('parent', 'Admin')->where('child', $permName)->exists()) {
                    AuthItemChild::create(['parent' => 'Admin', 'child' => $permName]);
                }
            }
        }

        // 3. Assign Admin Role to User ID 1 (Assuming ID 1 is Super Admin)
        // Check if user 1 exists first
        $adminUser = User::find(1);
        if ($adminUser) {
            if (!AuthAssignment::where('user_id', 1)->where('item_name', 'Admin')->exists()) {
                AuthAssignment::create(['user_id' => 1, 'item_name' => 'Admin', 'created_at' => time()]);
            }
        }
    }
}
