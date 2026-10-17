<?php

namespace App\Policies;

use App\Models\RdbDip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RdbDipPolicy
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

    public function viewAny(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function view(?User $user, RdbDip $rdbDip): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbDip $rdbDip): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbDip $rdbDip): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbDip $rdbDip): bool
    {
        return false;
    }

    public function forceDelete(?User $user, RdbDip $rdbDip): bool
    {
        return false;
    }
}
