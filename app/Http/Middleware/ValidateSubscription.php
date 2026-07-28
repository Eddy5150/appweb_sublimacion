<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $companyId = $request->user()->company_id;

        if (! $companyId) {
            return $next($request);
        }

        $company = Company::find($companyId);

        if (! $company) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tu empresa no está registrada.']);
        }

        if ($company->estado === 'suspendida') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tu suscripción ha expirado. Contacta al administrador.']);
        }

        if ($company->estado === 'cancelada') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tu cuenta ha sido cancelada.']);
        }

        $hasActiveSubscription = Subscription::where('company_id', $companyId)
            ->whereIn('estado', ['activa', 'prueba'])
            ->where(function ($query) {
                $query->whereNull('fecha_renovacion')
                    ->orWhere('fecha_renovacion', '>=', now());
            })
            ->exists();

        if (! $hasActiveSubscription) {
            $totalSubscriptions = Subscription::where('company_id', $companyId)->count();

            if ($totalSubscriptions > 0) {
                $company->update(['estado' => 'suspendida']);

                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => 'Tu suscripción ha expirado.']);
            }
        }

        return $next($request);
    }
}
