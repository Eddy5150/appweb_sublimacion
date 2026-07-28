<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(
        string $tipo,
        string $modulo,
        string $accion,
        ?Model $registro = null,
        ?array $detalles = null,
    ): ActivityLog {
        return ActivityLog::create([
            'company_id' => auth()->user()?->company_id,
            'user_id' => auth()->id(),
            'tipo' => $tipo,
            'modulo' => $modulo,
            'accion' => $accion,
            'registro_type' => $registro ? get_class($registro) : null,
            'registro_id' => $registro?->getKey(),
            'detalles' => $detalles,
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function created(string $modulo, string $accion, ?Model $registro = null, ?array $detalles = null): ActivityLog
    {
        return $this->log('create', $modulo, $accion, $registro, $detalles);
    }

    public function updated(string $modulo, string $accion, ?Model $registro = null, ?array $detalles = null): ActivityLog
    {
        return $this->log('update', $modulo, $accion, $registro, $detalles);
    }

    public function deleted(string $modulo, string $accion, ?Model $registro = null, ?array $detalles = null): ActivityLog
    {
        return $this->log('delete', $modulo, $accion, $registro, $detalles);
    }

    public function exported(string $modulo, string $accion, ?array $detalles = null): ActivityLog
    {
        return $this->log('export', $modulo, $accion, null, $detalles);
    }
}
