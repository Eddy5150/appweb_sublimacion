<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'telefono' => fake()->numerify('7#######'),
            'email' => fake()->optional()->safeEmail(),
            'direccion' => fake()->optional()->address(),
            'estado' => fake()->randomElement(['nuevo', 'frecuente', 'vip']),
        ];
    }
}
