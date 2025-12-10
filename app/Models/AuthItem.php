<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthItem extends Model
{
    protected $table = 'auth_item';
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Handle manually if needed

    protected $fillable = [
        'name',
        'type',
        'description',
        'rule_name',
        'data',
        'created_at',
        'updated_at',
    ];
}
