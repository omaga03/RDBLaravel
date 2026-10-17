<?php

namespace App\Policies;

use App\Models\RdbPublished;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbPublishedPolicy
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
        return $user && $user->hasRole('Published');
    }

    public function view(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user || !$user->hasRole('Published')) {
            return false;
        }

        $creator = $rdbPublished->createdBy;

        return $creator && $creator->hasRole('Published');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Published');
    }

    public function update(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user || !$user->hasRole('Published')) {
            return false;
        }

        $creator = $rdbPublished->createdBy;

        return $creator && $creator->hasRole('Published');
    }

    public function delete(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user || !$user->hasRole('Published')) {
            return false;
        }

        $creator = $rdbPublished->createdBy;

        return $creator && $creator->hasRole('Published');
    }

    public function restore(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user || !$user->hasRole('Published')) {
            return false;
        }

        $creator = $rdbPublished->createdBy;

        return $creator && $creator->hasRole('Published');
    }

    public function forceDelete(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user || !$user->hasRole('Published')) {
            return false;
        }

        $creator = $rdbPublished->createdBy;

        return $creator && $creator->hasRole('Published');
    }
}