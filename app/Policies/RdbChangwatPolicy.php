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
        if ($user->hasRole('The Creator')) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return false;
    }

    public function view(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return false;
    }

    public function create(?User $user): bool
    {
        return false;
    }

    public function update(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return false;
    }

    public function delete(?User $user, RdbChangwat $rdbChangwat): bool
    {
        return false;
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
