<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'pedidos';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_direccion',
        'total',
        'estado',
        'fecha_entrega',
        'codigo',
        'cancelado_en',
        'cancelado_por',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'fecha_entrega' => 'date',
            'cancelado_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Pedido $pedido) {
            if (empty($pedido->codigo)) {
                $pedido->codigo = 'PED-'.strtoupper(uniqid());
            }
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function movimientosStock(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'referencia_id', 'id')
            ->where('referencia_type', static::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('cliente_nombre', 'like', "%{$search}%")
                ->orWhere('cliente_telefono', 'like', "%{$search}%");
        });
    }

    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        if (blank($estado)) {
            return $query;
        }

        return $query->where('estado', $estado);
    }

    public function scopeFechaDesde(Builder $query, ?string $fecha): Builder
    {
        if (blank($fecha)) {
            return $query;
        }

        return $query->whereDate('created_at', '>=', $fecha);
    }

    public function scopeFechaHasta(Builder $query, ?string $fecha): Builder
    {
        if (blank($fecha)) {
            return $query;
        }

        return $query->whereDate('created_at', '<=', $fecha);
    }

    public function scopeTotalMinimo(Builder $query, ?float $minimo): Builder
    {
        if (blank($minimo)) {
            return $query;
        }

        return $query->where('total', '>=', $minimo);
    }

    public function scopeTotalMaximo(Builder $query, ?float $maximo): Builder
    {
        if (blank($maximo)) {
            return $query;
        }

        return $query->where('total', '<=', $maximo);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->forCurrentCompany()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }
}
