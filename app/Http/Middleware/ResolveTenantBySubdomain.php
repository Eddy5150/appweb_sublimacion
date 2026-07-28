<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantBySubdomain
{
    protected array $except = [
        'login',
        'login.*',
        'register',
        'register.*',
        'password.*',
        'two-factor.*',
        'passkey.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs($this->except)) {
            return $next($request);
        }

        $host = $request->getHost();
        $baseDomain = config('app.base_domain', parse_url(config('app.url'), PHP_URL_HOST));

        if ($host && $host !== $baseDomain) {
            $parts = explode('.', $host);
            $subdomain = $parts[0] ?? null;

            if ($subdomain && $subdomain !== 'www' && $subdomain !== $baseDomain) {
                $company = Company::where('slug', $subdomain)
                    ->where('estado', 'activa')
                    ->first();

                if ($company) {
                    config(['app.company_id' => $company->id]);
                    config(['app.company' => $company]);

                    if (! $request->user()) {
                        $request->attributes->set('tenant_company', $company);
                    }
                }
            }
        }

        return $next($request);
    }
}
