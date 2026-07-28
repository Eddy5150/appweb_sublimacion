<?php

namespace App\Http\Controllers;

use App\Models\CrmActionsQueue;
use App\Models\CrmAlert;
use App\Models\CrmAutomationRule;
use App\Models\CrmComunicacion;
use App\Models\CrmEventLog;
use App\Services\CrmAlertService;
use App\Services\CrmAutoSalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmAutomationController extends Controller
{
    public function __construct(
        private CrmAlertService $alertService,
    ) {}

    public function reglasIndex(): Response
    {
        $reglas = CrmAutomationRule::forCurrentCompany()->orderBy('prioridad', 'desc')
            ->orderBy('nombre')
            ->get()
            ->map(fn (CrmAutomationRule $r) => [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'descripcion' => $r->descripcion,
                'activa' => $r->activa,
                'condicion_tipo' => $r->condicion_tipo,
                'condicion_valor' => $r->condicion_valor,
                'accion_tipo' => $r->accion_tipo,
                'accion_valor' => $r->accion_valor,
                'prioridad' => $r->prioridad,
                'created_at' => $r->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('crm/Automations', [
            'reglas' => $reglas,
        ]);
    }

    public function reglasStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean',
            'condicion_tipo' => 'required|string|max:100',
            'condicion_valor' => 'required|string|max:255',
            'condicion_extra' => 'nullable|array',
            'accion_tipo' => 'required|string|max:100',
            'accion_valor' => 'required|string|max:255',
            'accion_extra' => 'nullable|array',
            'prioridad' => 'integer|min:0',
        ]);

        CrmAutomationRule::create(array_merge($validated, ['company_id' => auth()->user()->company_id]));

        return redirect()->route('crm.automations')
            ->with('success', 'Regla de automatización creada.');
    }

    public function reglasUpdate(Request $request, CrmAutomationRule $regla): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean',
            'condicion_tipo' => 'required|string|max:100',
            'condicion_valor' => 'required|string|max:255',
            'condicion_extra' => 'nullable|array',
            'accion_tipo' => 'required|string|max:100',
            'accion_valor' => 'required|string|max:255',
            'accion_extra' => 'nullable|array',
            'prioridad' => 'integer|min:0',
        ]);

        $regla->update($validated);

        return redirect()->route('crm.automations')
            ->with('success', 'Regla actualizada.');
    }

    public function reglasDestroy(CrmAutomationRule $regla): RedirectResponse
    {
        $regla->delete();

        return redirect()->route('crm.automations')
            ->with('success', 'Regla eliminada.');
    }

    public function reglasToggle(CrmAutomationRule $regla): RedirectResponse
    {
        $regla->update(['activa' => ! $regla->activa]);

        return redirect()->route('crm.automations')
            ->with('success', $regla->activa ? 'Regla activada.' : 'Regla desactivada.');
    }

    public function alertsIndex(): Response
    {
        $alerts = CrmAlert::forCurrentCompany()->with('cliente')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn (CrmAlert $a) => [
                'id' => $a->id,
                'cliente_id' => $a->cliente_id,
                'cliente_nombre' => $a->cliente?->nombre,
                'tipo' => $a->tipo,
                'titulo' => $a->titulo,
                'mensaje' => $a->mensaje,
                'leida' => $a->leida,
                'leida_en' => $a->leida_en?->format('Y-m-d H:i:s'),
                'created_at' => $a->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('crm/Alerts', [
            'alerts' => $alerts,
            'pendientes' => $this->alertService->pendientes(),
        ]);
    }

    public function alertsMarkRead(CrmAlert $alert): RedirectResponse
    {
        $this->alertService->marcarLeida($alert);

        return redirect()->route('crm.alerts')
            ->with('success', 'Alerta marcada como leída.');
    }

    public function alertsMarkAllRead(): RedirectResponse
    {
        $count = $this->alertService->marcarTodasLeidas();

        return redirect()->route('crm.alerts')
            ->with('success', "{$count} alertas marcadas como leídas.");
    }

    public function eventsIndex(): Response
    {
        $events = CrmEventLog::forCurrentCompany()->with('cliente')
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->through(fn (CrmEventLog $e) => [
                'id' => $e->id,
                'tipo' => $e->tipo,
                'causable_type' => class_basename($e->causable_type ?? ''),
                'causable_id' => $e->causable_id,
                'cliente_id' => $e->cliente_id,
                'cliente_nombre' => $e->cliente?->nombre,
                'data' => $e->data,
                'created_at' => $e->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('crm/Events', [
            'events' => $events,
            'tipos' => CrmEventLog::forCurrentCompany()
                ->select('tipo')
                ->distinct()
                ->orderBy('tipo')
                ->pluck('tipo'),
        ]);
    }

    public function comunicacionesIndex(): Response
    {
        $comunicaciones = CrmComunicacion::forCurrentCompany()->with('cliente')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn (CrmComunicacion $c) => [
                'id' => $c->id,
                'cliente_id' => $c->cliente_id,
                'cliente_nombre' => $c->cliente?->nombre,
                'tipo' => $c->tipo,
                'canal' => $c->canal,
                'asunto' => $c->asunto,
                'mensaje' => $c->mensaje,
                'enviado' => $c->enviado,
                'enviado_en' => $c->enviado_en?->format('Y-m-d H:i:s'),
                'created_at' => $c->created_at->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('crm/Comunicaciones', [
            'comunicaciones' => $comunicaciones,
        ]);
    }

    public function salesActions(CrmAutoSalesService $sales): Response
    {
        return Inertia::render('crm/SalesActions', [
            'pending' => $sales->getPendingActions(20),
            'history' => $sales->getActionHistory(10),
            'stats' => $sales->getSalesStats(),
        ]);
    }

    public function salesApprove(CrmAutoSalesService $sales, CrmActionsQueue $action): RedirectResponse
    {
        $sales->approveAction($action);

        return redirect()->route('crm.sales.actions')
            ->with('success', 'Acción aprobada y lista para ejecutar.');
    }

    public function salesExecute(CrmAutoSalesService $sales, CrmActionsQueue $action): RedirectResponse
    {
        $sales->executeAction($action);

        return redirect()->route('crm.sales.actions')
            ->with('success', 'Acción ejecutada exitosamente.');
    }

    public function salesPipeline(CrmAutoSalesService $sales): RedirectResponse
    {
        $result = $sales->autoPipeline();

        return redirect()->route('crm.sales.actions')
            ->with('success', "Pipeline ejecutado: {$result['total']} acciones ({$result['from_agent']} del agente, {$result['from_triggers']} por triggers).");
    }
}
