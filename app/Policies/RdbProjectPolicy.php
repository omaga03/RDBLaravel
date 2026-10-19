<?php

namespace App\Policies;

use App\Models\RdbProject;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectPolicy
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
            $user->hasPermission('RdbProject.View') || 
            $user->hasRole(['Admin', 'Admin Project', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function view(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user) return false;

        if ($user->hasRole(['Admin', 'Admin Project', 'Admin Research', 'The Creator']) || $user->id === 129) {
            return true;
        }

        // Faculty coordinator can view works within their faculty
        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbProject->department_id)) {
            return true;
        }

        // Researcher can view only if author or creator
        if ($user->hasRole('Researcher') || $user->researcher_id) {
            $isCreator = (int)$rdbProject->user_created === (int)$user->id;
            if ($isCreator) {
                return true;
            }
            if ($user->researcher_id) {
                return $rdbProject->rdbProjectWorks()->where('rdb_project_work.researcher_id', $user->researcher_id)->exists();
            }
            return false;
        }

        return $user->hasPermission('RdbProject.View');
    }

    public function create(?User $user): bool
    {
        return $user && (
            $user->hasPermission('RdbProject.Create') || 
            $user->hasRole(['Admin', 'Admin Project', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function update(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user) return false;

        $isDomainAdmin = $user->hasRole(['Admin', 'Admin Project', 'Admin Research', 'The Creator']) || $user->id === 129;
        if ($isDomainAdmin) {
            return true;
        }

        // Strict lock when published or pending review or unlock requested:
        // Researchers and non-domain-admins CANNOT edit directly once published!
        if (in_array($rdbProject->workflow_status, ['published', 'pending_review', 'unlock_requested'])) {
            return false;
        }

        // Faculty coordinator can update projects within their faculty
        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbProject->department_id)) {
            return true;
        }

        // Researcher can update if they are author/collaborator or creator
        if ($user->researcher_id) {
            $isAuthor = $rdbProject->researchers()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
            $isCreator = (int)$rdbProject->user_created === (int)$user->id;
            if ($isAuthor || $isCreator) {
                return true;
            }
        }

        return false;
    }

    public function requestUnlock(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user || $rdbProject->workflow_status !== 'published') {
            return false;
        }

        // Domain admins and Super Admin can edit directly — no need to request unlock!
        if ($user->hasRole(['Admin', 'Admin Project', 'Admin Research', 'The Creator']) || $user->id === 129) {
            return false;
        }

        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbProject->department_id)) {
            return true;
        }

        if ($user->researcher_id) {
            $isAuthor = $rdbProject->researchers()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
            $isCreator = (int)$rdbProject->user_created === (int)$user->id;
            return $isAuthor || $isCreator;
        }

        return false;
    }

    public function approveUnlock(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user) return false;
        return $user->hasRole(['Admin', 'Admin Project', 'Admin Research']) || $user->id === 129;
    }

    public function delete(?User $user, RdbProject $rdbProject): bool
    {
        if (!$user) return false;

        if ($user->hasPermission('RdbProject.Delete') || $user->hasRole(['Admin', 'Admin Project'])) {
            return true;
        }

        // Researcher can delete draft projects they created
        if ($user->researcher_id && (int)$rdbProject->user_created === (int)$user->id) {
            return in_array($rdbProject->workflow_status, ['draft', 'rejected', null]);
        }

        return false;
    }

    public function restore(?User $user, RdbProject $rdbProject): bool
    {
        return $user && ($user->hasPermission('RdbProject.Delete') || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbProject $rdbProject): bool
    {
        return $user && ($user->hasPermission('RdbProject.Delete') || $user->hasRole('Admin'));
    }
}