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
        if (!$user) return false;

        if ($user->hasRole(['The Creator', 'Admin', 'Admin Research', 'Admin Researcher'])) {
            return true;
        }

        // Regular researchers or faculty coordinators cannot view/manage the full backend researcher directory
        if ($user->hasRole(['Researcher', 'Faculty Coordinator']) || $user->researcher_id) {
            return false;
        }

        return $user->hasPermission('RdbResearcher.View');
    }

    public function view(?User $user, RdbResearcher $rdbResearcher): bool
    {
        if (!$user) return false;

        if ($user->hasRole(['The Creator', 'Admin', 'Admin Research']) || $user->id === 129) {
            return true;
        }

        // Faculty coordinator can view researchers in their faculty
        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbResearcher->department_id)) {
            return true;
        }

        // Regular researcher can view ONLY their own profile in backend
        if ($user->hasRole('Researcher') || $user->researcher_id) {
            return (int)$user->researcher_id === (int)$rdbResearcher->researcher_id;
        }

        return $user->hasPermission('RdbResearcher.View');
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission('RdbResearcher.Create') || $user->hasRole('Admin'));
    }

    public function update(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && (
            $user->hasPermission('RdbResearcher.Update') || 
            $user->hasRole('Admin') || 
            ($user->researcher_id && (int)$user->researcher_id === (int)$rdbResearcher->researcher_id)
        );
    }

    public function delete(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && ($user->hasPermission('RdbResearcher.Delete') || $user->hasRole('Admin'));
    }

    public function restore(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && ($user->hasPermission('RdbResearcher.Delete') || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbResearcher $rdbResearcher): bool
    {
        return $user && ($user->hasPermission('RdbResearcher.Delete') || $user->hasRole('Admin'));
    }
}