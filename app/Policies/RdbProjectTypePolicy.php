<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbProjectType;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectTypePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
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
    public function view(?User $user, RdbProjectType $rdbProjectType): bool
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
    public function update(?User $user, RdbProjectType $rdbProjectType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, RdbProjectType $rdbProjectType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, RdbProjectType $rdbProjectType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, RdbProjectType $rdbProjectType): bool
    {
        return $user && $user->hasRole('Admin');
    }
}