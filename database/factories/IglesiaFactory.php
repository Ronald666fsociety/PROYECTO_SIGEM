<?php

namespace Database\Factories;

use App\Models\Circuito;
use App\Models\Iglesia;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Iglesia> */
class IglesiaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'circuito_id' => Circuito::factory(),
            'nombre' => 'Iglesia '.fake()->unique()->city(),
            'codigo' => fake()->unique()->bothify('IG-####'),
            'direccion' => fake()->streetAddress(),
            'localidad' => fake()->city(),
            'telefono' => fake()->numerify('2#######'),
            'pastor_nombre' => fake()->name(),
            'estado' => 'activo',
        ];
    }
}
