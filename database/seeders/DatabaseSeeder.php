<?php

namespace Database\Seeders;

use App\Models\Circuito;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $circuitos = [
            ['codigo' => 'C-CAR', 'nombre' => 'Circuito Carabuco'],
            ['codigo' => 'C-MOC', 'nombre' => 'Circuito Mocomoco'],
            ['codigo' => 'C-ESC', 'nombre' => 'Circuito Escoma'],
            ['codigo' => 'C-PAC', 'nombre' => 'Circuito Puerto Acosta'],
        ];

        foreach ($circuitos as $circuito) {
            Circuito::query()->updateOrCreate(
                ['codigo' => $circuito['codigo']],
                [...$circuito, 'estado' => 'activo'],
            );
        }

        $iglesias = [
            ['C-CAR', 'IG-001', 'Iglesia Metodista Carabuco Central', 'Carabuco'],
            ['C-CAR', 'IG-002', 'Iglesia Metodista Chaguaya', 'Chaguaya'],
            ['C-CAR', 'IG-003', 'Iglesia Metodista Camata', 'Camata'],
            ['C-CAR', 'IG-004', 'Iglesia Metodista Italaque', 'Italaque'],
            ['C-CAR', 'IG-005', 'Iglesia Metodista Humanata', 'Humanata'],
            ['C-CAR', 'IG-006', 'Iglesia Metodista Quilima', 'Quilima'],
            ['C-MOC', 'IG-007', 'Iglesia Metodista Mocomoco Central', 'Mocomoco'],
            ['C-MOC', 'IG-008', 'Iglesia Metodista Tilacoca', 'Tilacoca'],
            ['C-MOC', 'IG-009', 'Iglesia Metodista Tajani', 'Tajani'],
            ['C-MOC', 'IG-010', 'Iglesia Metodista Pacaures', 'Pacaures'],
            ['C-MOC', 'IG-011', 'Iglesia Metodista Muñecas', 'Muñecas'],
            ['C-MOC', 'IG-012', 'Iglesia Metodista Charazani', 'Charazani'],
            ['C-ESC', 'IG-013', 'Iglesia Metodista Escoma Central', 'Escoma'],
            ['C-ESC', 'IG-014', 'Iglesia Metodista Cajiata', 'Cajiata'],
            ['C-ESC', 'IG-015', 'Iglesia Metodista Ambana', 'Ambana'],
            ['C-ESC', 'IG-016', 'Iglesia Metodista Wilacala', 'Wilacala'],
            ['C-ESC', 'IG-017', 'Iglesia Metodista Sotalaya', 'Sotalaya'],
            ['C-ESC', 'IG-018', 'Iglesia Metodista Chuma', 'Chuma'],
            ['C-PAC', 'IG-019', 'Iglesia Metodista Puerto Acosta Central', 'Puerto Acosta'],
            ['C-PAC', 'IG-020', 'Iglesia Metodista Waychu', 'Waychu'],
            ['C-PAC', 'IG-021', 'Iglesia Metodista Calata', 'Calata'],
            ['C-PAC', 'IG-022', 'Iglesia Metodista Villa Puni', 'Villa Puni'],
            ['C-PAC', 'IG-023', 'Iglesia Metodista Tiquina', 'Tiquina'],
            ['C-PAC', 'IG-024', 'Iglesia Metodista Achacachi', 'Achacachi'],
        ];

        foreach ($iglesias as [$codigoCircuito, $codigo, $nombre, $localidad]) {
            $circuitoId = Circuito::query()->where('codigo', $codigoCircuito)->value('id');
            Iglesia::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'circuito_id' => $circuitoId,
                    'nombre' => $nombre,
                    'localidad' => $localidad,
                    'estado' => 'activo',
                ],
            );
        }

        User::query()->updateOrCreate(
            ['email' => config('sigem.seed_admin_email')],
            [
                'name' => 'Administrador SIGEM',
                'password' => config('sigem.seed_admin_password'),
                'rol' => 'admin',
                'estado' => 'activo',
            ],
        );
    }
}
