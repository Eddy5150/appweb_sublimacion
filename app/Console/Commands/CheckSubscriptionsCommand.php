<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckSubscriptionsCommand extends Command
{
    protected $signature = 'saas:check-subscriptions';

    protected $description = 'Verifica suscripciones vencidas y suspende empresas automáticamente';

    public function handle(): int
    {
        $vencidas = Subscription::whereIn('estado', ['activa', 'prueba'])
            ->where('fecha_renovacion', '<', now())
            ->with('company')
            ->get();

        $count = 0;
        foreach ($vencidas as $subscription) {
            $subscription->update(['estado' => 'vencida']);

            if ($subscription->company) {
                $subscription->company->update(['estado' => 'suspendida']);
            }

            $count++;
        }

        $this->info("Se marcaron {$count} suscripciones como vencidas.");

        return Command::SUCCESS;
    }
}
