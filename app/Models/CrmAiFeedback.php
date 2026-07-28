<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAiFeedback extends Model
{
    use BelongsToCompany;

    protected $table = 'crm_ai_feedback';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'action_taken',
        'result',
        'revenue_impact',
        'notas',
        'contexto_agente',
    ];

    protected function casts(): array
    {
        return [
            'revenue_impact' => 'decimal:2',
            'contexto_agente' => 'array',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
