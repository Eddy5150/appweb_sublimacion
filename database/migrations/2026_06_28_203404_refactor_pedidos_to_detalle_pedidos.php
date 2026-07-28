<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pedidos')
            ->whereNotNull('producto_id')
            ->orWhereNotNull('producto_nombre')
            ->orWhere('cantidad', '>', 0)
            ->orderBy('id')
            ->chunk(100, function ($pedidos) {
                foreach ($pedidos as $pedido) {
                    DB::table('detalle_pedidos')->insert([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $pedido->producto_id,
                        'producto_nombre' => $pedido->producto_nombre ?? 'Producto',
                        'cantidad' => $pedido->cantidad ?? 1,
                        'precio_unitario' => $pedido->precio_unitario ?? 0,
                        'subtotal' => ($pedido->cantidad ?? 1) * ($pedido->precio_unitario ?? 0),
                        'detalles_diseno' => $pedido->detalles_diseno,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'producto_id')) {
                $table->dropConstrainedForeignId('producto_id');
            }
        });

        $columns = ['producto_nombre', 'detalles_diseno', 'cantidad', 'precio_unitario'];
        foreach ($columns as $column) {
            if (Schema::hasColumn('pedidos', $column)) {
                Schema::table('pedidos', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'producto_id')) {
                $table->foreignId('producto_id')
                    ->nullable()
                    ->constrained('productos')
                    ->nullOnDelete()
                    ->after('cliente_id');
            }
        });

        $columns = ['producto_nombre', 'detalles_diseno', 'cantidad', 'precio_unitario'];
        foreach ($columns as $column) {
            if (! Schema::hasColumn('pedidos', $column)) {
                Schema::table('pedidos', function (Blueprint $table) use ($column) {
                    $table->string($column)->nullable()->after('producto_id');
                });
            }
        }

        DetallePedido::query()
            ->select('pedido_id', 'producto_id', 'producto_nombre', 'cantidad', 'precio_unitario', 'detalles_diseno')
            ->distinct('pedido_id')
            ->orderBy('pedido_id')
            ->orderBy('id')
            ->get()
            ->groupBy('pedido_id')
            ->each(function ($items, $pedidoId) {
                $first = $items->first();
                if ($first) {
                    DB::table('pedidos')->where('id', $pedidoId)->update([
                        'producto_id' => $first->producto_id,
                        'producto_nombre' => $first->producto_nombre,
                        'cantidad' => $first->cantidad,
                        'precio_unitario' => $first->precio_unitario,
                        'detalles_diseno' => $first->detalles_diseno,
                    ]);
                }
            });
    }
};
