<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        $logs = ActivityLog::forCurrentCompany()
            ->with('user')
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($log) => [
                'id' => $log->id,
                'tipo' => $log->tipo,
                'modulo' => $log->modulo,
                'accion' => $log->accion,
                'user_name' => $log->user?->name,
                'detalles' => $log->detalles,
                'ip' => $log->ip,
                'created_at' => $log->created_at->format('d/m/Y H:i'),
            ]);

        $stats = [
            'total' => ActivityLog::forCurrentCompany()->count(),
            'hoy' => ActivityLog::forCurrentCompany()->whereDate('created_at', today())->count(),
            'usuarios_activos' => ActivityLog::forCurrentCompany()
                ->whereDate('created_at', today())
                ->distinct('user_id')
                ->count('user_id'),
        ];

        return Inertia::render('activity/Index', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }
}
