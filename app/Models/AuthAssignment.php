<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthAssignment extends Model
{
    protected $table = 'auth_assignment';
    protected $primaryKey = 'item_name'; // Eloquent limitation with composite keys
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // We handle created_at manually if needed, or set to true if it matches

    protected $fillable = [
        'item_name',
        'user_id',
        'created_at',
    ];
}
