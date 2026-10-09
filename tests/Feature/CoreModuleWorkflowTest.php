<?php

namespace Tests\Feature;

use App\Models\Circuito;
use App\Models\Iglesia;
use App\Models\Miembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreModuleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_user_can_register_and_deactivate_a_member_without_deleting_history(): void
    {
        $circuit = Circuito::factory()->create();
        $church = Iglesia::factory()->for($circuit, 'circuito')->create();
        $local = User::factory()->local()->create(['iglesia_id' => $church->id, 'circuito_id' => $circuit->id]);

        $this->actingAs($local)->post(route('membresia.store'), [
            'iglesia_id' => $church->id,
            'nombres' => 'Ana',
            'apellidos' => 'Quispe',
            'categoria' => 'miembro_pleno',
            'fecha_ingreso' => '2025-01-15',
        ])->assertRedirect(route('membresia.index'));

        $member = Miembro::where('nombres', 'Ana')->firstOrFail();
        $this->actingAs($local)->delete(route('membresia.destroy', $member))->assertRedirect(route('membresia.index'));
        $this->assertDatabaseHas('miembros', [
            'id' => $member->id,
            'estado' => 'inactivo',
        ]);
    }

    public function test_monthly_count_records_operational_provenance(): void
    {
        $circuit = Circuito::factory()->create();
        $church = Iglesia::factory()->for($circuit, 'circuito')->create();
        $local = User::factory()->local()->create(['iglesia_id' => $church->id, 'circuito_id' => $circuit->id]);

        $this->actingAs($local)->post(route('conteos.store'), [
            'iglesia_id' => $church->id,
            'anio' => 2026,
            'mes' => 9,
            'total_activos' => 80,
            'estado' => 'validado',
        ])->assertRedirect(route('conteos.index'));

        $this->assertDatabaseHas('conteos_membresia', [
            'iglesia_id' => $church->id,
            'anio' => 2026,
            'mes' => 9,
            'fuente_datos' => 'registro_mensual_sigem',
            'es_sintetico' => false,
            'registrado_por' => $local->id,
        ]);
    }

    public function test_district_can_assign_a_local_responsible_user(): void
    {
        $district = User::factory()->distrito()->create();
        $circuit = Circuito::factory()->create();
        $church = Iglesia::factory()->for($circuit, 'circuito')->create();

        $this->actingAs($district)->post(route('usuarios.store'), [
            'name' => 'Pastora Responsable',
            'email' => 'responsable@sigem.test',
            'password' => 'clave-segura',
            'rol' => 'local',
            'iglesia_id' => $church->id,
            'telefono' => '70000000',
        ])->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'responsable@sigem.test',
            'rol' => 'local',
            'iglesia_id' => $church->id,
            'circuito_id' => $circuit->id,
        ]);
        $this->assertDatabaseHas('iglesias', [
            'id' => $church->id,
            'pastor_nombre' => 'Pastora Responsable',
        ]);
    }
}
