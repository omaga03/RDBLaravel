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
            $table = $builder->getModel()->getTable();
            $builder->where($table . '.data_show', 1);
        });
    }
}
