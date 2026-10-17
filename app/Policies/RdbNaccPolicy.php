<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbNacc;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbNaccPolicy
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

    public function view(?User $user, RdbNacc $rdbNacc): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbNacc $rdbNacc): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbNacc $rdbNacc): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbNacc $rdbNacc): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbNacc $rdbNacc): bool
    {
        return $user && $user->hasRole('Admin');
    }
}
