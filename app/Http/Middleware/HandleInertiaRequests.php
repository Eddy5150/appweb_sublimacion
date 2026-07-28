<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $companyData = null;
        $planLimits = null;

        if ($user && $user->company) {
            $company = $user->company;
            $companyData = $company->toArray();

            $planLimits = app(PlanLimitService::class)->checkCompanyLimits($company);
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'company' => $companyData,
            'planLimits' => $planLimits,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
