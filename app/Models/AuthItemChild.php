<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthItemChild extends Model
{
    protected $table = 'auth_item_child';
    protected $primaryKey = 'parent'; // Eloquent doesn't support composite keys well, but we only read
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'parent',
        'child',
    ];
}
