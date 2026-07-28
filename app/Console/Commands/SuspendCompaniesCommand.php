<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class SuspendCompaniesCommand extends Command
{
    protected $signature = 'saas:suspend-expired';

    protected $description = 'Suspende empresas sin suscripción activa o con suscripción vencida';

    public function handle(): int
    {
        $companies = Company::where('estado', 'activa')
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->whereIn('estado', ['activa', 'prueba'])
                    ->where('fecha_renovacion', '>=', now());
            })
            ->get();

        $count = 0;
        foreach ($companies as $company) {
            $company->update(['estado' => 'suspendida']);
            $count++;
        }

        $this->info("Se suspendieron {$count} empresas sin suscripción activa.");

        return Command::SUCCESS;
    }
}
