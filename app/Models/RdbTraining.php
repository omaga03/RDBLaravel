<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbTraining extends Model
{
    protected $table = 'rdb_training';
    protected $primaryKey = 'tra_id';
    public $timestamps = true; // Assuming no standard created_at/updated_at, change if needed

    protected $fillable = [
        'tra_name',
        'tra_description',
        'tra_property',
        'tra_fee',
        'tra_datetimestart',
        'tra_datetimeend',
        'tra_place',
        'tra_applicant',
        'tra_note',
        'tra_url',
        'user_updated',
        'user_created',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'user_created');
    }

    public function updater() {
        return $this->belongsTo(User::class, 'user_updated');
    }
}
