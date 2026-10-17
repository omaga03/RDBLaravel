<?php

namespace App\Policies;

use App\Models\RdbProject;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectPolicy
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
        return $user && ($user->hasPermission('RdbProject.View') || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function view(?User $user, RdbProject $rdbProject): bool
    {
        return $user && ($user->hasPermission('RdbProject.View') || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('RdbProject.Create') || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function update(?User $user, RdbProject $rdbProject): bool
    {
        return $user && ($user->hasPermission('RdbProject.Update') || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function delete(?User $user, RdbProject $rdbProject): bool
    {
        return $user && ($user->hasPermission('RdbProject.Delete') || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function restore(?User $user, RdbProject $rdbProject): bool
    {
        return $user && ($user->hasPermission('RdbProject.Delete') || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbProject $rdbProject): bool
    {
        return $user && ($user->hasPermission('RdbProject.Delete') || $user->hasRole('Admin'));
    }
}