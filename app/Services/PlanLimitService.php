<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class PlanLimitService
{
    public function canCreateUser(Company $company, int $newCount = 1): bool
    {
        $plan = $this->getActivePlan($company);
        if (! $plan) {
            return false;
        }

        $currentCount = User::where('company_id', $company->id)->count();

        return ($currentCount + $newCount) <= $plan->max_usuarios;
    }

    public function canCreateProducto(Company $company, int $newCount = 1): bool
    {
        $plan = $this->getActivePlan($company);
        if (! $plan) {
            return false;
        }

        $currentCount = $company->productos()->count();

        return ($currentCount + $newCount) <= $plan->max_productos;
    }

    public function canCreateCliente(Company $company, int $newCount = 1): bool
    {
        $plan = $this->getActivePlan($company);
        if (! $plan) {
            return false;
        }

        $currentCount = $company->clientes()->count();

        return ($currentCount + $newCount) <= $plan->max_clientes;
    }

    public function tieneCrm(Company $company): bool
    {
        $plan = $this->getActivePlan($company);

        return $plan && $plan->crm_incluido;
    }

    public function tieneReportesAvanzados(Company $company): bool
    {
        $plan = $this->getActivePlan($company);

        return $plan && $plan->reportes_avanzados;
    }

    public function tieneExportacionDatos(Company $company): bool
    {
        $plan = $this->getActivePlan($company);

        return $plan && $plan->exportacion_datos;
    }

    public function tieneApiIntegraciones(Company $company): bool
    {
        $plan = $this->getActivePlan($company);

        return $plan && $plan->api_integraciones;
    }

    public function getActivePlan(Company $company): ?Plan
    {
        $subscription = Subscription::where('company_id', $company->id)
            ->whereIn('estado', ['activa', 'prueba'])
            ->latest()
            ->first();

        if ($subscription && $subscription->plan) {
            return $subscription->plan;
        }

        return Plan::where('slug', $company->plan)->first();
    }

    public function checkCompanyLimits(Company $company): array
    {
        $plan = $this->getActivePlan($company);

        if (! $plan) {
            return [
                'within_limits' => false,
                'message' => 'No hay un plan activo para esta empresa.',
            ];
        }

        $usuariosActuales = User::where('company_id', $company->id)->count();
        $productosActuales = $company->productos()->count();
        $clientesActuales = $company->clientes()->count();

        return [
            'within_limits' => true,
            'plan' => $plan->nombre,
            'limits' => [
                'usuarios' => ['max' => $plan->max_usuarios, 'actual' => $usuariosActuales, 'disponible' => $plan->max_usuarios - $usuariosActuales],
                'productos' => ['max' => $plan->max_productos, 'actual' => $productosActuales, 'disponible' => $plan->max_productos - $productosActuales],
                'clientes' => ['max' => $plan->max_clientes, 'actual' => $clientesActuales, 'disponible' => $plan->max_clientes - $clientesActuales],
            ],
            'features' => [
                'crm' => $plan->crm_incluido,
                'reportes_avanzados' => $plan->reportes_avanzados,
                'exportacion_datos' => $plan->exportacion_datos,
                'api_integraciones' => $plan->api_integraciones,
                'soporte_prioritario' => $plan->soporte_prioritario,
            ],
        ];
    }
}
