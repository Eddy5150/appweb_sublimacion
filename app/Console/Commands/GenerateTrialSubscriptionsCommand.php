<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Console\Command;

class GenerateTrialSubscriptionsCommand extends Command
{
    protected $signature = 'saas:generate-trials';

    protected $description = 'Crea suscripciones de prueba para empresas sin ninguna suscripción';

    public function handle(): int
    {
        $freePlan = Plan::where('slug', 'free')->first();
        if (! $freePlan) {
            $this->warn('No se encontró el plan Free.');

            return Command::FAILURE;
        }

        $companies = Company::whereDoesntHave('subscriptions')->get();
        $count = 0;

        foreach ($companies as $company) {
            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $freePlan->id,
                'estado' => 'prueba',
                'ciclo_pago' => 'mensual',
                'fecha_inicio' => now(),
                'fecha_renovacion' => now()->addDays(14),
                'precio_pagado' => 0,
            ]);
            $count++;
        }

        $this->info("Se crearon {$count} suscripciones de prueba.");

        return Command::SUCCESS;
    }
}
