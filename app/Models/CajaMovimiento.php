<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaMovimiento extends Model
{
    use BelongsToCompany;

    protected $table = 'caja_movimientos';

    protected $fillable = [
        'company_id',
        'caja_apertura_id',
        'pedido_id',
        'tipo',
        'concepto',
        'monto',
        'metodo_pago',
        'referencia',
        'user_id',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
        ];
    }

    public function cajaApertura(): BelongsTo
    {
        return $this->belongsTo(CajaApertura::class, 'caja_apertura_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
