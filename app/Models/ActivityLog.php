<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use BelongsToCompany;

    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'user_id',
        'tipo',
        'modulo',
        'accion',
        'registro_type',
        'registro_id',
        'detalles',
        'ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'detalles' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registro(): MorphTo
    {
        return $this->morphTo();
    }
}
