<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Producto;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyBillingController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PlanLimitService $planLimitService,
    ) {}

    public function index(): Response
    {
        $company = auth()->user()->company;

        $subscription = Subscription::where('company_id', $company->id)
            ->with('plan')
            ->latest()
            ->first();

        $plans = Plan::where('activo', true)->orderBy('precio_mensual')->get();

        $usage = [
            'usuarios_actual' => User::forCurrentCompany()->count(),
            'productos_actual' => Producto::forCurrentCompany()->count(),
            'clientes_actual' => $company->clientes()->count(),
        ];

        $limits = $this->planLimitService->checkCompanyLimits($company);

        return Inertia::render('saas/billing/Index', [
            'company' => $company,
            'currentPlan' => $subscription?->plan,
            'subscription' => $subscription,
            'plans' => $plans,
            'usage' => [
                ...$usage,
                'planes' => $limits,
            ],
        ]);
    }

    public function upgrade(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if ($plan->id === optional($company->subscription)->plan_id) {
            return redirect()->route('company.billing')
                ->with('info', 'Ya estás en este plan.');
        }

        $this->paymentService->createSubscription($company, $plan);

        return redirect()->route('company.billing')
            ->with('success', 'Plan actualizado a '.$plan->nombre.' exitosamente.');
    }

    public function cancel(): RedirectResponse
    {
        $company = auth()->user()->company;
        $this->paymentService->cancelSubscription($company);

        return redirect()->route('company.billing')
            ->with('success', 'Suscripción cancelada.');
    }
}
