<?php

namespace App\Policies;

use App\Models\RdbResearcher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbResearcherPolicy
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

    public function view(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && $user->hasRole('Admin');
    }
}