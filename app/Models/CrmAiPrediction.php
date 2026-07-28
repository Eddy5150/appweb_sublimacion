<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAiPrediction extends Model
{
    use BelongsToCompany;

    protected $table = 'crm_ai_predictions';

    protected $fillable = [
        'company_id',
        'cliente_id',
        'ai_score',
        'purchase_probability',
        'estimated_next_purchase',
        'emotional_state',
        'future_trend',
        'ltv_estimated',
        'churn_risk',
        'features',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'purchase_probability' => 'decimal:2',
            'estimated_next_purchase' => 'date',
            'ltv_estimated' => 'decimal:2',
            'features' => 'array',
            'last_calculated_at' => 'datetime',
            'ai_score' => 'integer',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
