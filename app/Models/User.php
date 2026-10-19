<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $dateFormat = 'U'; // Unix timestamp

    protected $fillable = [
        'name',
        'password',
        'researcher_id',
        'username',
        'email',
        'status',
        'password_hash',
        'auth_key',
        'password_reset_token',
        'account_activation_token',
        'confirmed_at',
        'unconfirmed_email',
        'blocked_at',
        'registration_ip',
        'updated_at',
        'flags',
        'user_created',
        'user_updated',
        'created_at',
    ];

    protected $hidden = [
        'password_hash',
        'auth_key',
    ];

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->auth_key)) {
                $user->auth_key = \Illuminate\Support\Str::random(32);
            }
            if (!isset($user->status)) {
                $user->status = 10;
            }
            if (empty($user->confirmed_at)) {
                $user->confirmed_at = time();
            }
        });
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password_hash'] = \Illuminate\Support\Facades\Hash::needsRehash($value)
                ? \Illuminate\Support\Facades\Hash::make($value)
                : $value;
        }
    }

    public function setNameAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['username'] = $value;
        }
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * The roles assigned to the user.
     */
    public function roles()
    {
        return $this->hasMany(AuthAssignment::class, 'user_id', 'id');
    }

    /**
     * Scoped roles assigned to the user.
     */
    public function roleScopes()
    {
        return $this->hasMany(UserRoleScope::class, 'user_id', 'id')->where('is_active', true);
    }

    /**
     * Get currently active role scope for this user session.
     */
    public function getActiveRoleScope(): ?UserRoleScope
    {
        $activeScopeId = session('active_role_scope_id');
        if ($activeScopeId) {
            $scope = $this->roleScopes()->with('department')->find($activeScopeId);
            if ($scope) {
                return $scope;
            }
        }

        $first = $this->roleScopes()->with('department')->first();
        if ($first) {
            session(['active_role_scope_id' => $first->id]);
            return $first;
        }

        return null;
    }

    /**
     * Check if user can manage or review data in a specific department/faculty.
     */
    public function canAccessDepartment(?int $departmentId): bool
    {
        if ($this->hasRole('The Creator') || $this->hasRole('Admin Research')) {
            return true;
        }

        $activeScope = $this->getActiveRoleScope();
        if ($activeScope) {
            if (in_array($activeScope->role_name, ['The Creator', 'Admin Project', 'Admin Research', 'Admin Publication', 'Admin Utilize', 'Admin Researcher'])) {
                return true;
            }

            if ($activeScope->department_id && $activeScope->department_id == $departmentId) {
                return true;
            }
        }

        if ($this->researcher && $this->researcher->department_id == $departmentId) {
            return true;
        }

        return false;
    }

    /**
     * @var array|null A cache for all roles (direct and inherited) for the user.
     */
    protected $allRoles = null;

    /**
     * Get all roles assigned to the user, including inherited roles.
     * Caches the result for the duration of the request.
     *
     * @return array
     */
    private function getAllRoles(): array
    {
        if ($this->allRoles !== null) {
            return $this->allRoles;
        }

        $directRoles = $this->roles->pluck('item_name')->toArray();
        if (empty($directRoles) && $this->researcher_id) {
            $directRoles[] = 'Researcher';
        }
        $allRoles = $directRoles;
        $newRoles = $directRoles;

        do {
            $children = AuthItemChild::whereIn('parent', $newRoles)->pluck('child')->toArray();
            $newRoles = array_diff($children, $allRoles);
            $allRoles = array_merge($allRoles, $newRoles);
        } while (!empty($newRoles));

        $this->allRoles = array_unique($allRoles);

        return $this->allRoles;
    }

    /**
     * Check if the user has a specific role, including inherited roles.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return !empty(array_intersect($role, $this->getAllRoles()));
        }
        return in_array($role, $this->getAllRoles());
    }

    /**
     * Check if the user has a specific permission or any of the given permissions.
     * Superadmin ('The Creator') automatically has all permissions.
     *
     * @param string|array $permissions
     * @return bool
     */
    public function hasPermission(string|array $permissions): bool
    {
        if ($this->hasRole('The Creator')) {
            return true;
        }

        $all = $this->getAllRoles();

        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (in_array($permission, $all)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($permissions, $all);
    }

    public function researcher()
    {
        return $this->belongsTo(RdbResearcher::class, 'researcher_id', 'researcher_id');
    }

    public function getNameAttribute()
    {
        if ($this->researcher) {
            $prefix = $this->researcher->prefix->prefix_name ?? '';
            return trim($prefix . $this->researcher->researcher_fname . ' ' . $this->researcher->researcher_lname);
        }
        return $this->username;
    }
}
