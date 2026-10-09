<?php

namespace Database\Factories;

use App\Models\Iglesia;
use App\Models\Miembro;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Miembro> */
class MiembroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'iglesia_id' => Iglesia::factory(),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'categoria' => 'miembro_pleno',
            'estado' => 'activo',
            'fecha_ingreso' => now()->subYear()->toDateString(),
            'fecha_registro' => now()->subYear()->toDateString(),
        ];
    }
}
