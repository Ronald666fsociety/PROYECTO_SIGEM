<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCircuitoRequest;
use App\Models\Circuito;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CircuitoController extends Controller
{
    public function index(): View
    {
        $circuitos = Circuito::query()
            ->withCount(['iglesias', 'usuarios'])
            ->orderBy('nombre')
            ->paginate(12);

        return view('circuitos.index', compact('circuitos'));
    }

    public function create(): View
    {
        return view('circuitos.create');
    }

    public function store(SaveCircuitoRequest $request): RedirectResponse
    {
        Circuito::create($request->validated());

        return redirect()->route('circuitos.index')->with('success', 'El circuito fue registrado correctamente.');
    }

    public function edit(Circuito $circuito): View
    {
        return view('circuitos.edit', compact('circuito'));
    }

    public function update(SaveCircuitoRequest $request, Circuito $circuito): RedirectResponse
    {
        $circuito->update($request->validated());

        return redirect()->route('circuitos.index')->with('success', 'El circuito fue actualizado correctamente.');
    }

    public function destroy(Circuito $circuito): RedirectResponse
    {
        $circuito->update(['estado' => 'inactivo']);

        return redirect()->route('circuitos.index')->with('success', 'El circuito fue marcado como inactivo.');
    }
}
