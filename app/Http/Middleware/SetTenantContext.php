<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $companyId = $user->company_id;

            config(['app.company_id' => $companyId]);

            $user->loadMissing('company');

            if ($companyId && $user->company) {
                config(['app.company' => $user->company]);

                $company = $user->company;
                Company::setTenantCache($company);
            }
        }

        return $next($request);
    }
}
