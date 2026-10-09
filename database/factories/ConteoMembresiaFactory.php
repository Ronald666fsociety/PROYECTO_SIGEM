<?php

namespace Database\Factories;

use App\Models\ConteoMembresia;
use App\Models\Iglesia;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConteoMembresia> */
class ConteoMembresiaFactory extends Factory
{
    public function definition(): array
    {
        $anio = fake()->numberBetween(2023, 2026);
        $mes = fake()->numberBetween(1, 12);

        return [
            'iglesia_id' => Iglesia::factory(),
            'anio' => $anio,
            'mes' => $mes,
            'total_activos' => fake()->numberBetween(20, 150),
            'total_inactivos' => 0,
            'total_nuevos' => 0,
            'total_transferidos' => 0,
            'total_bajas' => 0,
            'fecha_corte' => now()->setDate($anio, $mes, 1)->endOfMonth()->toDateString(),
            'estado' => 'cerrado',
            'es_sintetico' => false,
            'fuente_datos' => 'padrón institucional',
        ];
    }
}
