<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\CrmAiPrediction;
use App\Models\CrmProductoRecomendado;
use App\Models\DetallePedido;
use App\Models\Producto;
use Illuminate\Support\Collection;

class RecommendationEngine
{
    public function recomendarProductos(Cliente $cliente, int $limit = 5): Collection
    {
        $productosComprados = DetallePedido::whereHas('pedido', function ($q) use ($cliente) {
            $q->where('cliente_id', $cliente->id);
        })->pluck('producto_id')->filter()->unique()->values()->toArray();

        $productosRecomendados = collect();

        if (count($productosComprados) > 0) {
            $clientesSimilares = DetallePedido::whereIn('producto_id', $productosComprados)
                ->whereHas('pedido', function ($q) {
                    $q->whereNotNull('cliente_id');
                })
                ->pluck('pedido.cliente_id')
                ->filter(fn ($id) => $id !== $cliente->id)
                ->unique()
                ->values()
                ->toArray();

            if (count($clientesSimilares) > 0) {
                $recomendados = DetallePedido::whereIn('producto_id', function ($q) use ($clientesSimilares, $productosComprados) {
                    $q->select('producto_id')
                        ->from('detalle_pedidos')
                        ->whereIn('pedido_id', function ($q2) use ($clientesSimilares) {
                            $q2->select('id')->from('pedidos')->whereIn('cliente_id', $clientesSimilares);
                        })
                        ->whereNotIn('producto_id', $productosComprados)
                        ->whereNotNull('producto_id');
                })
                    ->selectRaw('producto_id, COUNT(*) as total, SUM(cantidad) as total_qty')
                    ->groupBy('producto_id')
                    ->orderByDesc('total')
                    ->orderByDesc('total_qty')
                    ->take($limit)
                    ->get();

                foreach ($recomendados as $rec) {
                    $producto = Producto::find($rec->producto_id);
                    if ($producto && $producto->activo) {
                        $productosRecomendados->push([
                            'producto_id' => $producto->id,
                            'producto_nombre' => $producto->nombre,
                            'producto_precio' => (float) $producto->precio,
                            'score' => min($rec->total * 20, 100),
                            'motivo' => 'Clientes similares compraron este producto',
                        ]);
                    }
                }
            }
        }

        if ($productosRecomendados->count() < $limit) {
            $populares = DetallePedido::selectRaw('producto_id, COUNT(*) as total, SUM(cantidad) as total_qty')
                ->whereNotNull('producto_id')
                ->whereNotIn('producto_id', $productosComprados)
                ->groupBy('producto_id')
                ->orderByDesc('total')
                ->orderByDesc('total_qty')
                ->take($limit - $productosRecomendados->count())
                ->get();

            foreach ($populares as $pop) {
                $producto = Producto::find($pop->producto_id);
                if ($producto && $producto->activo) {
                    $productosRecomendados->push([
                        'producto_id' => $producto->id,
                        'producto_nombre' => $producto->nombre,
                        'producto_precio' => (float) $producto->precio,
                        'score' => min($pop->total * 15, 80),
                        'motivo' => 'Producto popular en el sistema',
                    ]);
                }
            }
        }

        CrmProductoRecomendado::where('cliente_id', $cliente->id)->delete();
        foreach ($productosRecomendados as $rec) {
            CrmProductoRecomendado::create([
                'cliente_id' => $cliente->id,
                'producto_id' => $rec['producto_id'],
                'score' => $rec['score'],
                'motivo' => $rec['motivo'],
            ]);
        }

        return $productosRecomendados->take($limit);
    }

    public function clientesAContactarHoy(int $limit = 5): Collection
    {
        return CrmAiPrediction::with('cliente')
            ->whereHas('cliente')
            ->where(function ($q) {
                $q->where('emotional_state', 'en_riesgo')
                    ->orWhere('churn_risk', 'alta')
                    ->orWhere(function ($q2) {
                        $q2->where('purchase_probability', '>=', 50)
                            ->where('emotional_state', 'feliz');
                    });
            })
            ->orderByDesc('purchase_probability')
            ->orderByDesc('ai_score')
            ->take($limit)
            ->get()
            ->map(fn (CrmAiPrediction $p) => [
                'cliente_id' => $p->cliente_id,
                'cliente_nombre' => $p->cliente?->nombre,
                'cliente_telefono' => $p->cliente?->telefono,
                'motivo' => $this->motivoContacto($p),
                'ai_score' => $p->ai_score,
                'purchase_probability' => $p->purchase_probability,
                'emotional_state' => $p->emotional_state,
                'churn_risk' => $p->churn_risk,
            ]);
    }

    private function motivoContacto(CrmAiPrediction $p): string
    {
        return match (true) {
            $p->churn_risk === 'alta' => 'Alto riesgo de abandono. Contactar urgente.',
            $p->emotional_state === 'en_riesgo' => 'Cliente en riesgo emocional. Enviar oferta especial.',
            $p->purchase_probability >= 70 => 'Alta probabilidad de compra. Aprovechar momento.',
            $p->purchase_probability >= 50 => 'Buena probabilidad de compra. Ofrecer productos.',
            default => 'Cliente valioso. Mantener relación.',
        };
    }
}
