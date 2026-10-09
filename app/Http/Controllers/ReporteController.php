<?php

namespace App\Http\Controllers;

use App\Models\ConteoMembresia;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        $anio = $this->selectedYear($request);
        $iglesias = $this->iglesiasPermitidas($request->user());
        $iglesiaIds = $iglesias->pluck('id');

        $serie = $this->conteosPermitidos($request->user())
            ->where('anio', $anio)
            ->selectRaw('mes, SUM(total_activos) as total_activos')
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total_activos', 'mes');

        $serieMensual = collect(range(1, 12))->map(fn (int $mes) => [
            'mes' => $mes,
            'total' => (int) ($serie[$mes] ?? 0),
        ]);
        $etiquetasSerie = $serieMensual
            ->map(fn (array $fila): string => str_pad((string) $fila['mes'], 2, '0', STR_PAD_LEFT).'/'.$anio)
            ->values();
        $valoresSerie = $serieMensual->pluck('total')->values();

        $ultimoConteo = $this->conteosPermitidos($request->user())
            ->where('anio', $anio)
            ->orderByDesc('mes')
            ->first();

        $ultimoMes = $ultimoConteo?->mes;
        $detalle = $iglesias->map(function (Iglesia $iglesia) use ($anio, $ultimoMes) {
            $conteo = $ultimoMes
                ? $iglesia->conteos->first(fn (ConteoMembresia $item) => (int) $item->anio === $anio && (int) $item->mes === (int) $ultimoMes)
                : null;

            return [
                'iglesia' => $iglesia,
                'conteo' => $conteo,
            ];
        });

        $totalActual = $detalle->sum(fn (array $fila) => (int) ($fila['conteo']?->total_activos ?? 0));
        $iglesiasConDato = $detalle->filter(fn (array $fila) => $fila['conteo'] !== null)->count();
        $cobertura = $iglesias->isEmpty() ? 0 : round(($iglesiasConDato / $iglesias->count()) * 100, 1);

        $fuentes = $this->conteosPermitidos($request->user())
            ->where('anio', $anio)
            ->selectRaw("COALESCE(fuente_datos, 'sin_clasificar') as fuente, COUNT(*) as total")
            ->groupBy('fuente_datos')
            ->pluck('total', 'fuente');

        return view('reportes.index', compact(
            'anio',
            'serieMensual',
            'etiquetasSerie',
            'valoresSerie',
            'ultimoMes',
            'detalle',
            'totalActual',
            'iglesiasConDato',
            'cobertura',
            'fuentes',
        ));
    }

    public function exportar(Request $request): StreamedResponse
    {
        $anio = $this->selectedYear($request);
        $query = $this->conteosPermitidos($request->user())
            ->with(['iglesia.circuito', 'registrador'])
            ->where('anio', $anio)
            ->orderBy('anio')
            ->orderBy('mes')
            ->orderBy('iglesia_id');

        return response()->streamDownload(function () use ($query) {
            echo "\xEF\xBB\xBF";
            $stream = fopen('php://output', 'w');
            fputcsv($stream, [
                'Circuito', 'Iglesia', 'Código', 'Año', 'Mes', 'Miembros activos',
                'Fecha de corte', 'Estado', 'Fuente', 'Versión de datos', 'Archivo de origen',
            ], ';');

            $query->chunk(200, function ($conteos) use ($stream) {
                foreach ($conteos as $conteo) {
                    fputcsv($stream, [
                        $conteo->iglesia?->circuito?->nombre,
                        $conteo->iglesia?->nombre,
                        $conteo->iglesia?->codigo,
                        $conteo->anio,
                        $conteo->mes,
                        $conteo->total_activos,
                        $conteo->fecha_corte?->format('Y-m-d'),
                        $conteo->estado,
                        $conteo->fuente_datos,
                        $conteo->version_datos,
                        $conteo->archivo_origen,
                    ], ';');
                }
            });

            fclose($stream);
        }, "reporte_membresia_{$anio}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function selectedYear(Request $request): int
    {
        $anio = $request->integer('anio', (int) date('Y'));

        return min(max($anio, 2023), 2100);
    }

    /** @return Collection<int, Iglesia> */
    private function iglesiasPermitidas(User $user): Collection
    {
        return Iglesia::query()
            ->with(['circuito', 'conteos'])
            ->when($user->isLocal(), fn (Builder $query) => $query->whereKey($user->iglesia_id))
            ->when($user->isCircuito(), fn (Builder $query) => $query->where('circuito_id', $user->circuito_id))
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();
    }

    private function conteosPermitidos(User $user): Builder
    {
        return ConteoMembresia::query()
            ->when($user->isLocal(), fn (Builder $query) => $query->where('iglesia_id', $user->iglesia_id))
            ->when($user->isCircuito(), fn (Builder $query) => $query->whereHas(
                'iglesia',
                fn (Builder $iglesias) => $iglesias->where('circuito_id', $user->circuito_id),
            ));
    }
}
