<?php

namespace App\Policies;

use App\Models\ResearchNews;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResearchNewsPolicy
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
        return $user && $user->hasRole('Admin');
    }

    public function view(?User $user, ResearchNews $researchNews): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, ResearchNews $researchNews): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, ResearchNews $researchNews): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, ResearchNews $researchNews): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, ResearchNews $researchNews): bool
    {
        return $user && $user->hasRole('Admin');
    }
}