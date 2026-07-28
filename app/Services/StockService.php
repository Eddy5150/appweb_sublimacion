<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\StockMovimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function salida(Producto $producto, int $cantidad, ?Model $referencia = null, ?string $nota = null): void
    {
        DB::transaction(function () use ($producto, $cantidad, $referencia, $nota) {
            $anterior = (int) $producto->stock;
            $nuevo = max($anterior - $cantidad, 0);

            $producto->updateQuietly([
                'stock' => $nuevo,
                'veces_vendido' => DB::raw('veces_vendido + '.(int) $cantidad),
            ]);

            $producto->refresh();

            StockMovimiento::create([
                'producto_id' => $producto->id,
                'tipo' => 'salida',
                'cantidad' => $cantidad,
                'stock_anterior' => $anterior,
                'stock_nuevo' => (int) $producto->stock,
                'referencia_type' => $referencia ? $referencia::class : null,
                'referencia_id' => $referencia?->id,
                'nota' => $nota,
                'user_id' => auth()->id(),
            ]);
        });
    }

    public function entrada(Producto $producto, int $cantidad, ?Model $referencia = null, ?string $nota = null): void
    {
        DB::transaction(function () use ($producto, $cantidad, $referencia, $nota) {
            $anterior = (int) $producto->stock;
            $nuevo = $anterior + $cantidad;

            $producto->updateQuietly(['stock' => $nuevo]);

            $producto->refresh();

            StockMovimiento::create([
                'producto_id' => $producto->id,
                'tipo' => 'entrada',
                'cantidad' => $cantidad,
                'stock_anterior' => $anterior,
                'stock_nuevo' => (int) $producto->stock,
                'referencia_type' => $referencia ? $referencia::class : null,
                'referencia_id' => $referencia?->id,
                'nota' => $nota,
                'user_id' => auth()->id(),
            ]);
        });
    }

    public function restauracion(Producto $producto, int $cantidad, ?Model $referencia = null, ?string $nota = null): void
    {
        DB::transaction(function () use ($producto, $cantidad, $referencia, $nota) {
            $anterior = (int) $producto->stock;
            $nuevo = $anterior + $cantidad;

            $producto->updateQuietly(['stock' => $nuevo]);

            $producto->refresh();

            StockMovimiento::create([
                'producto_id' => $producto->id,
                'tipo' => 'cancelacion',
                'cantidad' => $cantidad,
                'stock_anterior' => $anterior,
                'stock_nuevo' => (int) $producto->stock,
                'referencia_type' => $referencia ? $referencia::class : null,
                'referencia_id' => $referencia?->id,
                'nota' => $nota ?: 'Restauración por cancelación',
                'user_id' => auth()->id(),
            ]);
        });
    }

    public function ajuste(Producto $producto, int $nuevoStock, ?string $nota = null): void
    {
        DB::transaction(function () use ($producto, $nuevoStock, $nota) {
            $anterior = (int) $producto->stock;
            $diferencia = $nuevoStock - $anterior;

            if ($diferencia === 0) {
                return;
            }

            $producto->updateQuietly(['stock' => max($nuevoStock, 0)]);

            $producto->refresh();

            StockMovimiento::create([
                'producto_id' => $producto->id,
                'tipo' => 'ajuste',
                'cantidad' => abs($diferencia),
                'stock_anterior' => $anterior,
                'stock_nuevo' => (int) $producto->stock,
                'nota' => $nota ?: 'Ajuste manual de inventario',
                'user_id' => auth()->id(),
            ]);
        });
    }

    public function procesarProductosVenta(array $items, Model $referencia): void
    {
        foreach ($items as $item) {
            if (! empty($item['producto_id'])) {
                $producto = Producto::find($item['producto_id']);
                if ($producto) {
                    $this->salida($producto, (int) $item['cantidad'], $referencia);
                }
            }
        }
    }

    public function restaurarProductosVenta(Model $referencia): void
    {
        $movimientos = StockMovimiento::forCurrentCompany()
            ->where('referencia_type', $referencia::class)
            ->where('referencia_id', $referencia->id)
            ->where('tipo', 'salida')
            ->get();

        foreach ($movimientos as $mov) {
            $producto = Producto::find($mov->producto_id);
            if ($producto) {
                $this->restauracion($producto, $mov->cantidad, $referencia, 'Restauración por modificación de pedido');

                $producto->decrement('veces_vendido', min($mov->cantidad, (int) $producto->veces_vendido));
            }
        }
    }

    public function restaurarProductosCompra(Model $referencia): void
    {
        $movimientos = StockMovimiento::forCurrentCompany()
            ->where('referencia_type', $referencia::class)
            ->where('referencia_id', $referencia->id)
            ->where('tipo', 'entrada')
            ->get();

        foreach ($movimientos as $mov) {
            $producto = Producto::find($mov->producto_id);
            if ($producto) {
                $anterior = (int) $producto->stock;
                $nuevo = max($anterior - $mov->cantidad, 0);

                $producto->updateQuietly(['stock' => $nuevo]);

                StockMovimiento::create([
                    'producto_id' => $producto->id,
                    'tipo' => 'cancelacion',
                    'cantidad' => $mov->cantidad,
                    'stock_anterior' => $anterior,
                    'stock_nuevo' => (int) $producto->stock,
                    'referencia_type' => $referencia::class,
                    'referencia_id' => $referencia->id,
                    'nota' => 'Cancelación de compra',
                    'user_id' => auth()->id(),
                ]);
            }
        }
    }
}
