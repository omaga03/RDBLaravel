<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RdbReviewCriteria extends Model
{
    protected $table = 'rdb_review_criteria';

    protected $fillable = [
        'template_id',
        'category',
        'name',
        'description',
        'item_type',
        'scale_max',
        'scale_step',
        'weight',
        'is_required',
        'order_seq',
    ];

    protected $casts = [
        'scale_max' => 'integer',
        'scale_step' => 'integer',
        'weight' => 'decimal:2',
        'is_required' => 'boolean',
        'order_seq' => 'integer',
    ];

    /**
     * Compute array of scale options based on scale_max and scale_step.
     * E.g. scale_max=25, scale_step=5 => [5, 10, 15, 20, 25]
     */
    public function getScaleOptionsAttribute(): array
    {
        $max = (int) ($this->scale_max ?: ($this->template?->scale_max ?: 5));
        $step = max(1, (int) ($this->scale_step ?: 1));

        $options = [];
        for ($val = $step; $val <= $max; $val += $step) {
            $options[] = $val;
        }

        if (empty($options) || end($options) !== $max) {
            $options[] = $max;
        }

        return $options;
    }

    public function template()
    {
        return $this->belongsTo(RdbReviewTemplate::class, 'template_id', 'id');
    }

    public function scores()
    {
        return $this->hasMany(RdbReviewScore::class, 'criteria_id', 'id');
    }
}
