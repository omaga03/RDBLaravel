<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbProjectFiles;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectFilesPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Project') || $user->hasPermission(['MasterData.Manage', 'RdbProject.View'])) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProject.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function view(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProject.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProject.Create']) || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function update(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProject.Update']) || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function delete(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProject.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function restore(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProject.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }

    public function forceDelete(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProject.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Project'));
    }
}
