<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmComunicacion extends Model
{
    use BelongsToCompany;

    protected $table = 'crm_comunicaciones';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'tipo',
        'canal',
        'asunto',
        'mensaje',
        'enviado',
        'enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'enviado' => 'boolean',
            'enviado_en' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
