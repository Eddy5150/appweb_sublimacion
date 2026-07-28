<?php

namespace App\Services;

use App\Models\CrmAlert;

class CrmAlertService
{
    public function pendientes(): int
    {
        return CrmAlert::where('leida', false)->count();
    }

    public function marcarLeida(CrmAlert $alert): void
    {
        $alert->update([
            'leida' => true,
            'leida_en' => now(),
        ]);
    }

    public function marcarTodasLeidas(): int
    {
        return CrmAlert::where('leida', false)->update([
            'leida' => true,
            'leida_en' => now(),
        ]);
    }

    public function ultimas(int $limit = 20): mixed
    {
        return CrmAlert::with('cliente')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(fn (CrmAlert $alert) => [
                'id' => $alert->id,
                'cliente_id' => $alert->cliente_id,
                'cliente_nombre' => $alert->cliente?->nombre,
                'tipo' => $alert->tipo,
                'titulo' => $alert->titulo,
                'mensaje' => $alert->mensaje,
                'leida' => $alert->leida,
                'leida_en' => $alert->leida_en?->format('Y-m-d H:i:s'),
                'created_at' => $alert->created_at->format('Y-m-d H:i:s'),
            ]);
    }
}
