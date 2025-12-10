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

    public function roles()
    {
        return $this->hasMany(AuthAssignment::class, 'user_id', 'id');
    }

    public function hasRole($role)
    {
        return $this->roles->contains('item_name', $role);
    }
}
