<?php

namespace Database\Factories;

use App\Models\Circuito;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Circuito> */
class CircuitoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Circuito '.fake()->unique()->numberBetween(1, 9999),
            'codigo' => fake()->unique()->bothify('CIR-####'),
            'descripcion' => fake()->sentence(),
            'responsable_nombre' => fake()->name(),
            'telefono' => fake()->numerify('7#######'),
            'estado' => 'activo',
        ];
    }
}
