<?php

namespace App\Policies;

use App\Models\RdbDip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbDipPolicy
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
        return $user && ($user->hasPermission(['RdbDip.View', 'Dip']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    public function view(?User $user, RdbDip $rdbDip): bool
    {
        return $user && ($user->hasPermission(['RdbDip.View', 'Dip']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['RdbDip.Create', 'Dip']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    public function update(?User $user, RdbDip $rdbDip): bool
    {
        return $user && ($user->hasPermission(['RdbDip.Update', 'Dip']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    public function delete(?User $user, RdbDip $rdbDip): bool
    {
        return $user && ($user->hasPermission(['RdbDip.Delete', 'Dip']) || $user->hasRole('Admin') || $user->hasRole('Admin Dip'));
    }

    public function restore(?User $user, RdbDip $rdbDip): bool
    {
        return $user && ($user->hasPermission(['RdbDip.Delete', 'Dip']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbDip $rdbDip): bool
    {
        return $user && ($user->hasPermission(['RdbDip.Delete', 'Dip']) || $user->hasRole('Admin'));
    }
}
