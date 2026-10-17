<?php

namespace App\Policies;

use App\Models\RdbPublishedBranch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbPublishedBranchPolicy
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
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasPermission('MasterData.Manage')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User|null  $user
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\RdbPublishedBranch  $rdbPublishedBranch
     * @return bool
     */
    public function view(?User $user, RdbPublishedBranch $rdbPublishedBranch): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User|null  $user
     * @return bool
     */
    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\RdbPublishedBranch  $rdbPublishedBranch
     * @return bool
     */
    public function update(?User $user, RdbPublishedBranch $rdbPublishedBranch): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\RdbPublishedBranch  $rdbPublishedBranch
     * @return bool
     */
    public function delete(?User $user, RdbPublishedBranch $rdbPublishedBranch): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\RdbPublishedBranch  $rdbPublishedBranch
     * @return bool
     */
    public function restore(?User $user, RdbPublishedBranch $rdbPublishedBranch): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\RdbPublishedBranch  $rdbPublishedBranch
     * @return bool
     */
    public function forceDelete(?User $user, RdbPublishedBranch $rdbPublishedBranch): bool
    {
        return $user && ($user->hasPermission('MasterData.Manage') || $user->hasRole('Admin'));
    }
}
