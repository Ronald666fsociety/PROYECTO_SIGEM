<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Circuito;
use App\Models\Comunicacion;
use App\Models\Iglesia;
use App\Models\Prediccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_district_operational_views_render_successfully(): void
    {
        $district = User::factory()->distrito()->create();
        $circuit = Circuito::factory()->create();
        $church = Iglesia::factory()->for($circuit, 'circuito')->create();
        $recipient = User::factory()->local()->create(['iglesia_id' => $church->id, 'circuito_id' => $circuit->id]);
        $activity = Actividad::factory()->for($church, 'iglesia')->create([
            'circuito_id' => $circuit->id,
            'creado_por' => $district->id,
        ]);
        $communication = Comunicacion::create([
            'remitente_id' => $district->id,
            'nivel' => 'iglesia',
            'iglesia_id' => $church->id,
            'circuito_id' => $circuit->id,
            'tipo' => 'aviso',
            'titulo' => 'Aviso institucional',
            'contenido' => 'Contenido',
            'prioridad' => 'normal',
            'estado' => 'enviada',
            'fecha_envio' => now(),
        ]);
        $communication->destinatarios()->create(['destinatario_id' => $recipient->id]);

        $routes = [
            route('dashboard'),
            route('actividades.index'),
            route('actividades.create'),
            route('actividades.show', $activity),
            route('actividades.edit', $activity),
            route('comunicaciones.index'),
            route('comunicaciones.create'),
            route('comunicaciones.show', $communication),
            route('circuitos.index'),
            route('circuitos.create'),
            route('iglesias.index'),
            route('iglesias.create'),
            route('iglesias.show', $church),
            route('iglesias.edit', $church),
            route('reportes.index', ['anio' => 2025]),
            route('prediccion.index'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($district)->get($route)->assertOk();
        }
    }

    public function test_synthetic_prediction_is_never_presented_as_real_validation(): void
    {
        $prediction = Prediccion::create([
            'fecha_ejecucion' => now(),
            'modelo' => 'Holt lineal sin estacionalidad',
            'estado' => 'viable',
            'modo_datos' => 'prueba_funcional',
        ]);

        $this->assertFalse($prediction->es_viable);
        $this->assertSame('Prueba funcional pendiente de validación real', $prediction->estado_evaluacion_display);
    }
}
