<?php

namespace Database\Factories;

use App\Models\Actividad;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Actividad> */
class ActividadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'iglesia_id' => Iglesia::factory(),
            'nivel' => 'iglesia',
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->sentence(),
            'tipo' => 'reunion_administrativa',
            'fecha_inicio' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'estado' => 'programada',
            'creado_por' => User::factory(),
        ];
    }
}
