<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthRule extends Model
{
    protected $table = 'auth_rule';
    protected $primaryKey = 'name';
    public $timestamps = false; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'data',
        'created_at',
        'updated_at',
    ];
}
