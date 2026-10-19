<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasPermission('User Manager')) {
            return true;
        }
    }

    public function viewDashboard(User $user): bool
    {
        return true;
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission('User Manager') || $user->hasRole('Admin'));
    }

    public function view(?User $user, User $model): bool
    {
        return $user && ($user->hasPermission('User Manager') || $user->hasRole('Admin') || $user->id === $model->id);
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('User Manager') || $user->hasRole('Admin'));
    }

    public function update(?User $user, User $model): bool
    {
        return $user && ($user->hasPermission('User Manager') || $user->hasRole('Admin') || $user->id === $model->id);
    }

    public function delete(?User $user, User $model): bool
    {
        return $user && ($user->hasPermission('User Manager') || $user->hasRole('Admin'));
    }

    public function restore(?User $user, User $model): bool
    {
        return $user && ($user->hasPermission('User Manager') || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, User $model): bool
    {
        return $user && ($user->hasPermission('User Manager') || $user->hasRole('Admin'));
    }
}
