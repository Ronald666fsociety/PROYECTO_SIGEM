<?php

namespace Tests\Feature;

use App\Models\Circuito;
use App\Models\Iglesia;
use App\Models\Miembro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurisdictionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_user_cannot_register_a_count_for_another_church(): void
    {
        [$local, , $otherChurch] = $this->organization();

        $response = $this->actingAs($local)->post(route('conteos.store'), [
            'iglesia_id' => $otherChurch->id,
            'anio' => 2025,
            'mes' => 12,
            'total_activos' => 50,
            'estado' => 'cerrado',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('conteos_membresia', 0);
    }

    public function test_circuit_user_cannot_create_an_activity_in_another_circuit(): void
    {
        [, $circuitUser, $otherChurch] = $this->organization();

        $response = $this->actingAs($circuitUser)->post(route('actividades.store'), [
            'nivel' => 'iglesia',
            'iglesia_id' => $otherChurch->id,
            'titulo' => 'Actividad ajena',
            'tipo' => 'culto',
            'fecha_inicio' => '2026-10-20',
            'estado' => 'programada',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('actividades', 0);
    }

    public function test_local_activity_is_forced_to_the_assigned_church(): void
    {
        [$local, , $otherChurch] = $this->organization();

        $response = $this->actingAs($local)->post(route('actividades.store'), [
            'nivel' => 'distrito',
            'iglesia_id' => $otherChurch->id,
            'titulo' => 'Reunión local',
            'tipo' => 'reunion_administrativa',
            'fecha_inicio' => '2026-10-20',
            'estado' => 'programada',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('actividades', [
            'titulo' => 'Reunión local',
            'nivel' => 'iglesia',
            'iglesia_id' => $local->iglesia_id,
        ]);
    }

    public function test_predictive_api_is_restricted_to_district_roles(): void
    {
        [$local] = $this->organization();
        $district = User::factory()->distrito()->create();

        $this->actingAs($local)->get('/api/serie-historica')->assertForbidden();
        $this->actingAs($district)->get('/api/serie-historica')->assertOk()->assertJsonCount(0);
    }

    public function test_local_user_cannot_view_a_member_from_another_church(): void
    {
        [$local, , $otherChurch] = $this->organization();
        $member = Miembro::factory()->for($otherChurch, 'iglesia')->create();

        $this->actingAs($local)->get(route('membresia.show', $member))->assertForbidden();
    }

    /** @return array{User, User, Iglesia} */
    private function organization(): array
    {
        $circuitA = Circuito::factory()->create();
        $circuitB = Circuito::factory()->create();
        $churchA = Iglesia::factory()->for($circuitA, 'circuito')->create();
        $churchB = Iglesia::factory()->for($circuitB, 'circuito')->create();
        $local = User::factory()->local()->create(['iglesia_id' => $churchA->id, 'circuito_id' => $circuitA->id]);
        $circuitUser = User::factory()->circuito()->create(['circuito_id' => $circuitA->id]);

        return [$local, $circuitUser, $churchB];
    }
}
