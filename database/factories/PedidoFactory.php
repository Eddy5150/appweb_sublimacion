<?php

namespace Database\Factories;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'cliente_nombre' => fake()->name(),
            'cliente_telefono' => fake()->numerify('7#######'),
            'cliente_direccion' => fake()->optional()->address(),
            'total' => 0,
            'estado' => fake()->randomElement(['pendiente', 'en_proceso', 'listo', 'entregado']),
        ];
    }

    public function withDetalles(int $count = 1): static
    {
        return $this->afterCreating(function (Pedido $pedido) use ($count) {
            DetallePedidoFactory::new()->count($count)->create([
                'pedido_id' => $pedido->id,
            ]);

            $pedido->update([
                'total' => $pedido->detalles()->sum('subtotal'),
            ]);
        });
    }
}
