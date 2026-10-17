<?php

namespace App\Policies;

use App\Models\RdbProject;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectPolicy
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
        return $user && $user->hasRole('Admin');
    }

    public function view(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user || !$user->hasRole('Admin')) {
            return false;
        }

        $creator = $rdbProject->createdBy;

        return $creator && $creator->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user || !$user->hasRole('Admin')) {
            return false;
        }

        $creator = $rdbProject->createdBy;

        return $creator && $creator->hasRole('Admin');
    }

    public function delete(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user || !$user->hasRole('Admin')) {
            return false;
        }

        $creator = $rdbProject->createdBy;

        return $creator && $creator->hasRole('Admin');
    }

    public function restore(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user || !$user->hasRole('Admin')) {
            return false;
        }

        $creator = $rdbProject->createdBy;

        return $creator && $creator->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user || !$user->hasRole('Admin')) {
            return false;
        }

        $creator = $rdbProject->createdBy;

        return $creator && $creator->hasRole('Admin');
    }
}