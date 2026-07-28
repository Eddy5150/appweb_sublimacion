<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoVariante extends Model
{
    use BelongsToCompany;

    protected $table = 'producto_variantes';

    protected $fillable = [
        'company_id',
        'producto_id',
        'tipo',
        'valor',
        'precio_ajuste',
        'stock',
        'sku',
        'imagen',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_ajuste' => 'decimal:2',
            'stock' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function getPrecioFinalAttribute(): float
    {
        return (float) $this->producto->precio + (float) $this->precio_ajuste;
    }
}
