<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbProjectUtilize;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectUtilizePolicy
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
        return $user && ($user->hasPermission(['RdbProjectUtilize.View', 'Utilize']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function view(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.View', 'Utilize']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.Create', 'Utilize']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function update(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.Update', 'Utilize']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function delete(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.Delete', 'Utilize']) || $user->hasRole('Admin') || $user->hasRole('Admin Utilize'));
    }

    public function restore(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.Delete', 'Utilize']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.Delete', 'Utilize']) || $user->hasRole('Admin'));
    }
}