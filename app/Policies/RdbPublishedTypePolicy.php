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
        if ($user->hasRole('The Creator')) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function view(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbPublishedType $rdbPublishedType): bool
    {
        return $user && $user->hasRole('Admin');
    }
}