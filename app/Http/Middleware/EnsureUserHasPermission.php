<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'No autenticado.');
        }

        $hasPermission = $user->roles()
            ->where('company_id', $user->company_id)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('nombre', $permission);
            })
            ->exists();

        if (! $hasPermission) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
