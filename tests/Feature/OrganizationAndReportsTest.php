<?php

namespace Tests\Feature;

use App\Models\Circuito;
use App\Models\ConteoMembresia;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_district_can_manage_circuits_and_churches(): void
    {
        $district = User::factory()->distrito()->create();

        $this->actingAs($district)->post(route('circuitos.store'), [
            'nombre' => 'Circuito Central',
            'codigo' => 'CEN-01',
            'estado' => 'activo',
        ])->assertRedirect(route('circuitos.index'));

        $circuit = Circuito::where('codigo', 'CEN-01')->firstOrFail();
        $this->actingAs($district)->post(route('iglesias.store'), [
            'circuito_id' => $circuit->id,
            'nombre' => 'Iglesia Central',
            'codigo' => 'IG-CEN',
            'estado' => 'activo',
        ])->assertRedirect();

        $this->assertDatabaseHas('iglesias', ['codigo' => 'IG-CEN', 'circuito_id' => $circuit->id]);
    }

    public function test_local_user_cannot_access_district_organization_management(): void
    {
        $local = User::factory()->local()->create();

        $this->actingAs($local)->get(route('circuitos.index'))->assertForbidden();
        $this->actingAs($local)->get(route('iglesias.create'))->assertForbidden();
    }

    public function test_report_only_contains_the_users_jurisdiction(): void
    {
        $circuitA = Circuito::factory()->create();
        $circuitB = Circuito::factory()->create();
        $churchA = Iglesia::factory()->for($circuitA, 'circuito')->create(['nombre' => 'Iglesia Autorizada']);
        $churchB = Iglesia::factory()->for($circuitB, 'circuito')->create(['nombre' => 'Iglesia Restringida']);
        $local = User::factory()->local()->create(['iglesia_id' => $churchA->id, 'circuito_id' => $circuitA->id]);
        ConteoMembresia::factory()->for($churchA, 'iglesia')->create(['anio' => 2025, 'mes' => 12]);
        ConteoMembresia::factory()->for($churchB, 'iglesia')->create(['anio' => 2025, 'mes' => 12]);

        $response = $this->actingAs($local)->get(route('reportes.index', ['anio' => 2025]));

        $response->assertOk()->assertSee('Iglesia Autorizada')->assertDontSee('Iglesia Restringida');
    }
}
