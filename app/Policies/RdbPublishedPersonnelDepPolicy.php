<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbPublishedPersonnelDep;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbPublishedPersonnelDepPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasPermission('MasterData.Manage')) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    public function view(?User $user, RdbPublishedPersonnelDep $model): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    public function update(?User $user, RdbPublishedPersonnelDep $model): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    public function delete(?User $user, RdbPublishedPersonnelDep $model): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }
}
