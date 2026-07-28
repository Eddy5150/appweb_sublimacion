<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaApertura extends Model
{
    use BelongsToCompany;

    protected $table = 'caja_aperturas';

    protected $fillable = [
        'company_id',
        'user_id',
        'monto_inicial',
        'monto_final',
        'apertura_en',
        'cierre_en',
        'estado',
        'notas_apertura',
        'notas_cierre',
    ];

    protected function casts(): array
    {
        return [
            'monto_inicial' => 'decimal:2',
            'monto_final' => 'decimal:2',
            'apertura_en' => 'datetime',
            'cierre_en' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class, 'caja_apertura_id');
    }

    public function getIngresosAttribute(): float
    {
        return (float) $this->movimientos()->where('tipo', 'ingreso')->sum('monto');
    }

    public function getEgresosAttribute(): float
    {
        return (float) $this->movimientos()->where('tipo', 'egreso')->sum('monto');
    }

    public function getSaldoActualAttribute(): float
    {
        return (float) $this->monto_inicial + $this->ingresos - $this->egresos;
    }
}
