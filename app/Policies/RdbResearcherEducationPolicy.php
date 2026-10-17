<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbResearcherEducation;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbResearcherEducationPolicy
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

    public function view(?User $user, RdbResearcherEducation $rdbResearcherEducation): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbResearcherEducation $rdbResearcherEducation): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbResearcherEducation $rdbResearcherEducation): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbResearcherEducation $rdbResearcherEducation): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbResearcherEducation $rdbResearcherEducation): bool
    {
        return $user && $user->hasRole('Admin');
    }
}
