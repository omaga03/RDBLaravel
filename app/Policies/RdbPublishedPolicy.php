<?php

namespace App\Policies;

use App\Models\RdbPublished;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbPublishedPolicy
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
        return $user && ($user->hasPermission('RdbPublished.View') || $user->hasRole('Admin') || $user->hasRole('Published') || $user->hasRole('Admin Published'));
    }

    public function view(?User $user, RdbPublished $rdbPublished): bool
    {
        return $user && ($user->hasPermission('RdbPublished.View') || $user->hasRole('Admin') || $user->hasRole('Published') || $user->hasRole('Admin Published'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('RdbPublished.Create') || $user->hasRole('Admin') || $user->hasRole('Published') || $user->hasRole('Admin Published'));
    }

    public function update(?User $user, RdbPublished $rdbPublished): bool
    {
        return $user && ($user->hasPermission('RdbPublished.Update') || $user->hasRole('Admin') || $user->hasRole('Published') || $user->hasRole('Admin Published'));
    }

    public function delete(?User $user, RdbPublished $rdbPublished): bool
    {
        return $user && ($user->hasPermission('RdbPublished.Delete') || $user->hasRole('Admin') || $user->hasRole('Published') || $user->hasRole('Admin Published'));
    }

    public function restore(?User $user, RdbPublished $rdbPublished): bool
    {
        return $user && ($user->hasPermission('RdbPublished.Delete') || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbPublished $rdbPublished): bool
    {
        return $user && ($user->hasPermission('RdbPublished.Delete') || $user->hasRole('Admin'));
    }
}