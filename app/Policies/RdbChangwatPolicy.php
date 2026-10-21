<?php

namespace App\Policies;

use App\Models\RdbChangwat;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbChangwatPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasPermission('MasterData.Manage') || $user->hasRole('Admin Utilize')) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole(['Admin', 'Admin Utilize']));
    }

    public function view(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole(['Admin', 'Admin Utilize']));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole(['Admin', 'Admin Utilize']));
    }

    public function update(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole(['Admin', 'Admin Utilize']));
    }

    public function delete(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole(['Admin', 'Admin Utilize']));
    }

    public function restore(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return false;
    }

    public function forceDelete(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return false;
    }
}
