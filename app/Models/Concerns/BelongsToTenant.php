<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Auto-fill business_id on create
        static::creating(function ($model) {
            if (! $model->business_id && auth()->check()) {
                $model->business_id = session('current_business_id') ?? auth()->user()->businesses()->first()?->id;
            }
        });

        // Apply global scope
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && session('current_business_id')) {
                $builder->where($builder->getModel()->getTable() . '.business_id', session('current_business_id'));
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
}
