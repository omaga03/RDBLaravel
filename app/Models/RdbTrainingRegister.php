<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbTrainingRegister extends Model
{
    protected $table = 'rdb_training_register';
    protected $primaryKey = 'treg_id';
    public $timestamps = false;

    public function training()
    {
        return $this->belongsTo(RdbTraining::class, 'tra_id', 'tra_id');
    }

    protected $fillable = [
        'tra_id',
        'treg_perfix',
        'treg_name',
        'treg_department',
        'treg_position',
        'treg_address',
        'treg_tel',
        'treg_email',
        'treg_session',
        'created_at',
        'updated_at',
    ];
}
