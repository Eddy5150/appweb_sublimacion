<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::factory()->count(5)->create();

        $productos = Producto::factory()->count(8)->create();

        Pedido::factory()
            ->count(15)
            ->withDetalles(2)
            ->sequence(fn ($sequence) => [
                'cliente_id' => $clientes->random()->id,
            ])
            ->create();
    }
}
