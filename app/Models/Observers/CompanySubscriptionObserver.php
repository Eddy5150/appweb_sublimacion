<?php

namespace App\Models\Observers;

use App\Models\Company;
use App\Models\Subscription;

class CompanySubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        if ($subscription->plan) {
            Company::where('id', $subscription->company_id)->update([
                'plan' => $subscription->plan->slug,
            ]);
        }
    }

    public function updated(Subscription $subscription): void
    {
        if ($subscription->isDirty('plan_id') && $subscription->plan) {
            Company::where('id', $subscription->company_id)->update([
                'plan' => $subscription->plan->slug,
            ]);
        }

        if ($subscription->isDirty('estado') && in_array($subscription->estado, ['vencida', 'cancelada'])) {
            $hasActive = Subscription::where('company_id', $subscription->company_id)
                ->whereIn('estado', ['activa', 'prueba'])
                ->where('id', '!=', $subscription->id)
                ->exists();

            if (! $hasActive) {
                Company::where('id', $subscription->company_id)->update([
                    'estado' => 'suspendida',
                ]);
            }
        }
    }
}
