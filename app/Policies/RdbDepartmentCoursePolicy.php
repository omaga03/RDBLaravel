<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbDepartmentCourse;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbDepartmentCoursePolicy
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

    public function view(?User $user, RdbDepartmentCourse $rdbDepartmentCourse): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbDepartmentCourse $rdbDepartmentCourse): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbDepartmentCourse $rdbDepartmentCourse): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbDepartmentCourse $rdbDepartmentCourse): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbDepartmentCourse $rdbDepartmentCourse): bool
    {
        return $user && $user->hasRole('Admin');
    }
}
