<?php

namespace App\Http\Controllers;

use App\Models\Iglesia;
use App\Models\Circuito;
use Illuminate\Http\Request;

class IglesiaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Iglesia::with('circuito')->withCount('miembrosActivos');

        if ($user->isLocal()) {
            $query->where('id', $user->iglesia_id);
            $circuitos = Circuito::where('id', $user->circuito_id)->get();
        } elseif ($user->isCircuito()) {
            $query->where('circuito_id', $user->circuito_id);
            $circuitos = Circuito::where('id', $user->circuito_id)->get();
        } else {
            if ($request->filled('circuito_id')) {
                $query->where('circuito_id', $request->circuito_id);
            }
            $circuitos = Circuito::where('estado', 'activo')->orderBy('nombre')->get();
        }

        $iglesias = $query->orderBy('nombre')->paginate(24);

        return view('iglesias.index', compact('iglesias', 'circuitos'));
    }

    public function show(Iglesia $iglesia)
    {
        $user = auth()->user();

        // Scope validation
        if ($user->isLocal() && (int)$iglesia->id !== (int)$user->iglesia_id) {
            abort(403, 'Acceso restringido: No tiene autorización para consultar los expedientes ni la gestión de otra iglesia.');
        }

        if ($user->isCircuito() && (int)$iglesia->circuito_id !== (int)$user->circuito_id) {
            abort(403, 'Acceso restringido: Esta iglesia no corresponde a su circuito asignado.');
        }

        $iglesia->load('circuito', 'miembros', 'conteos', 'actividades');
        $miembrosActivos = $iglesia->miembros()->where('estado', 'activo')->count();
        $miembrosPorCategoria = $iglesia->miembros()
            ->where('estado', 'activo')
            ->selectRaw('categoria, COUNT(*) as total')
            ->groupBy('categoria')
            ->get();

        $conteos = $iglesia->conteos()
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        return view('iglesias.show', compact('iglesia', 'miembrosActivos', 'miembrosPorCategoria', 'conteos'));
    }
}
