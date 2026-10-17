<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbProjectStatus; // Import the model
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RdbProjectStatusPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator')) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, RdbProjectStatus $rdbProjectStatus): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return false;
    }

    public function update(?User $user, RdbProjectStatus $rdbProjectStatus): bool
    {
        return false;
    }

    public function delete(?User $user, RdbProjectStatus $rdbProjectStatus): bool
    {
        return false;
    }

    public function restore(?User $user, RdbProjectStatus $rdbProjectStatus): bool
    {
        return false;
    }

    public function forceDelete(?User $user, RdbProjectStatus $rdbProjectStatus): bool
    {
        return false;
    }
}
