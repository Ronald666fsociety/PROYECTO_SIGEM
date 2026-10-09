<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveIglesiaRequest;
use App\Models\Circuito;
use App\Models\Iglesia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IglesiaController extends Controller
{
    public function index(Request $request): View
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

    public function create(): View
    {
        $circuitos = Circuito::where('estado', 'activo')->orderBy('nombre')->get();

        return view('iglesias.create', compact('circuitos'));
    }

    public function store(SaveIglesiaRequest $request): RedirectResponse
    {
        $iglesia = Iglesia::create($request->validated());

        return redirect()->route('iglesias.show', $iglesia)->with('success', 'La iglesia fue registrada correctamente.');
    }

    public function show(Iglesia $iglesia): View
    {
        $user = auth()->user();

        // Scope validation
        if ($user->isLocal() && (int) $iglesia->id !== (int) $user->iglesia_id) {
            abort(403, 'Acceso restringido: No tiene autorización para consultar los expedientes ni la gestión de otra iglesia.');
        }

        if ($user->isCircuito() && (int) $iglesia->circuito_id !== (int) $user->circuito_id) {
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

    public function edit(Iglesia $iglesia): View
    {
        $circuitos = Circuito::where('estado', 'activo')->orderBy('nombre')->get();

        return view('iglesias.edit', compact('iglesia', 'circuitos'));
    }

    public function update(SaveIglesiaRequest $request, Iglesia $iglesia): RedirectResponse
    {
        $iglesia->update($request->validated());

        return redirect()->route('iglesias.show', $iglesia)->with('success', 'La iglesia fue actualizada correctamente.');
    }

    public function destroy(Iglesia $iglesia): RedirectResponse
    {
        $iglesia->update(['estado' => 'inactivo']);

        return redirect()->route('iglesias.index')->with('success', 'La iglesia fue marcada como inactiva.');
    }
}
