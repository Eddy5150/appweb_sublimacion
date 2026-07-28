<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('login', 'login.*', 'register', 'register.*', 'password.*', 'two-factor.*', 'passkey.*')) {
            return $next($request);
        }

        if (! auth()->check()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user->is_super_admin) {
            return $next($request);
        }

        $companyId = $user->company_id;

        if (! $companyId) {
            if ($user->tokens()->count() > 0) {
                $user->tokens()->delete();
            }
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'No tienes una empresa asignada.']);
        }

        $request->session()->put('company_id', $companyId);
        config(['app.company_id' => $companyId]);

        $user->loadMissing('company');

        if (! $user->company) {
            if ($user->tokens()->count() > 0) {
                $user->tokens()->delete();
            }
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu empresa no está registrada.']);
        }

        config(['app.company' => $user->company]);
        Company::setTenantCache($user->company);

        $company = $user->company;

        if ($company->estado === 'suspendida') {
            if ($user->tokens()->count() > 0) {
                $user->tokens()->delete();
            }
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu suscripción ha expirado. Contacta al administrador.']);
        }

        if ($company->estado === 'cancelada') {
            if ($user->tokens()->count() > 0) {
                $user->tokens()->delete();
            }
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta ha sido cancelada.']);
        }

        $hasActiveSubscription = Subscription::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->whereIn('estado', ['activa', 'prueba'])
            ->where(function ($query) {
                $query->whereNull('fecha_renovacion')
                    ->orWhere('fecha_renovacion', '>=', now());
            })
            ->exists();

        if (! $hasActiveSubscription) {
            $company->update(['estado' => 'suspendida']);

            if ($user->tokens()->count() > 0) {
                $user->tokens()->delete();
            }
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu suscripción ha expirado.']);
        }

        $request->attributes->set('company_id', $companyId);
        $request->attributes->set('company', $company);

        return $next($request);
    }
}
