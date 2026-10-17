<?php

namespace App\Policies;

use App\Models\AuthItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuthItemPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasPermission(['Rbac.Management', 'RBAC Manager'])) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission(['Rbac.Management', 'RBAC Manager']) || $user->hasRole('Admin'));
    }

    public function view(?User $user, AuthItem $authItem): bool
    {
        return $user && ($user->hasPermission(['Rbac.Management', 'RBAC Manager']) || $user->hasRole('Admin'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['Rbac.Management', 'RBAC Manager']) || $user->hasRole('Admin'));
    }

    public function update(?User $user, AuthItem $authItem): bool
    {
        return $user && ($user->hasPermission(['Rbac.Management', 'RBAC Manager']) || $user->hasRole('Admin'));
    }

    public function delete(?User $user, AuthItem $authItem): bool
    {
        return $user && ($user->hasPermission(['Rbac.Management', 'RBAC Manager']) || $user->hasRole('Admin'));
    }

    public function restore(?User $user, AuthItem $authItem): bool
    {
        return $user && ($user->hasPermission(['Rbac.Management', 'RBAC Manager']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, AuthItem $authItem): bool
    {
        return $user && ($user->hasPermission(['Rbac.Management', 'RBAC Manager']) || $user->hasRole('Admin'));
    }
}
