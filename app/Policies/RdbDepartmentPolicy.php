<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbDepartment;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbDepartmentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator')) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Department']) || $user->hasRole('Admin'));
    }

    public function view(?User $user, RdbDepartment $rdbDepartment): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Department']) || $user->hasRole('Admin'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Department']) || $user->hasRole('Admin'));
    }

    public function update(?User $user, RdbDepartment $rdbDepartment): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Department']) || $user->hasRole('Admin'));
    }

    public function delete(?User $user, RdbDepartment $rdbDepartment): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Department']) || $user->hasRole('Admin'));
    }

    public function restore(?User $user, RdbDepartment $rdbDepartment): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Department']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbDepartment $rdbDepartment): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Department']) || $user->hasRole('Admin'));
    }
}