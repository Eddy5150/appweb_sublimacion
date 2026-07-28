<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CrmAutomationRule extends Model
{
    use BelongsToCompany;

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->forCurrentCompany()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    protected $table = 'crm_automation_rules';

    protected $fillable = [
        'company_id',
        'nombre',
        'descripcion',
        'activa',
        'condicion_tipo',
        'condicion_valor',
        'condicion_extra',
        'accion_tipo',
        'accion_valor',
        'accion_extra',
        'prioridad',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'condicion_extra' => 'array',
            'accion_extra' => 'array',
            'prioridad' => 'integer',
        ];
    }
}
