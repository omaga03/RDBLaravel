<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbStrategic;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbStrategicPolicy
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
        return $user && $user->hasRole('Admin');
    }

    public function view(?User $user, RdbStrategic $rdbStrategic): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbStrategic $rdbStrategic): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbStrategic $rdbStrategic): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbStrategic $rdbStrategic): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbStrategic $rdbStrategic): bool
    {
        return $user && $user->hasRole('Admin');
    }
}