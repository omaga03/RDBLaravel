<?php

namespace App\Policies;

use App\Models\RdbPublished;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbPublishedPolicy
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
            $user->hasPermission('RdbPublished.View') || 
            $user->hasRole(['Admin', 'Admin Published', 'Published', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function view(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user) return false;

        if ($user->hasRole(['Admin', 'Admin Published', 'Admin Research', 'Published', 'The Creator']) || $user->id === 129) {
            return true;
        }

        // Faculty coordinator can view works within their faculty
        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbPublished->department_id)) {
            return true;
        }

        // Researcher can view only if direct researcher, author, or creator
        if ($user->hasRole('Researcher') || $user->researcher_id) {
            $isCreator = (int)$rdbPublished->user_created === (int)$user->id;
            if ($isCreator) {
                return true;
            }
            if ($user->researcher_id) {
                $isDirect = (int)$rdbPublished->researcher_id === (int)$user->researcher_id;
                $isAuthor = $rdbPublished->authors()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
                return $isDirect || $isAuthor;
            }
            return false;
        }

        return $user->hasPermission('RdbPublished.View');
    }

    public function create(?User $user): bool
    {
        return $user && (
            $user->hasPermission('RdbPublished.Create') || 
            $user->hasRole(['Admin', 'Admin Published', 'Published', 'Researcher', 'Faculty Coordinator']) ||
            $user->researcher_id
        );
    }

    public function update(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user) return false;

        $isDomainAdmin = $user->hasRole(['Admin', 'Admin Published', 'Admin Research', 'Published', 'The Creator']) || $user->id === 129;
        if ($isDomainAdmin) {
            return true;
        }

        // Strict lock when published or pending review or unlock requested
        if (in_array($rdbPublished->workflow_status, ['published', 'pending_review', 'unlock_requested'])) {
            return false;
        }

        // Faculty coordinator can update works within their faculty
        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbPublished->department_id)) {
            return true;
        }

        // Researcher can update if author or creator
        if ($user->researcher_id) {
            $isDirect = (int)$rdbPublished->researcher_id === (int)$user->researcher_id;
            $isAuthor = $rdbPublished->authors()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
            $isCreator = (int)$rdbPublished->user_created === (int)$user->id;
            if ($isDirect || $isAuthor || $isCreator) {
                return true;
            }
        }

        return false;
    }

    public function requestUnlock(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user || $rdbPublished->workflow_status !== 'published') {
            return false;
        }

        // Domain admins and Super Admin can edit directly — no need to request unlock!
        if ($user->hasRole(['Admin', 'Admin Published', 'Admin Research', 'Published', 'The Creator']) || $user->id === 129) {
            return false;
        }

        if ($user->hasRole('Faculty Coordinator') && $user->canAccessDepartment($rdbPublished->department_id)) {
            return true;
        }

        if ($user->researcher_id) {
            $isDirect = (int)$rdbPublished->researcher_id === (int)$user->researcher_id;
            $isAuthor = $rdbPublished->authors()->where('rdb_researcher.researcher_id', $user->researcher_id)->exists();
            $isCreator = (int)$rdbPublished->user_created === (int)$user->id;
            return $isDirect || $isAuthor || $isCreator;
        }

        return false;
    }

    public function approveUnlock(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user) return false;
        return $user->hasRole(['Admin', 'Admin Published', 'Admin Research', 'Published']) || $user->id === 129;
    }

    public function delete(?User $user, RdbPublished $rdbPublished): bool
    {
        if (!$user) return false;

        if ($user->hasPermission('RdbPublished.Delete') || $user->hasRole(['Admin', 'Admin Published', 'Published'])) {
            return true;
        }

        // Researcher can delete draft works they created
        if ($user->researcher_id && (int)$rdbPublished->user_created === (int)$user->id) {
            return in_array($rdbPublished->workflow_status, ['draft', 'rejected', null]);
        }

        return false;
    }

    public function restore(?User $user, RdbPublished $rdbPublished): bool
    {
        return $user && ($user->hasPermission('RdbPublished.Delete') || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, RdbPublished $rdbPublished): bool
    {
        return $user && ($user->hasPermission('RdbPublished.Delete') || $user->hasRole('Admin'));
    }
}