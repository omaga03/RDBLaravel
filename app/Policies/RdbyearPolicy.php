<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbYear;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbyearPolicy
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
        return $user && ($user->hasPermission(['MasterData.Manage', 'Year']) || $user->hasRole('Admin'));
    }

    public function view(?User $user, RdbYear $rdbyear): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Year']) || $user->hasRole('Admin'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Year']) || $user->hasRole('Admin'));
    }

    public function update(?User $user, RdbYear $rdbyear): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Year']) || $user->hasRole('Admin'));
    }

    public function delete(?User $user, RdbYear $rdbyear): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Year']) || $user->hasRole('Admin'));
    }

    public function restore(?User $user, RdbYear $rdbyear): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Year']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbYear $rdbyear): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'Year']) || $user->hasRole('Admin'));
    }
}