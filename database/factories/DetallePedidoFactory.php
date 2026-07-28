<?php

namespace Database\Factories;

use App\Models\DetallePedido;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetallePedidoFactory extends Factory
{
    protected $model = DetallePedido::class;

    public function definition(): array
    {
        $precio = fake()->randomFloat(2, 10, 250);
        $cantidad = fake()->numberBetween(1, 6);

        return [
            'producto_id' => Producto::factory(),
            'producto_nombre' => fake()->word(),
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $cantidad * $precio,
            'detalles_diseno' => fake()->optional()->sentence(),
        ];
    }
}
