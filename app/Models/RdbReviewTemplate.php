<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbReviewTemplate extends Model
{
    protected $table = 'rdb_review_template';

    protected $fillable = [
        'title',
        'description',
        'evaluation_mode',
        'scale_max',
        'pass_threshold',
        'is_active',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'scale_max' => 'integer',
        'pass_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function criteria()
    {
        return $this->hasMany(RdbReviewCriteria::class, 'template_id', 'id')
            ->orderBy('order_seq', 'asc')
            ->orderBy('id', 'asc');
    }

    public function assignments()
    {
        return $this->hasMany(RdbReviewAssignment::class, 'template_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
