<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmEventLog extends Model
{
    use BelongsToCompany;

    protected $table = 'crm_event_logs';

    protected $fillable = [
        'company_id',
        'tipo',
        'causable_type',
        'causable_id',
        'cliente_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function causable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
