<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbPublishedType;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbPublishedTypePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Published') || $user->hasPermission(['MasterData.Manage', 'RdbPublished.View'])) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbPublished.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Published'));
    }

    public function view(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbPublished.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Published'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbPublished.Create']) || $user->hasRole('Admin') || $user->hasRole('Admin Published'));
    }

    public function update(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbPublished.Update']) || $user->hasRole('Admin') || $user->hasRole('Admin Published'));
    }

    public function delete(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbPublished.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Published'));
    }

    public function restore(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbPublished.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Published'));
    }

    public function forceDelete(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbPublished.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Published'));
    }
}