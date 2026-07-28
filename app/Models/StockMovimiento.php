<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovimiento extends Model
{
    use BelongsToCompany;

    protected $table = 'stock_movements';

    protected $fillable = [
        'company_id',
        'producto_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'referencia_type',
        'referencia_id',
        'nota',
        'user_id',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function referencia()
    {
        return $this->morphTo();
    }
}
