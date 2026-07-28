<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAiMessage extends Model
{
    use BelongsToCompany;

    protected $table = 'crm_ai_messages';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'mensaje',
        'tipo',
        'estado',
        'accion_recomendada',
        'contexto',
        'enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'contexto' => 'array',
            'enviado_en' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
