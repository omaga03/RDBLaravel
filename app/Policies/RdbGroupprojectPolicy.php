<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbGroupproject;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbGroupprojectPolicy
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
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasPermission('MasterData.Manage')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, RdbGroupproject $rdbGroupproject): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, RdbGroupproject $rdbGroupproject): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, RdbGroupproject $rdbGroupproject): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, RdbGroupproject $rdbGroupproject): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, RdbGroupproject $rdbGroupproject): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }
}