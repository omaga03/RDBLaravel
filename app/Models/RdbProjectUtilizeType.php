<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbProjectUtilizeType extends Model
{
    protected $table = 'rdb_project_utilize_type';
    protected $primaryKey = 'utz_type_id';
    public $timestamps = true; // Changed to true

    protected $fillable = [
        'utz_typr_name',
        'utz_type_index',
        'user_created',
        'user_updated',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'user_updated');
    }

    /**
     * Accessor for utz_type_name to normalize legacy spelling utz_typr_name
     */
    public function getUtzTypeNameAttribute(): ?string
    {
        return $this->utz_typr_name;
    }
}
