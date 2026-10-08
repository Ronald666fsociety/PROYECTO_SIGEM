<?php

namespace App\Http\Controllers;

use App\Models\ConteoMembresia;
use App\Models\Iglesia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConteoController extends Controller
{
    private function obtenerResumenCompuerta(): array
    {
        $cortes = ConteoMembresia::query()
            ->whereIn('estado', ['cerrado', 'validado'])
            ->select(
                'anio',
                'mes',
                DB::raw('COUNT(DISTINCT iglesia_id) as iglesias_reportadas'),
                DB::raw('MAX(CASE WHEN es_sintetico = 1 THEN 1 ELSE 0 END) as contiene_sinteticos'),
            )
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        $indices = $cortes
            ->map(fn ($corte): int => ((int) $corte->anio * 12) + (int) $corte->mes - 1)
            ->values();
        $serieContinua = $indices->count() > 0;
        for ($indice = 1; $indice < $indices->count(); $indice++) {
            if ($indices[$indice] - $indices[$indice - 1] !== 1) {
                $serieContinua = false;
                break;
            }
        }

        $coberturaCompleta = $cortes->isNotEmpty()
            && $cortes->every(fn ($corte): bool => (int) $corte->iglesias_reportadas === 24);
        $contieneSinteticos = $cortes->contains(
            fn ($corte): bool => (bool) $corte->contiene_sinteticos
        );
        $aprobadaEstructural = $cortes->count() >= 36 && $serieContinua && $coberturaCompleta;
        $primerCorte = $cortes->first();
        $ultimoCorte = $cortes->last();

        return [
            'total_meses' => $cortes->count(),
            'requerido' => 36,
            'serie_continua' => $serieContinua,
            'cobertura_completa' => $coberturaCompleta,
            'contiene_sinteticos' => $contieneSinteticos,
            'aprobada' => $aprobadaEstructural,
            'aprobada_validacion_real' => $aprobadaEstructural && ! $contieneSinteticos,
            'primer_periodo' => $primerCorte ? "{$primerCorte->mes}/{$primerCorte->anio}" : '--',
            'ultimo_periodo' => $ultimoCorte ? "{$ultimoCorte->mes}/{$ultimoCorte->anio}" : '--',
            'total_registros' => ConteoMembresia::count(),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ConteoMembresia::with(['iglesia.circuito', 'registrador']);

        // Scope by user role
        if ($user->isLocal()) {
            $query->where('iglesia_id', $user->iglesia_id);
        } elseif ($user->isCircuito()) {
            $query->whereHas('iglesia', function ($q) use ($user) {
                $q->where('circuito_id', $user->circuito_id);
            });
        }

        // Filters
        if ($request->filled('anio')) {
            $query->where('anio', $request->anio);
        }
        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }
        if ($request->filled('iglesia_id') && ! $user->isLocal()) {
            $query->where('iglesia_id', $request->iglesia_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $conteos = $query->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->orderBy('iglesia_id', 'asc')
            ->paginate(24);

        $anios = ConteoMembresia::select('anio')->distinct()->orderBy('anio', 'desc')->pluck('anio');
        $iglesias = $user->isLocal()
            ? Iglesia::where('id', $user->iglesia_id)->get()
            : Iglesia::where('estado', 'activo')->orderBy('nombre')->get();

        $resumenCompuerta = $this->obtenerResumenCompuerta();
        $totalMesesDistritales = $resumenCompuerta['total_meses'];
        $compuertaAprobada = $resumenCompuerta['aprobada_validacion_real'];

        return view('conteos.index', compact('conteos', 'anios', 'iglesias', 'totalMesesDistritales', 'compuertaAprobada', 'resumenCompuerta'));
    }

    public function create()
    {
        $user = auth()->user();
        $iglesias = $user->isLocal()
            ? Iglesia::where('id', $user->iglesia_id)->get()
            : Iglesia::where('estado', 'activo')->orderBy('nombre')->get();

        $anioActual = (int) date('Y');
        $mesActual = (int) date('n');

        return view('conteos.create', compact('iglesias', 'anioActual', 'mesActual'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'iglesia_id' => 'required|exists:iglesias,id',
            'anio' => 'required|integer|min:2000|max:2050',
            'mes' => 'required|integer|min:1|max:12',
            'total_activos' => 'required|integer|min:0',
            'total_inactivos' => 'nullable|integer|min:0',
            'total_nuevos' => 'nullable|integer|min:0',
            'total_transferidos' => 'nullable|integer|min:0',
            'total_bajas' => 'nullable|integer|min:0',
            'fecha_corte' => 'nullable|date',
            'estado' => 'required|in:borrador,validado,cerrado',
        ]);

        if ($user->isLocal() && (int) $validated['iglesia_id'] !== (int) $user->iglesia_id) {
            abort(403, 'No tiene autorización para registrar conteos en otra iglesia.');
        }

        $validated['total_inactivos'] = $validated['total_inactivos'] ?? 0;
        $validated['total_nuevos'] = $validated['total_nuevos'] ?? 0;
        $validated['total_transferidos'] = $validated['total_transferidos'] ?? 0;
        $validated['total_bajas'] = $validated['total_bajas'] ?? 0;
        $validated['fecha_corte'] = $validated['fecha_corte'] ?? date('Y-m-t', strtotime("{$validated['anio']}-{$validated['mes']}-01"));
        $validated['es_sintetico'] = false;
        $validated['registrado_por'] = $user->id;

        ConteoMembresia::updateOrCreate(
            [
                'iglesia_id' => $validated['iglesia_id'],
                'anio' => $validated['anio'],
                'mes' => $validated['mes'],
            ],
            $validated
        );

        return redirect()->route('conteos.index')
            ->with('success', "Conteo mensual ({$validated['mes']}/{$validated['anio']}) guardado correctamente.");
    }

    public function importar()
    {
        $compuerta = $this->obtenerResumenCompuerta();

        return view('conteos.importar', compact('compuerta'));
    }

    public function descargarPlantilla(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_conteos_historicos_sigem.csv"',
        ];

        $iglesias = Iglesia::with('circuito')->where('estado', 'activo')->orderBy('id')->get();

        return response()->stream(function () use ($iglesias) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'codigo_iglesia',
                'nombre_iglesia',
                'circuito',
                'anio',
                'mes',
                'total_activos',
                'total_inactivos',
                'total_nuevos',
                'total_transferidos',
                'total_bajas',
                'fecha_corte',
                'estado',
            ]);

            // Sample rows: 36 months structure guide for each church or recent years
            $aniosEjemplo = [2023, 2024, 2025];
            foreach ($iglesias as $ig) {
                foreach ($aniosEjemplo as $anio) {
                    for ($mes = 1; $mes <= 12; $mes++) {
                        $fechaCorte = date('Y-m-t', strtotime("{$anio}-{$mes}-01"));
                        fputcsv($handle, [
                            $ig->codigo,
                            $ig->nombre,
                            $ig->circuito->nombre ?? 'Distrito',
                            $anio,
                            $mes,
                            50, // Ejemplo editable
                            5,
                            1,
                            0,
                            0,
                            $fechaCorte,
                            'cerrado', // 'cerrado' o 'validado' para ser reconocido por Holt
                        ]);
                    }
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function procesarImportacion(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt|max:10240',
            'reemplazar_existentes' => 'nullable|boolean',
        ]);

        $file = $request->file('archivo_csv');
        $reemplazar = $request->boolean('reemplazar_existentes');

        $iglesiasMap = Iglesia::pluck('id', 'codigo')->toArray();
        $iglesiasNombreMap = Iglesia::pluck('id', 'nombre')->toArray();

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // Remove BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            $header = fgetcsv($handle, 2000, ',');
            if (! $header) {
                return back()->with('error', 'El archivo CSV está vacío o tiene un formato inválido.');
            }

            // Normalize header names
            $header = array_map(function ($h) {
                return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
            }, $header);

            $userId = auth()->id();

            DB::beginTransaction();
            try {
                if ($reemplazar) {
                    // Truncate or clean existing counts to substitute fictitious data with real data
                    ConteoMembresia::truncate();
                }

                $importados = 0;
                $linea = 1;

                while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                    $linea++;
                    if (count($data) < 6) {
                        continue; // Skip incomplete lines
                    }

                    $row = array_combine(array_slice($header, 0, count($data)), $data);

                    $codigo = trim($row['codigo_iglesia'] ?? '');
                    $nombre = trim($row['nombre_iglesia'] ?? '');
                    $anio = (int) ($row['anio'] ?? 0);
                    $mes = (int) ($row['mes'] ?? 0);
                    $totalActivos = (int) ($row['total_activos'] ?? 0);
                    $totalInactivos = (int) ($row['total_inactivos'] ?? 0);
                    $totalNuevos = (int) ($row['total_nuevos'] ?? 0);
                    $totalTransferidos = (int) ($row['total_transferidos'] ?? 0);
                    $totalBajas = (int) ($row['total_bajas'] ?? 0);
                    $fechaCorte = ! empty($row['fecha_corte']) ? trim($row['fecha_corte']) : date('Y-m-t', strtotime("{$anio}-{$mes}-01"));
                    $estado = strtolower(trim($row['estado'] ?? 'cerrado'));

                    if (! in_array($estado, ['borrador', 'validado', 'cerrado'])) {
                        $estado = 'cerrado';
                    }

                    // Find church id by code or by name
                    $iglesiaId = $iglesiasMap[$codigo] ?? ($iglesiasNombreMap[$nombre] ?? null);
                    if (! $iglesiaId) {
                        continue; // Skip unrecognized church
                    }

                    if ($anio < 2000 || $mes < 1 || $mes > 12) {
                        continue;
                    }

                    ConteoMembresia::updateOrCreate(
                        [
                            'iglesia_id' => $iglesiaId,
                            'anio' => $anio,
                            'mes' => $mes,
                        ],
                        [
                            'total_activos' => $totalActivos,
                            'total_inactivos' => $totalInactivos,
                            'total_nuevos' => $totalNuevos,
                            'total_transferidos' => $totalTransferidos,
                            'total_bajas' => $totalBajas,
                            'fecha_corte' => $fechaCorte,
                            'estado' => $estado,
                            'es_sintetico' => false,
                            'registrado_por' => $userId,
                        ]
                    );

                    $importados++;
                }

                fclose($handle);
                DB::commit();

                $compuerta = $this->obtenerResumenCompuerta();
                $mensaje = "Se importaron {$importados} registros históricos reales. Total de meses distritales: {$compuerta['total_meses']}.";
                if ($compuerta['aprobada_validacion_real']) {
                    $mensaje .= ' La serie cumple suficiencia, continuidad y cobertura de las 24 iglesias para la evaluación temporal.';
                } else {
                    $mensaje .= ' La validación predictiva continuará pendiente hasta reunir al menos 36 meses continuos y completos de las 24 iglesias.';
                }

                return redirect()->route('conteos.index')->with('success', $mensaje);
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->with('error', 'Error durante la importación: '.$e->getMessage());
            }
        }

        return back()->with('error', 'No se pudo abrir el archivo CSV subido.');
    }

    public function regenerarFicticios()
    {
        $userId = auth()->id();
        $iglesias = Iglesia::where('estado', 'activo')->get();

        if ($iglesias->isEmpty()) {
            return back()->with('error', 'No hay iglesias registradas para generar la serie.');
        }

        DB::beginTransaction();
        try {
            ConteoMembresia::truncate();

            // 45 continuous months: Jan 2023 to Sep 2026
            $meses = [];
            for ($y = 2023; $y <= 2025; $y++) {
                for ($m = 1; $m <= 12; $m++) {
                    $meses[] = ['anio' => $y, 'mes' => $m];
                }
            }
            for ($m = 1; $m <= 9; $m++) {
                $meses[] = ['anio' => 2026, 'mes' => $m];
            }

            // Base size per church (distributed to sum approx ~1000 in 2023 up to ~1360 in 2026)
            $totalIglesias = $iglesias->count();
            $basePorIglesia = [
                1 => 62, 2 => 45, 3 => 78, 4 => 55, 5 => 48, 6 => 68,
                7 => 42, 8 => 38, 9 => 52, 10 => 46, 11 => 60, 12 => 39,
                13 => 58, 14 => 44, 15 => 72, 16 => 50, 17 => 40, 18 => 64,
                19 => 47, 20 => 53, 21 => 36, 22 => 65, 23 => 41, 24 => 55,
            ];

            foreach ($meses as $idx => $per) {
                $t = $idx; // trend index 0..44
                $fechaCorte = date('Y-m-t', strtotime("{$per['anio']}-{$per['mes']}-01"));

                foreach ($iglesias as $ig) {
                    $base = $basePorIglesia[$ig->id] ?? 45;
                    // Linear growth trend (~8.2 members per month across all 24 churches)
                    $crecimiento = (int) round(($t * 8.2) / $totalIglesias);
                    $ruido = (($t + $ig->id * 3) % 5) - 2;
                    $activos = max(15, $base + $crecimiento + $ruido);

                    $inactivos = (int) round($activos * 0.12);
                    $nuevos = max(0, (int) round(($t % 3 == 0) ? 2 : 1));
                    $bajas = max(0, (int) round(($t % 5 == 0) ? 1 : 0));

                    ConteoMembresia::create([
                        'iglesia_id' => $ig->id,
                        'anio' => $per['anio'],
                        'mes' => $per['mes'],
                        'total_activos' => $activos,
                        'total_inactivos' => $inactivos,
                        'total_nuevos' => $nuevos,
                        'total_transferidos' => 0,
                        'total_bajas' => $bajas,
                        'fecha_corte' => $fechaCorte,
                        'estado' => 'cerrado',
                        'es_sintetico' => true,
                        'registrado_por' => $userId,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('conteos.index')
                ->with('success', 'Serie sintética de 45 meses restaurada para pruebas funcionales. No constituye evidencia de precisión predictiva real.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al regenerar serie sintética: '.$e->getMessage());
        }
    }
}
