<?php

namespace App\Policies;

use App\Models\RdbDipType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbDipTypePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Dip') || $user->hasPermission(['MasterData.Manage', 'RdbDip.View'])) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbDip.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, RdbDipType $rdbDipType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbDip.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbDip.Create']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, RdbDipType $rdbDipType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbDip.Update']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, RdbDipType $rdbDipType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbDip.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, RdbDipType $rdbDipType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbDip.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, RdbDipType $rdbDipType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbDip.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }
}