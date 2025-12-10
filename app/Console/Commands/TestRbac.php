<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestRbac extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:rbac';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RBAC permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = 211; // User with 'Admin Big Research' role
        $user = \App\Models\User::find($userId);

        if (!$user) {
            $this->error("User $userId not found!");
            return;
        }

        $this->info("Testing RBAC for user: " . $user->username);

        // Get roles
        $roles = $user->roles->pluck('item_name');
        $this->info("Roles: " . $roles->implode(', '));

        if ($roles->isEmpty()) {
            $this->error("User has no roles.");
            return;
        }

        // Find a permission that is a child of the first role
        $roleName = $roles->first();
        $child = \App\Models\AuthItemChild::where('parent', $roleName)->first();

        if ($child) {
            $permission = $child->child;
            $this->info("Testing permission: $permission (inherited from $roleName)");

            if (\Illuminate\Support\Facades\Gate::forUser($user)->allows($permission)) {
                $this->info("PASS: User has permission '$permission'");
            } else {
                $this->error("FAIL: User DOES NOT have permission '$permission'");
            }
        } else {
            $this->warn("Role $roleName has no children to test.");
        }
        
        // Test a fake permission
        $fakePermission = 'FakePermission';
        if (\Illuminate\Support\Facades\Gate::forUser($user)->allows($fakePermission)) {
            $this->error("FAIL: User has permission '$fakePermission' (should fail)");
        } else {
            $this->info("PASS: User does not have permission '$fakePermission'");
        }
    }
}
