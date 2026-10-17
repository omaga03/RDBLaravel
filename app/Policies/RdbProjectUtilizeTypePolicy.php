<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbProjectUtilizeType;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectUtilizeTypePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasRole('Admin Utilize') || $user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.View'])) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function view(?User $user, RdbProjectUtilizeType $rdbProjectUtilizeType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.View']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.Create']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function update(?User $user, RdbProjectUtilizeType $rdbProjectUtilizeType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.Update']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function delete(?User $user, RdbProjectUtilizeType $rdbProjectUtilizeType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function restore(?User $user, RdbProjectUtilizeType $rdbProjectUtilizeType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function forceDelete(?User $user, RdbProjectUtilizeType $rdbProjectUtilizeType): bool
    {
        return $user && ($user->hasPermission(['MasterData.Manage', 'RdbProjectUtilize.Delete']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }
}