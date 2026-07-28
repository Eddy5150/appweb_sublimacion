<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAlert extends Model
{
    use BelongsToCompany;

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->forCurrentCompany()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    protected $table = 'crm_alerts';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'tipo',
        'titulo',
        'mensaje',
        'leida',
        'leida_en',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'leida_en' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
