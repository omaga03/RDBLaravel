<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RdbProjectFiles;
use Illuminate\Auth\Access\HandlesAuthorization;

class RdbProjectFilesPolicy
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

    public function view(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function create(?User $user): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function update(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function delete(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function restore(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && $user->hasRole('Admin');
    }

    public function forceDelete(?User $user, RdbProjectFiles $rdbProjectFiles): bool
    {
        return $user && $user->hasRole('Admin');
    }
}
