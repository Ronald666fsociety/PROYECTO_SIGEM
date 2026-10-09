<?php

namespace Tests\Feature;

use App\Models\Circuito;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HistoricalImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_import_is_atomic_and_does_not_modify_counts(): void
    {
        $district = User::factory()->distrito()->create();
        $church = Iglesia::factory()->for(Circuito::factory(), 'circuito')->create();
        $csv = "codigo_iglesia,anio,mes,total_activos,estado\n{$church->codigo},2025,13,50,cerrado\n";

        $response = $this->actingAs($district)->post(route('conteos.procesar-importar'), [
            'archivo_csv' => UploadedFile::fake()->createWithContent('historico.csv', $csv),
            'fuente_datos' => 'Padrón distrital autorizado',
            'version_datos' => 'corte-2025',
        ]);

        $response->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseCount('conteos_membresia', 0);
    }

    public function test_valid_import_stores_provenance_and_hash(): void
    {
        $district = User::factory()->distrito()->create();
        $church = Iglesia::factory()->for(Circuito::factory(), 'circuito')->create();
        $csv = "codigo_iglesia,anio,mes,total_activos,total_inactivos,total_nuevos,total_transferidos,total_bajas,fecha_corte,estado\n{$church->codigo},2025,12,50,2,1,0,0,2025-12-31,cerrado\n";

        $response = $this->actingAs($district)->post(route('conteos.procesar-importar'), [
            'archivo_csv' => UploadedFile::fake()->createWithContent('historico.csv', $csv),
            'fuente_datos' => 'Padrón distrital autorizado',
            'version_datos' => 'corte-2025',
            'observaciones_calidad' => 'Revisado con responsables locales.',
        ]);

        $response->assertRedirect(route('conteos.index'));
        $this->assertDatabaseHas('conteos_membresia', [
            'iglesia_id' => $church->id,
            'anio' => 2025,
            'mes' => 12,
            'fuente_datos' => 'Padrón distrital autorizado',
            'version_datos' => 'corte-2025',
            'archivo_origen' => 'historico.csv',
            'es_sintetico' => false,
        ]);
        $this->assertSame(64, strlen((string) $church->conteos()->firstOrFail()->hash_archivo));
    }
}
