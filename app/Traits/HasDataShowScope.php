<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasDataShowScope
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('data_show', 1);
        });
    }
}
