<?php

namespace Tests\Feature;

use App\Models\Circuito;
use App\Models\Comunicacion;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_district_can_send_to_a_circuit_and_recipient_read_is_recorded(): void
    {
        $circuit = Circuito::factory()->create();
        $church = Iglesia::factory()->for($circuit, 'circuito')->create();
        $sender = User::factory()->distrito()->create();
        $recipient = User::factory()->local()->create(['iglesia_id' => $church->id, 'circuito_id' => $circuit->id]);

        $response = $this->actingAs($sender)->post(route('comunicaciones.store'), [
            'nivel' => 'circuito',
            'circuito_id' => $circuit->id,
            'tipo' => 'circular',
            'prioridad' => 'normal',
            'titulo' => 'Corte mensual',
            'contenido' => 'Registrar el corte dentro del plazo institucional.',
        ]);

        $response->assertRedirect();
        $communication = Comunicacion::firstOrFail();
        $this->assertDatabaseHas('comunicacion_destinatarios', [
            'comunicacion_id' => $communication->id,
            'destinatario_id' => $recipient->id,
            'leido' => false,
        ]);

        $this->actingAs($recipient)->get(route('comunicaciones.show', $communication))->assertOk();
        $this->assertDatabaseHas('comunicacion_destinatarios', [
            'comunicacion_id' => $communication->id,
            'destinatario_id' => $recipient->id,
            'leido' => true,
        ]);
    }

    public function test_unrelated_user_cannot_open_a_communication(): void
    {
        $circuit = Circuito::factory()->create();
        $sender = User::factory()->distrito()->create();
        $recipient = User::factory()->circuito()->create(['circuito_id' => $circuit->id]);
        $unrelated = User::factory()->local()->create();
        $communication = Comunicacion::create([
            'remitente_id' => $sender->id,
            'nivel' => 'circuito',
            'circuito_id' => $circuit->id,
            'tipo' => 'aviso',
            'prioridad' => 'normal',
            'titulo' => 'Aviso',
            'contenido' => 'Contenido interno',
            'estado' => 'enviada',
            'fecha_envio' => now(),
        ]);
        $communication->destinatarios()->create(['destinatario_id' => $recipient->id]);

        $this->actingAs($unrelated)->get(route('comunicaciones.show', $communication))->assertForbidden();
    }
}
