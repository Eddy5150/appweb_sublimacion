<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmActionsQueue extends Model
{
    use BelongsToCompany;

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->forCurrentCompany()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    protected $table = 'crm_actions_queue';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'crm_ai_message_id',
        'action_type',
        'payload',
        'status',
        'scheduled_at',
        'sent_at',
        'revenue_impact',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'revenue_impact' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(CrmAiMessage::class, 'crm_ai_message_id');
    }
}
