<?php

namespace App\Http\Controllers;

use App\Models\Circuito;
use App\Models\Iglesia;
use App\Models\Miembro;
use App\Models\ConteoMembresia;
use App\Models\Actividad;
use App\Models\Comunicacion;
use App\Models\Prediccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $anioActual = date('Y');

        if ($user->isLocal()) {
            // Scope strictly to the pastor's local church
            $totalIglesias = 1;
            $totalCircuitos = 1;
            $totalMiembros = Miembro::where('iglesia_id', $user->iglesia_id)->where('estado', 'activo')->count();
            $totalUsuarios = 1;

            $ultimoConteo = ConteoMembresia::where('iglesia_id', $user->iglesia_id)
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->first();

            $totalMembresiaActual = $ultimoConteo ? $ultimoConteo->total_activos : 0;
            $periodoActual = $ultimoConteo ? $ultimoConteo->periodo : 'Sin datos';

            $tendencia = 0;
            if ($ultimoConteo) {
                $mesAnterior = $ultimoConteo->mes - 1;
                $anioAnterior = $ultimoConteo->anio;
                if ($mesAnterior < 1) {
                    $mesAnterior = 12;
                    $anioAnterior--;
                }
                $anterior = ConteoMembresia::where('iglesia_id', $user->iglesia_id)
                    ->where('anio', $anioAnterior)
                    ->where('mes', $mesAnterior)
                    ->first();
                if ($anterior && $anterior->total_activos > 0) {
                    $tendencia = round(($totalMembresiaActual - $anterior->total_activos) / $anterior->total_activos * 100, 1);
                }
            }

            $nuevosEsteAnio = Miembro::where('iglesia_id', $user->iglesia_id)->whereYear('fecha_ingreso', $anioActual)->count();
            $bajasEsteAnio = Miembro::where('iglesia_id', $user->iglesia_id)->whereYear('fecha_baja', $anioActual)->count();

            $serieReciente = ConteoMembresia::where('iglesia_id', $user->iglesia_id)
                ->select('anio', 'mes', 'total_activos')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();

            $porCategoria = Miembro::where('iglesia_id', $user->iglesia_id)
                ->where('estado', 'activo')
                ->selectRaw('categoria, COUNT(*) as total')
                ->groupBy('categoria')
                ->pluck('total', 'categoria')
                ->toArray();

            $porCircuito = collect();
            $ultimaPrediccion = null; // Holt is a district-level strategic module

            $actividades = Actividad::where('iglesia_id', $user->iglesia_id)
                ->where('fecha_inicio', '>=', now()->toDateString())
                ->orderBy('fecha_inicio')
                ->limit(5)
                ->get();

        } elseif ($user->isCircuito()) {
            // Scope to the 6 churches in the coordinator's circuit
            $totalIglesias = Iglesia::where('circuito_id', $user->circuito_id)->where('estado', 'activo')->count();
            $totalCircuitos = 1;
            $totalMiembros = Miembro::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                ->where('estado', 'activo')
                ->count();
            $totalUsuarios = \App\Models\User::where('circuito_id', $user->circuito_id)->where('estado', 'activo')->count();

            $ultimoConteo = ConteoMembresia::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->first();

            $totalMembresiaActual = 0;
            $periodoActual = 'Sin datos';
            if ($ultimoConteo) {
                $totalMembresiaActual = ConteoMembresia::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                    ->where('anio', $ultimoConteo->anio)
                    ->where('mes', $ultimoConteo->mes)
                    ->sum('total_activos');
                $periodoActual = $ultimoConteo->periodo;
            }

            $tendencia = 0;
            if ($ultimoConteo) {
                $mesAnterior = $ultimoConteo->mes - 1;
                $anioAnterior = $ultimoConteo->anio;
                if ($mesAnterior < 1) {
                    $mesAnterior = 12;
                    $anioAnterior--;
                }
                $anterior = ConteoMembresia::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                    ->where('anio', $anioAnterior)
                    ->where('mes', $mesAnterior)
                    ->sum('total_activos');
                if ($anterior > 0) {
                    $tendencia = round(($totalMembresiaActual - $anterior) / $anterior * 100, 1);
                }
            }

            $nuevosEsteAnio = Miembro::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                ->whereYear('fecha_ingreso', $anioActual)
                ->count();
            $bajasEsteAnio = Miembro::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                ->whereYear('fecha_baja', $anioActual)
                ->count();

            $serieReciente = ConteoMembresia::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                ->select('anio', 'mes', DB::raw('SUM(total_activos) as total_activos'))
                ->groupBy('anio', 'mes')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();

            $porCategoria = Miembro::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                ->where('estado', 'activo')
                ->selectRaw('categoria, COUNT(*) as total')
                ->groupBy('categoria')
                ->pluck('total', 'categoria')
                ->toArray();

            $porCircuito = collect();
            $ultimaPrediccion = null;

            $actividades = Actividad::whereHas('iglesia', fn($q) => $q->where('circuito_id', $user->circuito_id))
                ->where('fecha_inicio', '>=', now()->toDateString())
                ->orderBy('fecha_inicio')
                ->limit(5)
                ->get();

        } else {
            // Admin and Superintendente de Distrito: Full consolidated district overview
            $totalIglesias = Iglesia::where('estado', 'activo')->count();
            $totalCircuitos = Circuito::where('estado', 'activo')->count();
            $totalMiembros = Miembro::where('estado', 'activo')->count();
            $totalUsuarios = \App\Models\User::where('estado', 'activo')->count();

            $ultimoConteo = ConteoMembresia::orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->first();

            $totalMembresiaActual = 0;
            $periodoActual = 'Sin datos';
            if ($ultimoConteo) {
                $totalMembresiaActual = ConteoMembresia::where('anio', $ultimoConteo->anio)
                    ->where('mes', $ultimoConteo->mes)
                    ->sum('total_activos');
                $periodoActual = $ultimoConteo->periodo;
            }

            $tendencia = 0;
            if ($ultimoConteo) {
                $mesAnterior = $ultimoConteo->mes - 1;
                $anioAnterior = $ultimoConteo->anio;
                if ($mesAnterior < 1) {
                    $mesAnterior = 12;
                    $anioAnterior--;
                }
                $totalAnterior = ConteoMembresia::where('anio', $anioAnterior)
                    ->where('mes', $mesAnterior)
                    ->sum('total_activos');
                if ($totalAnterior > 0) {
                    $tendencia = round(($totalMembresiaActual - $totalAnterior) / $totalAnterior * 100, 1);
                }
            }

            $nuevosEsteAnio = Miembro::whereYear('fecha_ingreso', $anioActual)->count();
            $bajasEsteAnio = Miembro::whereYear('fecha_baja', $anioActual)->count();

            $serieReciente = ConteoMembresia::select(
                    'anio', 'mes',
                    DB::raw('SUM(total_activos) as total_activos')
                )
                ->groupBy('anio', 'mes')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();

            $porCategoria = Miembro::where('estado', 'activo')
                ->selectRaw('categoria, COUNT(*) as total')
                ->groupBy('categoria')
                ->pluck('total', 'categoria')
                ->toArray();

            $porCircuito = Circuito::with(['iglesias.miembrosActivos'])
                ->get()
                ->map(function ($circuito) {
                    $total = 0;
                    foreach ($circuito->iglesias as $ig) {
                        $total += $ig->miembrosActivos->count();
                    }
                    return [
                        'nombre' => $circuito->nombre,
                        'total' => $total,
                        'iglesias' => $circuito->iglesias->count(),
                    ];
                });

            $ultimaPrediccion = Prediccion::with('valores')
                ->orderBy('created_at', 'desc')
                ->first();

            $actividades = Actividad::with(['iglesia', 'circuito'])
                ->where('fecha_inicio', '>=', now()->toDateString())
                ->orderBy('fecha_inicio')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact(
            'totalIglesias', 'totalCircuitos', 'totalMiembros', 'totalUsuarios',
            'totalMembresiaActual', 'periodoActual', 'tendencia',
            'nuevosEsteAnio', 'bajasEsteAnio',
            'serieReciente', 'porCategoria', 'porCircuito',
            'ultimaPrediccion', 'actividades'
        ));
    }
}
