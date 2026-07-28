<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    public function edit(): Response
    {
        $company = auth()->user()->company;

        $subscription = Subscription::where('company_id', $company->id)
            ->with('plan')
            ->latest()
            ->first();

        return Inertia::render('company/Settings', [
            'company' => $company,
            'plan' => $subscription?->plan,
            'subscription' => $subscription,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'sitio_web' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
        ]);

        $company->update($validated);

        return redirect()->route('company.settings')
            ->with('success', 'Configuración actualizada exitosamente.');
    }
}
