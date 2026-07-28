<?php

namespace App\Observers;

use App\Helpers\TenantCache;
use Illuminate\Database\Eloquent\Model;

class TenantObserver
{
    public function created(Model $model): void
    {
        if (config('cache.default') !== 'array') {
            TenantCache::forget(class_basename($model).'_count');
        }
    }

    public function updated(Model $model): void
    {
        if (config('cache.default') !== 'array') {
            TenantCache::forget(class_basename($model).'_'.$model->getKey());
        }
    }

    public function deleted(Model $model): void
    {
        if (config('cache.default') !== 'array') {
            TenantCache::forget(class_basename($model).'_count');
            TenantCache::forget(class_basename($model).'_'.$model->getKey());
        }
    }

    public function restored(Model $model): void
    {
        if (config('cache.default') !== 'array') {
            TenantCache::forget(class_basename($model).'_count');
        }
    }
}
