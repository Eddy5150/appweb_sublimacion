<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmProductoRecomendado extends Model
{
    use BelongsToCompany;

    protected $table = 'crm_producto_recomendados';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'producto_id',
        'score',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
