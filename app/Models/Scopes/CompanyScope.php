<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($companyId = $this->getCompanyId()) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutCompanyScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    private function getCompanyId(): ?int
    {
        if (auth()->check()) {
            return auth()->user()->company_id;
        }

        return config('app.company_id');
    }
}
