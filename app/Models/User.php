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
    public function hasRole($role): bool
    {
        return in_array($role, $this->getAllRoles());
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
