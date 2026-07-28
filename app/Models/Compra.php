<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use BelongsToCompany;

    protected $table = 'compras';

    protected $fillable = [
        'company_id',
        'codigo',
        'proveedor_id',
        'proveedor_nombre',
        'total',
        'estado',
        'tipo_pago',
        'factura',
        'notas',
        'fecha_compra',
        'cancelado_en',
        'cancelado_por',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'fecha_compra' => 'date',
            'cancelado_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Compra $compra) {
            if (empty($compra->codigo)) {
                $compra->codigo = 'CMP-'.strtoupper(uniqid());
            }
        });
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('codigo', 'like', "%{$search}%")
                ->orWhere('proveedor_nombre', 'like', "%{$search}%");
        });
    }

    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        if (blank($estado)) {
            return $query;
        }

        return $query->where('estado', $estado);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->forCurrentCompany()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }
}
