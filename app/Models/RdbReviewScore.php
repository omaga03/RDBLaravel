<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbReviewScore extends Model
{
    protected $table = 'rdb_review_score';

    protected $fillable = [
        'assignment_id',
        'criteria_id',
        'score',
        'pass_status',
        'comment',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function assignment()
    {
        return $this->belongsTo(RdbReviewAssignment::class, 'assignment_id', 'id');
    }

    public function criteria()
    {
        return $this->belongsTo(RdbReviewCriteria::class, 'criteria_id', 'id');
    }
}
