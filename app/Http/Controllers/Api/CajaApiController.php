<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CajaApiController extends Controller
{
    public function actual(): JsonResponse
    {
        $caja = CajaApertura::forCurrentCompany()
            ->where('estado', 'abierta')
            ->with('user')
            ->first();

        if (! $caja) {
            return response()->json(['caja_abierta' => false]);
        }

        return response()->json([
            'caja_abierta' => true,
            'id' => $caja->id,
            'monto_inicial' => (float) $caja->monto_inicial,
            'ingresos' => $caja->ingresos,
            'egresos' => $caja->egresos,
            'saldo_actual' => $caja->saldo_actual,
            'user_name' => $caja->user?->name,
            'apertura_en' => $caja->apertura_en,
        ]);
    }

    public function abrir(Request $request): JsonResponse
    {
        $existe = CajaApertura::forCurrentCompany()
            ->where('estado', 'abierta')
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya hay una caja abierta.'], 422);
        }

        $validated = $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'notas' => 'nullable|string|max:500',
        ]);

        $caja = CajaApertura::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'monto_inicial' => $validated['monto_inicial'],
            'notas_apertura' => $validated['notas'] ?? null,
        ]);

        return response()->json($caja, 201);
    }

    public function cerrar(Request $request): JsonResponse
    {
        $caja = CajaApertura::forCurrentCompany()
            ->where('estado', 'abierta')
            ->firstOrFail();

        $validated = $request->validate([
            'monto_final' => 'required|numeric|min:0',
            'notas' => 'nullable|string|max:500',
        ]);

        $caja->update([
            'monto_final' => $validated['monto_final'],
            'notas_cierre' => $validated['notas'] ?? null,
            'cierre_en' => now(),
            'estado' => 'cerrada',
        ]);

        return response()->json($caja);
    }

    public function movimientos(Request $request): JsonResponse
    {
        $query = CajaMovimiento::forCurrentCompany()->with('user');

        if ($request->caja_apertura_id) {
            $query->where('caja_apertura_id', $request->caja_apertura_id);
        }

        return response()->json(
            $query->latest()->paginate($request->per_page ?? 20)
        );
    }
}
