<?php

namespace App\Policies;

use App\Models\AuthAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuthAssignmentPolicy
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
        return false;
    }

    public function view(?User $user, AuthAssignment $authAssignment): bool
    {
        return false;
    }

    public function create(?User $user): bool
    {
        return false;
    }

    public function update(?User $user, AuthAssignment $authAssignment): bool
    {
        return false;
    }

    public function delete(?User $user, AuthAssignment $authAssignment): bool
    {
        return false;
    }

    public function restore(?User $user, AuthAssignment $authAssignment): bool
    {
        return false;
    }

    public function forceDelete(?User $user, AuthAssignment $authAssignment): bool
    {
        return false;
    }
}
