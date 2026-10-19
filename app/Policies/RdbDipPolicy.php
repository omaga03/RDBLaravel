<?php

namespace App\Policies;

use App\Models\RdbDip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbDipPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($ability === 'requestUnlock') {
            return null; // Admins edit directly; requestUnlock is strictly for non-admin creators/researchers
        }

        if ($user->hasRole('The Creator')) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && (
            $user->hasPermission(['RdbDip.View', 'Dip']) || 
            $user->hasRole(['Admin', 'Admin Dip', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function view(?User $user, RdbDip $rdbDip): bool
    {
        if (!$user) return false;

        if ($user->hasRole(['Admin', 'Admin Dip', 'Admin Research', 'Dip', 'The Creator']) || $user->id === 129) {
            return true;
        }

        // Faculty coordinator can view works within their faculty
        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbDip->department_id)) {
            return true;
        }

        // Researcher can view only if direct researcher or creator
        if ($user->hasRole('Researcher') || $user->researcher_id) {
            $isCreator = (int)$rdbDip->user_created === (int)$user->id;
            if ($isCreator) {
                return true;
            }
            if ($user->researcher_id) {
                return (int)$rdbDip->researcher_id === (int)$user->researcher_id;
            }
            return false;
        }

        return $user->hasPermission(['RdbDip.View', 'Dip']);
    }

    public function create(?User $user): bool
    {
        return $user && (
            $user->hasPermission(['RdbDip.Create', 'Dip']) || 
            $user->hasRole(['Admin', 'Admin Dip', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function update(?User $user, RdbDip $rdbDip): bool
    {
        if (!$user) return false;

        $isDomainAdmin = $user->hasRole(['Admin', 'Admin Dip', 'Admin Research', 'The Creator']) || $user->id === 129;
        if ($isDomainAdmin) {
            return true;
        }

        // Strict lock when published or pending review or unlock requested
        if (in_array($rdbDip->workflow_status, ['published', 'pending_review', 'unlock_requested'])) {
            return false;
        }

        if ($user->hasRole('Faculty Coordinator') && $rdbDip->project && $user->canAccessDepartment($rdbDip->project->department_id)) {
            return true;
        }

        if ($user->researcher_id) {
            $isDirect = (int)$rdbDip->researcher_id === (int)$user->researcher_id;
            $isCreator = (int)$rdbDip->user_created === (int)$user->id;
            if ($isDirect || $isCreator) {
                return true;
            }
        }

        return false;
    }

    public function requestUnlock(?User $user, RdbDip $rdbDip): bool
    {
        if (!$user || $rdbDip->workflow_status !== 'published') {
            return false;
        }

        // Domain admins and Super Admin can edit directly — no need to request unlock!
        if ($user->hasRole(['Admin', 'Admin Dip', 'Admin Research', 'The Creator']) || $user->id === 129) {
            return false;
        }

        if ($user->hasRole('Faculty Coordinator') && $rdbDip->project && $user->canAccessDepartment($rdbDip->project->department_id)) {
            return true;
        }

        if ($user->researcher_id) {
            $isDirect = (int)$rdbDip->researcher_id === (int)$user->researcher_id;
            $isCreator = (int)$rdbDip->user_created === (int)$user->id;
            return $isDirect || $isCreator;
        }

        return false;
    }

    public function approveUnlock(?User $user, RdbDip $rdbDip): bool
    {
        if (!$user) return false;
        return $user->hasRole(['Admin', 'Admin Dip', 'Admin Research']) || $user->id === 129;
    }

    public function delete(?User $user, RdbDip $rdbDip): bool
    {
        if (!$user) return false;

        if ($user->hasPermission(['RdbDip.Delete', 'Dip']) || $user->hasRole(['Admin', 'Admin Dip'])) {
            return true;
        }

        if ($user->researcher_id && (int)$rdbDip->user_created === (int)$user->id) {
            return in_array($rdbDip->workflow_status, ['draft', 'rejected', null]);
        }

        return false;
    }

    public function restore(?User $user, RdbDip $rdbDip): bool
    {
        return $user && ($user->hasPermission(['RdbDip.Delete', 'Dip']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbDip $rdbDip): bool
    {
        return $user && ($user->hasPermission(['RdbDip.Delete', 'Dip']) || $user->hasRole('Admin'));
    }
}
