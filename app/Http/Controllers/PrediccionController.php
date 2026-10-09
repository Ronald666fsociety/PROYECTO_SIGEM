<?php

namespace App\Http\Controllers;

use App\Models\ConteoMembresia;
use App\Models\Prediccion;
use App\Models\PrediccionValor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrediccionController extends Controller
{
    private function getPythonExecutable(): string
    {
        $configured = trim((string) config('sigem.python_executable', 'auto'));
        if ($configured !== '' && $configured !== 'auto') {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $localAppData = getenv('LOCALAPPDATA');
            if (is_string($localAppData) && $localAppData !== '') {
                $patterns = [
                    $localAppData.'/Python/*/python.exe',
                    $localAppData.'/Programs/Python/Python*/python.exe',
                ];
                foreach ($patterns as $pattern) {
                    $candidates = glob($pattern) ?: [];
                    rsort($candidates, SORT_NATURAL);
                    if ($candidates !== []) {
                        return $candidates[0];
                    }
                }
            }

            return 'python';
        }

        return 'python3';
    }

    private function obtenerSerieHistorica(): Collection
    {
        return ConteoMembresia::query()
            ->whereIn('estado', ['cerrado', 'validado'])
            ->select(
                'anio',
                'mes',
                DB::raw('SUM(total_activos) as total_activos'),
                DB::raw('COUNT(DISTINCT iglesia_id) as iglesias_reportadas'),
                DB::raw('MAX(CASE WHEN es_sintetico = 1 THEN 1 ELSE 0 END) as contiene_sinteticos'),
            )
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();
    }

    public function index(): View
    {
        $predicciones = Prediccion::query()
            ->latest()
            ->limit(10)
            ->get();
        $serieHistorica = $this->obtenerSerieHistorica();
        $ultimaPrediccion = Prediccion::query()
            ->latest()
            ->with('valores')
            ->first();

        return view('prediccion.index', compact('predicciones', 'serieHistorica', 'ultimaPrediccion'));
    }

    public function ejecutar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'horizonte' => ['required', 'integer', 'between:1,6'],
        ]);

        $horizonte = (int) $validated['horizonte'];
        $executionId = (string) Str::uuid();
        $inputPath = storage_path("app/prediccion_entrada_{$executionId}.json");
        $outputPath = storage_path("app/prediccion_resultado_{$executionId}.json");
        $pythonScript = base_path('python/holt_prediccion.py');
        $pythonExe = $this->getPythonExecutable();

        if (! is_file($pythonScript)) {
            return redirect()->route('prediccion.index')
                ->with('error', "El script predictivo no se encuentra en {$pythonScript}.");
        }

        $serieHistorica = $this->obtenerSerieHistorica();
        file_put_contents($inputPath, json_encode([
            'serie' => $serieHistorica->map(fn ($fila): array => [
                'anio' => (int) $fila->anio,
                'mes' => (int) $fila->mes,
                'total_activos' => (float) $fila->total_activos,
                'iglesias_reportadas' => (int) $fila->iglesias_reportadas,
                'registros_iglesia_mes' => (int) $fila->iglesias_reportadas,
                'registros_sinteticos' => (bool) $fila->contiene_sinteticos ? 1 : 0,
            ])->values()->all(),
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        $result = Process::timeout(120)->run([
            $pythonExe,
            $pythonScript,
            '--input-json',
            $inputPath,
            '--horizonte',
            (string) $horizonte,
            '--output',
            $outputPath,
        ]);

        if (! is_file($outputPath)) {
            $error = trim($result->errorOutput() ?: $result->output());

            @unlink($inputPath);

            return redirect()->route('prediccion.index')
                ->with('error', 'No se pudo ejecutar la evaluación predictiva: '.($error ?: 'sin salida del motor.'));
        }

        $data = json_decode((string) file_get_contents($outputPath), true);
        @unlink($inputPath);
        @unlink($outputPath);
        if (! is_array($data)) {
            return redirect()->route('prediccion.index')
                ->with('error', 'El motor predictivo devolvió un resultado ilegible.');
        }

        if (($data['estado'] ?? null) !== 'evaluado') {
            return redirect()->route('prediccion.index')
                ->with('error', 'Compuerta de calidad: '.($data['error'] ?? 'la serie no puede evaluarse.'));
        }

        $resumenFinal = collect($data['evaluacion_temporal']['resumen_comprobacion_final'] ?? [])
            ->keyBy('metodo');
        $holt = $resumenFinal->get('holt', []);
        $ultimoValor = $resumenFinal->get('ultimo_valor', []);
        $suavizamientoSimple = $resumenFinal->get('suavizamiento_simple', []);
        $regresionLineal = $resumenFinal->get('regresion_lineal', []);
        $protocolo = $data['evaluacion_temporal']['protocolo'] ?? [];
        $fechaCorte = isset($data['parametros']['ultimo_corte'])
            ? $data['parametros']['ultimo_corte'].'-01'
            : null;
        $estado = $this->clasificarResultado($data);

        $prediccion = DB::transaction(function () use (
            $data,
            $request,
            $holt,
            $ultimoValor,
            $suavizamientoSimple,
            $regresionLineal,
            $protocolo,
            $fechaCorte,
            $estado,
        ): Prediccion {
            $prediccion = Prediccion::create([
                'fecha_ejecucion' => now(),
                'modelo' => $data['modelo'],
                'alpha' => $data['parametros']['alpha'] ?? null,
                'beta' => $data['parametros']['beta'] ?? null,
                'mae' => $holt['mae'] ?? null,
                'rmse' => $holt['rmse'] ?? null,
                'mae_linea_base' => $ultimoValor['mae'] ?? null,
                'rmse_linea_base' => $ultimoValor['rmse'] ?? null,
                'mae_suavizamiento_simple' => $suavizamientoSimple['mae'] ?? null,
                'rmse_suavizamiento_simple' => $suavizamientoSimple['rmse'] ?? null,
                'mae_regresion_lineal' => $regresionLineal['mae'] ?? null,
                'rmse_regresion_lineal' => $regresionLineal['rmse'] ?? null,
                'ventanas_evaluadas' => $protocolo['numero_origenes_desarrollo'] ?? 0,
                'ventanas_superadas' => 0,
                'meses_entrenamiento' => $data['parametros']['meses_entrenamiento_final'] ?? 0,
                'horizonte_meses' => $data['parametros']['horizonte_meses'] ?? 6,
                'estado' => $estado,
                'modo_datos' => $data['modo_datos'],
                'mejor_metodo' => $data['metricas']['mejor_metodo'] ?? null,
                'observaciones' => $data['observaciones'],
                'resultados_validacion' => $data['evaluacion_temporal'],
                'fecha_corte_datos' => $fechaCorte,
                'ejecutado_por' => $request->user()?->id,
            ]);

            foreach ($data['pronosticos'] as $pronostico) {
                PrediccionValor::create([
                    'prediccion_id' => $prediccion->id,
                    'anio' => $pronostico['anio'],
                    'mes' => $pronostico['mes'],
                    'valor_predicho' => $pronostico['valor_predicho'],
                    'valor_real' => null,
                    'intervalo_inferior' => $pronostico['intervalo_aproximado_inferior'],
                    'intervalo_superior' => $pronostico['intervalo_aproximado_superior'],
                    'crecimiento_absoluto' => $pronostico['crecimiento_absoluto'],
                    'crecimiento_porcentual' => $pronostico['crecimiento_porcentual'],
                ]);
            }

            return $prediccion;
        });

        $mensaje = $data['modo_datos'] === 'prueba_funcional'
            ? 'Prueba funcional ejecutada con datos sintéticos; no constituye validación de precisión real.'
            : 'Evaluación temporal y pronóstico Holt registrados correctamente.';

        return redirect()->route('prediccion.resultado', $prediccion)
            ->with('success', $mensaje);
    }

    public function resultado(int $id): View
    {
        $prediccion = Prediccion::with(['valores', 'ejecutador'])->findOrFail($id);
        $serieHistorica = $this->obtenerSerieHistorica();

        return view('prediccion.resultado', compact('prediccion', 'serieHistorica'));
    }

    public function apiSerieHistorica(): JsonResponse
    {
        $meses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];
        $serie = $this->obtenerSerieHistorica()->map(fn ($fila): array => [
            'anio' => $fila->anio,
            'mes' => $fila->mes,
            'periodo' => ($meses[$fila->mes] ?? '')." {$fila->anio}",
            'total_activos' => (float) $fila->total_activos,
            'iglesias_reportadas' => (int) $fila->iglesias_reportadas,
            'contiene_sinteticos' => (bool) $fila->contiene_sinteticos,
        ]);

        return response()->json($serie);
    }

    public function apiUltimaPrediccion(): JsonResponse
    {
        $prediccion = Prediccion::query()
            ->latest()
            ->with('valores')
            ->first();

        return response()->json($prediccion);
    }

    /**
     * Clasifica la ejecución sin confundir una prueba sintética con validación real.
     *
     * @param  array<string, mixed>  $data
     */
    private function clasificarResultado(array $data): string
    {
        if (($data['modo_datos'] ?? null) === 'prueba_funcional') {
            return 'pendiente';
        }

        $holtEsMejor = ($data['metricas']['mejor_metodo'] ?? null) === 'holt';
        $tieneAdvertencias = ! empty($data['advertencias'] ?? []);

        return $holtEsMejor && ! $tieneAdvertencias ? 'viable' : 'no_viable';
    }
}
