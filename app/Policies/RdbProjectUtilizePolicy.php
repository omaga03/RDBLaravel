<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbProjectUtilize;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectUtilizePolicy
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
            $user->hasPermission(['RdbProjectUtilize.View', 'Utilize']) || 
            $user->hasRole(['Admin', 'Admin Utilize', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function view(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        if (!$user) return false;

        $isDomainAdmin = $user->hasRole(['Admin', 'Admin Utilize', 'Admin Research', 'Utilize', 'The Creator']) || $user->id === 129;
        if ($isDomainAdmin) {
            return true;
        }

        // Faculty coordinator can view works within their faculty
        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbProjectUtilize->department_id)) {
            return true;
        }

        // Researcher can view only if direct researcher, creator, or author of linked project
        if ($user->hasRole('Researcher') || $user->researcher_id) {
            $isCreator = (int)$rdbProjectUtilize->user_created === (int)$user->id;
            if ($isCreator) {
                return true;
            }
            if ($user->researcher_id) {
                $isDirect = (int)$rdbProjectUtilize->researcher_id === (int)$user->researcher_id;
                $isProjectAuthor = $rdbProjectUtilize->project && $rdbProjectUtilize->project->rdbProjectWorks()->where('rdb_project_work.researcher_id', $user->researcher_id)->exists();
                return $isDirect || $isProjectAuthor;
            }
            return false;
        }

        return $user->hasPermission(['RdbProjectUtilize.View', 'Utilize']);
    }

    public function create(?User $user): bool
    {
        return $user && (
            $user->hasPermission(['RdbProjectUtilize.Create', 'Utilize']) || 
            $user->hasRole(['Admin', 'Admin Utilize', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function update(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        if (!$user) return false;

        $isDomainAdmin = $user->hasRole(['Admin', 'Admin Utilize', 'Admin Research', 'The Creator']) || $user->id === 129;
        if ($isDomainAdmin) {
            return true;
        }

        // Strict lock when published or pending review or unlock requested
        if (in_array($rdbProjectUtilize->workflow_status, ['published', 'pending_review', 'unlock_requested'])) {
            return false;
        }

        if ($user->hasRole('Faculty Coordinator') && $rdbProjectUtilize->project && $user->canAccessDepartment($rdbProjectUtilize->project->department_id)) {
            return true;
        }

        if ($user->researcher_id) {
            $isAuthor = $rdbProjectUtilize->project && $rdbProjectUtilize->project->researchers()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
            $isCreator = (int)$rdbProjectUtilize->user_created === (int)$user->id;
            if ($isAuthor || $isCreator) {
                return true;
            }
        }

        return false;
    }

    public function requestUnlock(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        if (!$user || $rdbProjectUtilize->workflow_status !== 'published') {
            return false;
        }

        // Domain admins and Super Admin can edit directly — no need to request unlock!
        if ($user->hasRole(['Admin', 'Admin Utilize', 'Admin Research', 'The Creator']) || $user->id === 129) {
            return false;
        }

        if ($user->hasRole('Faculty Coordinator') && $rdbProjectUtilize->project && $user->canAccessDepartment($rdbProjectUtilize->project->department_id)) {
            return true;
        }

        if ($user->researcher_id) {
            $isAuthor = $rdbProjectUtilize->project && $rdbProjectUtilize->project->researchers()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
            $isCreator = (int)$rdbProjectUtilize->user_created === (int)$user->id;
            return $isAuthor || $isCreator;
        }

        return false;
    }

    public function approveUnlock(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        if (!$user) return false;
        return $user->hasRole(['Admin', 'Admin Utilize', 'Admin Research']) || $user->id === 129;
    }

    public function delete(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        if (!$user) return false;

        if ($user->hasPermission(['RdbProjectUtilize.Delete', 'Utilize']) || $user->hasRole(['Admin', 'Admin Utilize'])) {
            return true;
        }

        if ($user->researcher_id && (int)$rdbProjectUtilize->user_created === (int)$user->id) {
            return in_array($rdbProjectUtilize->workflow_status, ['draft', 'rejected', null]);
        }

        return false;
    }

    public function restore(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.Delete', 'Utilize']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbProjectUtilize $rdbProjectUtilize): bool
    {
        return $user && ($user->hasPermission(['RdbProjectUtilize.Delete', 'Utilize']) || $user->hasRole('Admin'));
    }
}