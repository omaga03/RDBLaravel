<?php

namespace App\Policies;

use App\Models\ResearchConference;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResearchConferencePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('The Creator') || $user->hasRole('Admin') || $user->hasPermission(['Event', 'MasterData.Manage'])) {
            return true;
        }
    }

    public function viewAny(?User $user): bool
    {
        return $user && ($user->hasPermission(['Event', 'MasterData.Manage']) || $user->hasRole('Admin'));
    }

    public function view(?User $user, ResearchConference $researchConference): bool
    {
        return $user && ($user->hasPermission(['Event', 'MasterData.Manage']) || $user->hasRole('Admin'));
    }

    public function create(?User $user): bool
    {
        return $user && ($user->hasPermission(['Event', 'MasterData.Manage']) || $user->hasRole('Admin'));
    }

    public function update(?User $user, ResearchConference $researchConference): bool
    {
        return $user && ($user->hasPermission(['Event', 'MasterData.Manage']) || $user->hasRole('Admin'));
    }

    public function delete(?User $user, ResearchConference $researchConference): bool
    {
        return $user && ($user->hasPermission(['Event', 'MasterData.Manage']) || $user->hasRole('Admin'));
    }

    public function restore(?User $user, ResearchConference $researchConference): bool
    {
        return $user && ($user->hasPermission(['Event', 'MasterData.Manage']) || $user->hasRole('Admin'));
    }

    public function forceDelete(?User $user, ResearchConference $researchConference): bool
    {
        return $user && ($user->hasPermission(['Event', 'MasterData.Manage']) || $user->hasRole('Admin'));
    }
}