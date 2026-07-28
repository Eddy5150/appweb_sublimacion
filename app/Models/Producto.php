<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'company_id',
        'nombre',
        'categoria',
        'descripcion',
        'precio',
        'stock',
        'veces_vendido',
        'imagen',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function movimientosStock(): HasMany
    {
        return $this->hasMany(StockMovimiento::class);
    }

    public function variantes(): HasMany
    {
        return $this->hasMany(ProductoVariante::class);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ProductoImagen::class)->orderBy('orden');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo(Builder $query, int $umbral = 10): Builder
    {
        return $query->where('stock', '>', 0)->where('stock', '<=', $umbral);
    }

    public function scopeAgotados(Builder $query): Builder
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopePorCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }

    public function getValorInventarioAttribute(): float
    {
        return round((float) $this->stock * (float) $this->precio, 2);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->forCurrentCompany()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }
}
