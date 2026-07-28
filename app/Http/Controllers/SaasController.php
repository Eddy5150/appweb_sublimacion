<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SaasController extends Controller
{
    public function dashboard(): Response
    {
        $paymentService = app(PaymentService::class);

        $totalCompanies = Company::count();
        $totalUsers = User::withoutCompanyScope()->count();
        $totalIngresos = Subscription::withoutCompanyScope()->whereIn('estado', ['activa', 'prueba'])->sum('precio_pagado');
        $empresasActivas = Company::where('estado', 'activa')->count();

        $mrr = $paymentService->calculateMrr();
        $churnRate = $paymentService->getChurnRate();
        $growth = $paymentService->getGrowthMetrics();

        $ingresosPorPlan = Subscription::withoutCompanyScope()
            ->selectRaw('plan_id, SUM(precio_pagado) as total')
            ->whereIn('estado', ['activa', 'prueba'])
            ->groupBy('plan_id')
            ->with('plan')
            ->get()
            ->map(fn ($s) => [
                'plan' => $s->plan?->nombre ?? 'Sin plan',
                'total' => (float) $s->total,
            ]);

        $empresasRecientes = Company::latest()->take(5)->get()->map(fn ($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre,
            'email' => $c->email,
            'plan' => $c->plan,
            'estado' => $c->estado,
            'created_at' => $c->created_at->format('d/m/Y'),
        ]);

        $subscriptionsPorEstado = Subscription::withoutCompanyScope()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get()
            ->pluck('total', 'estado');

        return Inertia::render('saas/Dashboard', [
            'stats' => [
                'totalCompanies' => $totalCompanies,
                'totalUsers' => $totalUsers,
                'totalIngresos' => $totalIngresos,
                'empresasActivas' => $empresasActivas,
                'mrr' => $mrr,
                'churnRate' => $churnRate,
                'growth' => $growth,
            ],
            'ingresosPorPlan' => $ingresosPorPlan,
            'empresasRecientes' => $empresasRecientes,
            'subscriptionsPorEstado' => $subscriptionsPorEstado,
        ]);
    }

    public function companies(): Response
    {
        $companies = Company::withCount('usuarios')
            ->with('subscription.plan')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'email' => $c->email,
                'slug' => $c->slug,
                'plan' => $c->plan,
                'estado' => $c->estado,
                'usuarios_count' => $c->usuarios_count,
                'subscription_plan' => $c->subscription?->plan?->nombre,
                'created_at' => $c->created_at->format('d/m/Y'),
            ]);

        $plans = Plan::where('activo', true)->orderBy('precio_mensual')->get();

        return Inertia::render('saas/Companies', [
            'companies' => $companies,
            'plans' => $plans,
        ]);
    }

    public function updateCompany(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'plan' => 'required|string|in:free,basic,pro,premium',
            'estado' => 'required|in:activa,suspendida,cancelada',
            'slug' => 'nullable|string|max:255|unique:companies,slug,'.$company->id,
        ]);

        $company->update($validated);

        return redirect()->route('saas.companies')
            ->with('success', 'Empresa actualizada correctamente.');
    }

    /**
     * @throws ValidationException
     */
    public function toggleCompany(Company $company): RedirectResponse
    {
        if ($company->estado === 'activa') {
            $company->update(['estado' => 'suspendida']);
            $message = 'Empresa suspendida.';
        } else {
            $company->update(['estado' => 'activa']);
            $message = 'Empresa activada.';
        }

        return redirect()->route('saas.companies')
            ->with('success', $message);
    }

    /**
     * @throws ValidationException
     */
    public function deleteCompany(Company $company): RedirectResponse
    {
        if ($company->subscription()->exists()) {
            throw ValidationException::withMessages([
                'company' => 'No se puede eliminar una empresa con suscripciones activas.',
            ]);
        }

        DB::transaction(function () use ($company) {
            User::withoutCompanyScope()->where('company_id', $company->id)->delete();
            $company->delete();
        });

        return redirect()->route('saas.companies')
            ->with('success', 'Empresa eliminada correctamente.');
    }

    public function plans(): Response
    {
        $plans = Plan::orderBy('precio_mensual')->get();

        return Inertia::render('saas/Plans', [
            'plans' => $plans,
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'descripcion' => 'nullable|string',
            'precio_mensual' => 'required|numeric|min:0',
            'precio_anual' => 'required|numeric|min:0',
            'max_usuarios' => 'required|integer|min:1',
            'max_productos' => 'required|integer|min:1',
            'max_clientes' => 'required|integer|min:1',
            'crm_incluido' => 'boolean',
            'reportes_avanzados' => 'boolean',
            'exportacion_datos' => 'boolean',
            'api_integraciones' => 'boolean',
            'soporte_prioritario' => 'boolean',
            'caracteristicas' => 'nullable|array',
        ]);

        Plan::create($validated);

        return redirect()->route('saas.plans')
            ->with('success', 'Plan creado exitosamente.');
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,'.$plan->id,
            'descripcion' => 'nullable|string',
            'precio_mensual' => 'required|numeric|min:0',
            'precio_anual' => 'required|numeric|min:0',
            'max_usuarios' => 'required|integer|min:1',
            'max_productos' => 'required|integer|min:1',
            'max_clientes' => 'required|integer|min:1',
            'crm_incluido' => 'boolean',
            'reportes_avanzados' => 'boolean',
            'exportacion_datos' => 'boolean',
            'api_integraciones' => 'boolean',
            'soporte_prioritario' => 'boolean',
            'caracteristicas' => 'nullable|array',
        ]);

        $plan->update($validated);

        return redirect()->route('saas.plans')
            ->with('success', 'Plan actualizado exitosamente.');
    }

    public function togglePlan(Plan $plan): RedirectResponse
    {
        $plan->update(['activo' => ! $plan->activo]);

        return redirect()->route('saas.plans')
            ->with('success', $plan->activo ? 'Plan activado.' : 'Plan desactivado.');
    }

    public function subscriptions(): Response
    {
        $subscriptions = Subscription::withoutCompanyScope()->with(['company', 'plan'])
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($s) => [
                'id' => $s->id,
                'company_nombre' => $s->company?->nombre,
                'plan_nombre' => $s->plan?->nombre,
                'estado' => $s->estado,
                'ciclo_pago' => $s->ciclo_pago,
                'precio_pagado' => (float) $s->precio_pagado,
                'fecha_renovacion' => $s->fecha_renovacion?->format('d/m/Y'),
                'fecha_inicio' => $s->fecha_inicio?->format('d/m/Y'),
                'created_at' => $s->created_at->format('d/m/Y'),
            ]);

        $plans = Plan::where('activo', true)->get();
        $companies = Company::where('estado', 'activa')->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('saas/Subscriptions', [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
            'companies' => $companies,
        ]);
    }

    public function storeSubscription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'plan_id' => 'required|exists:plans,id',
            'ciclo_pago' => 'required|in:mensual,anual',
            'estado' => 'required|in:activa,prueba,cancelada,vencida',
            'fecha_inicio' => 'nullable|date',
            'fecha_renovacion' => 'nullable|date|after:fecha_inicio',
            'precio_pagado' => 'nullable|numeric|min:0',
            'metodo_pago' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $precio = $validated['ciclo_pago'] === 'anual' ? $plan->precio_anual : $plan->precio_mensual;

        $validated['precio_pagado'] = $validated['precio_pagado'] ?? $precio;

        Subscription::create($validated);

        Company::where('id', $validated['company_id'])->update([
            'plan' => $plan->slug,
        ]);

        return redirect()->route('saas.subscriptions')
            ->with('success', 'Suscripción creada exitosamente.');
    }
}
