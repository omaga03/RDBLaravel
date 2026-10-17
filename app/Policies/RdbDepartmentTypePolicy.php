<?php

namespace App\Policies;

use App\Models\RdbDepartmentType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbDepartmentTypePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, RdbDepartmentType $rdbDepartmentType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, RdbDepartmentType $rdbDepartmentType): bool
    {
        return $user && $user->hasRole('Admin');
    }



    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, RdbDepartmentType $rdbDepartmentType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, RdbDepartmentType $rdbDepartmentType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, RdbDepartmentType $rdbDepartmentType): bool
    {
        return $user && $user->hasRole('Admin');
    }
}