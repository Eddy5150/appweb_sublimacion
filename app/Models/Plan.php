<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'precio_mensual',
        'precio_anual',
        'max_usuarios',
        'max_productos',
        'max_clientes',
        'crm_incluido',
        'reportes_avanzados',
        'exportacion_datos',
        'api_integraciones',
        'soporte_prioritario',
        'caracteristicas',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_mensual' => 'decimal:2',
            'precio_anual' => 'decimal:2',
            'max_usuarios' => 'integer',
            'max_productos' => 'integer',
            'max_clientes' => 'integer',
            'crm_incluido' => 'boolean',
            'reportes_avanzados' => 'boolean',
            'exportacion_datos' => 'boolean',
            'api_integraciones' => 'boolean',
            'soporte_prioritario' => 'boolean',
            'caracteristicas' => 'array',
            'activo' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
