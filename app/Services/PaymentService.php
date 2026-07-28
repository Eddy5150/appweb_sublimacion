<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createSubscription(Company $company, Plan $plan, string $cycle = 'mensual'): Subscription
    {
        return DB::transaction(function () use ($company, $plan, $cycle) {
            $price = $cycle === 'anual' ? $plan->precio_anual : $plan->precio_mensual;
            $now = now();

            Subscription::where('company_id', $company->id)
                ->whereIn('estado', ['activa', 'prueba'])
                ->update(['estado' => 'cancelada']);

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'estado' => 'activa',
                'ciclo_pago' => $cycle,
                'fecha_inicio' => $now,
                'fecha_renovacion' => $cycle === 'anual' ? $now->addYear() : $now->addMonth(),
                'precio_pagado' => $price,
                'metodo_pago' => 'sistema',
            ]);

            $company->update(['plan' => $plan->slug, 'estado' => 'activa']);

            return $subscription;
        });
    }

    public function cancelSubscription(Company $company): ?Subscription
    {
        $subscription = Subscription::where('company_id', $company->id)
            ->whereIn('estado', ['activa', 'prueba'])
            ->latest()
            ->first();

        if ($subscription) {
            $subscription->update([
                'estado' => 'cancelada',
                'fecha_cancelacion' => now(),
            ]);

            $company->update(['estado' => 'suspendida']);
        }

        return $subscription;
    }

    public function calculateMrr(): float
    {
        return (float) Subscription::withoutCompanyScope()
            ->whereIn('estado', ['activa', 'prueba'])
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->selectRaw('SUM(CASE WHEN ciclo_pago = "anual" THEN plans.precio_anual / 12 ELSE plans.precio_mensual END) as mrr')
            ->value('mrr') ?? 0;
    }

    public function getChurnRate(): float
    {
        $total = Subscription::withoutCompanyScope()->count();
        if ($total === 0) {
            return 0;
        }

        $canceladas = Subscription::withoutCompanyScope()->where('estado', 'cancelada')->count();

        return round(($canceladas / $total) * 100, 1);
    }

    public function getGrowthMetrics(): array
    {
        $now = now();
        $thisMonth = Company::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $lastMonth = Company::whereMonth('created_at', $now->subMonth()->month)->whereYear('created_at', $now->subMonth()->year)->count();

        $growth = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        return [
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'growth_percent' => $growth,
        ];
    }
}
