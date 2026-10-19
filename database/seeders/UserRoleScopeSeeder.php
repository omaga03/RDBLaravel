<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserRoleScope;

class UserRoleScopeSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = DB::table('auth_assignment')->get();

        foreach ($assignments as $assignment) {
            $user = User::find($assignment->user_id);
            $departmentId = ($user && $user->researcher) ? $user->researcher->department_id : null;

            $domainKey = match ($assignment->item_name) {
                'Admin Project' => 'project',
                'Admin Publication' => 'publication',
                'Admin Utilize' => 'utilize',
                'Admin Researcher' => 'researcher',
                'Admin Research' => 'research',
                default => null,
            };

            UserRoleScope::firstOrCreate([
                'user_id' => $assignment->user_id,
                'role_name' => $assignment->item_name,
                'department_id' => in_array($assignment->item_name, ['Researcher', 'Faculty Coordinator']) ? $departmentId : null,
                'domain_key' => $domainKey,
            ], [
                'is_active' => true,
                'notes' => 'Synchronized from auth_assignment',
            ]);
        }
    }
}
