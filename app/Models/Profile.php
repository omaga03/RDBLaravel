<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profile';
    protected $primaryKey = 'user_id';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'prefix_id',
        'name',
        'gender',
        'department_id',
        'branch_id',
        'birthdate',
        'address',
        'workaddress',
        'tel',
        'mobile',
        'fax',
        'picture',
        'public_email',
        'gravatar_email',
        'gravatar_id',
        'location',
        'website',
        'bio',
        'created_at',
        'updated_at',
    ];
}
