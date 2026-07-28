<?php

namespace App\Models\Traits;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where($this->getTable().'.company_id', $companyId);
    }

    public function scopeForCurrentCompany(Builder $query): Builder
    {
        if ($companyId = auth()->user()?->company_id) {
            return $query->where($this->getTable().'.company_id', $companyId);
        }

        return $query;
    }

    public function scopeWithoutCompany(Builder $query): Builder
    {
        return $query->withoutGlobalScope(CompanyScope::class);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        /** @var Model $this */
        $field ??= $this->getRouteKeyName();

        return $this->forCurrentCompany()->where($field, $value)->firstOrFail();
    }

    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (Model $model) {
            if (! $model->company_id) {
                if (auth()->check()) {
                    $model->company_id = auth()->user()->company_id;
                } elseif ($configCompanyId = config('app.company_id')) {
                    $model->company_id = $configCompanyId;
                }
            }
        });
    }
}
