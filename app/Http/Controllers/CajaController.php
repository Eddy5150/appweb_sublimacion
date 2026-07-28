<?php

namespace App\Http\Controllers;

use App\Models\CajaApertura;
use App\Models\CajaMovimiento;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CajaController extends Controller
{
    public function __construct(
        protected ActivityLogger $logger,
    ) {}

    public function index(): Response
    {
        $companyId = auth()->user()->company_id;
        $cajaAbierta = CajaApertura::forCurrentCompany()
            ->where('estado', 'abierta')
            ->with('user')
            ->first();

        $aperturas = CajaApertura::forCurrentCompany()
            ->with('user')
            ->latest()
            ->paginate(10);

        $stats = [
            'abierta' => $cajaAbierta ? [
                'id' => $cajaAbierta->id,
                'monto_inicial' => (float) $cajaAbierta->monto_inicial,
                'ingresos' => $cajaAbierta->ingresos,
                'egresos' => $cajaAbierta->egresos,
                'saldo_actual' => $cajaAbierta->saldo_actual,
                'user_name' => $cajaAbierta->user?->name,
                'apertura_en' => $cajaAbierta->apertura_en?->format('d/m/Y H:i'),
            ] : null,
            'total_hoy' => (float) CajaMovimiento::forCurrentCompany()
                ->whereDate('created_at', today())
                ->where('tipo', 'ingreso')
                ->sum('monto'),
            'movimientos_hoy' => CajaMovimiento::forCurrentCompany()
                ->whereDate('created_at', today())
                ->count(),
        ];

        return Inertia::render('caja/Index', [
            'cajaAbierta' => $stats['abierta'],
            'stats' => $stats,
            'aperturas' => $aperturas->through(fn ($a) => [
                'id' => $a->id,
                'user_name' => $a->user?->name,
                'monto_inicial' => (float) $a->monto_inicial,
                'monto_final' => (float) ($a->monto_final ?? 0),
                'ingresos' => $a->ingresos,
                'egresos' => $a->egresos,
                'estado' => $a->estado,
                'apertura_en' => $a->apertura_en?->format('d/m/Y H:i'),
                'cierre_en' => $a->cierre_en?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function abrir(Request $request): RedirectResponse
    {
        $existe = CajaApertura::forCurrentCompany()
            ->where('estado', 'abierta')
            ->exists();

        if ($existe) {
            return redirect()->route('caja.index')
                ->with('error', 'Ya hay una caja abierta. Ciérrala antes de abrir otra.');
        }

        $validated = $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'notas_apertura' => 'nullable|string|max:500',
        ]);

        CajaApertura::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'monto_inicial' => $validated['monto_inicial'],
            'notas_apertura' => $validated['notas_apertura'] ?? null,
        ]);

        $this->logger->created('caja', 'Apertura de caja', null, [
            'monto_inicial' => $validated['monto_inicial'],
        ]);

        return redirect()->route('caja.index')
            ->with('success', 'Caja abierta correctamente.');
    }

    public function cerrar(Request $request): RedirectResponse
    {
        $caja = CajaApertura::forCurrentCompany()
            ->where('estado', 'abierta')
            ->firstOrFail();

        $validated = $request->validate([
            'monto_final' => 'required|numeric|min:0',
            'notas_cierre' => 'nullable|string|max:500',
        ]);

        $caja->update([
            'monto_final' => $validated['monto_final'],
            'notas_cierre' => $validated['notas_cierre'] ?? null,
            'cierre_en' => now(),
            'estado' => 'cerrada',
        ]);

        $this->logger->updated('caja', 'Cierre de caja', $caja, [
            'monto_final' => $validated['monto_final'],
            'diferencia' => (float) $validated['monto_final'] - $caja->saldo_actual,
        ]);

        return redirect()->route('caja.index')
            ->with('success', 'Caja cerrada correctamente.');
    }

    public function movimientos(CajaApertura $cajaApertura): Response
    {
        $movimientos = CajaMovimiento::forCurrentCompany()
            ->where('caja_apertura_id', $cajaApertura->id)
            ->with('user', 'pedido')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('caja/Movimientos', [
            'caja' => [
                'id' => $cajaApertura->id,
                'monto_inicial' => (float) $cajaApertura->monto_inicial,
                'monto_final' => (float) ($cajaApertura->monto_final ?? 0),
                'ingresos' => $cajaApertura->ingresos,
                'egresos' => $cajaApertura->egresos,
                'saldo_actual' => $cajaApertura->saldo_actual,
                'estado' => $cajaApertura->estado,
                'apertura_en' => $cajaApertura->apertura_en?->format('d/m/Y H:i'),
            ],
            'movimientos' => $movimientos->through(fn ($m) => [
                'id' => $m->id,
                'tipo' => $m->tipo,
                'concepto' => $m->concepto,
                'monto' => (float) $m->monto,
                'metodo_pago' => $m->metodo_pago,
                'referencia' => $m->referencia,
                'user_name' => $m->user?->name,
                'pedido_id' => $m->pedido_id,
                'notas' => $m->notas,
                'created_at' => $m->created_at->format('d/m/Y H:i'),
            ]),
        ]);
    }
}
