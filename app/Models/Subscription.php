<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'plan_id',
        'estado',
        'fecha_inicio',
        'fecha_renovacion',
        'fecha_cancelacion',
        'ciclo_pago',
        'precio_pagado',
        'metodo_pago',
        'referencia_pago',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_renovacion' => 'datetime',
            'fecha_cancelacion' => 'datetime',
            'precio_pagado' => 'decimal:2',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
