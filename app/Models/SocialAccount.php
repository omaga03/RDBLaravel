<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table = 'social_account';
    protected $primaryKey = 'id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'user_id',
        'provider',
        'client_id',
        'data',
        'code',
        'created_at',
        'email',
        'username',
    ];
}
