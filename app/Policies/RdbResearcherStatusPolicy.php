<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbResearcherStatus; // Import the model
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RdbResearcherStatusPolicy
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

    public function view(?User $user, RdbResearcherStatus $rdbResearcherStatus): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbResearcherStatus $rdbResearcherStatus): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbResearcherStatus $rdbResearcherStatus): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbResearcherStatus $rdbResearcherStatus): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbResearcherStatus $rdbResearcherStatus): bool
    {
        return $user && $user->hasRole('Admin');
    }
}
