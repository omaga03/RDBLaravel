<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbDepartmentCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbDepartmentCategoryPolicy
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
        return $user && $user->hasRole('Admin');
    }

    public function view(?User $user, RdbDepartmentCategory $rdbDepartmentCategory): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbDepartmentCategory $rdbDepartmentCategory): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbDepartmentCategory $rdbDepartmentCategory): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbDepartmentCategory $rdbDepartmentCategory): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbDepartmentCategory $rdbDepartmentCategory): bool
    {
        return $user && $user->hasRole('Admin');
    }
}
