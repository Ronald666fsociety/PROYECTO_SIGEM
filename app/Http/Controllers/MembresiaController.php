<?php

namespace App\Http\Controllers;

use App\Models\Iglesia;
use App\Models\Miembro;
use Illuminate\Http\Request;

class MembresiaController extends Controller
{
    /**
     * Check if the authenticated user has permission to manage or view this member.
     */
    private function autorizarMiembro(Miembro $miembro): void
    {
        $user = auth()->user();

        if ($user->hasAccesoDistrito()) {
            return; // Admin and Distrito have universal access
        }

        if ($user->isLocal()) {
            if ((int) $miembro->iglesia_id !== (int) $user->iglesia_id) {
                abort(403, 'Acceso restringido: No tiene autorización para consultar o gestionar miembros pertenecientes a otra iglesia local.');
            }

            return;
        }

        if ($user->isCircuito()) {
            $circuitoId = $miembro->iglesia->circuito_id ?? null;
            if ((int) $circuitoId !== (int) $user->circuito_id) {
                abort(403, 'Acceso restringido: No tiene autorización para consultar o gestionar miembros pertenecientes a otro circuito.');
            }

            return;
        }

        abort(403, 'Acceso no autorizado.');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Miembro::with('iglesia.circuito');

        // Scope by institutional role
        if ($user->isLocal()) {
            $query->where('iglesia_id', $user->iglesia_id);
            $iglesias = Iglesia::where('id', $user->iglesia_id)->get();
        } elseif ($user->isCircuito()) {
            $query->whereHas('iglesia', function ($q) use ($user) {
                $q->where('circuito_id', $user->circuito_id);
            });
            $iglesias = Iglesia::where('circuito_id', $user->circuito_id)->where('estado', 'activo')->get();
        } else {
            // Admin and Distrito can view all and filter
            if ($request->filled('iglesia_id')) {
                $query->where('iglesia_id', $request->iglesia_id);
            }
            $iglesias = Iglesia::where('estado', 'activo')->orderBy('nombre')->get();
        }

        // Additional filters
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('ci', 'like', "%{$buscar}%");
            });
        }

        $miembros = $query->orderBy('apellidos')->paginate(20);

        return view('membresia.index', compact('miembros', 'iglesias'));
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->isLocal()) {
            $iglesias = Iglesia::where('id', $user->iglesia_id)->get();
        } elseif ($user->isCircuito()) {
            $iglesias = Iglesia::where('circuito_id', $user->circuito_id)->where('estado', 'activo')->orderBy('nombre')->get();
        } else {
            $iglesias = Iglesia::where('estado', 'activo')->orderBy('nombre')->get();
        }

        return view('membresia.create', compact('iglesias'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'iglesia_id' => 'required|exists:iglesias,id',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'ci' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:masculino,femenino',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'categoria' => 'required|in:miembro_pleno,miembro_preparatorio,simpatizante,nino',
            'fecha_ingreso' => 'nullable|date',
            'direccion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        // Enforce role assignment boundaries
        if ($user->isLocal()) {
            $validated['iglesia_id'] = $user->iglesia_id;
        } elseif ($user->isCircuito()) {
            $iglesia = Iglesia::find($validated['iglesia_id']);
            if (! $iglesia || (int) $iglesia->circuito_id !== (int) $user->circuito_id) {
                abort(403, 'No puede registrar miembros en iglesias fuera de su circuito asignado.');
            }
        }

        $validated['estado'] = 'activo';
        $validated['fecha_ingreso'] = $validated['fecha_ingreso'] ?? now()->toDateString();
        // Se conserva el campo heredado mientras existan instalaciones anteriores de SIGEM.
        $validated['fecha_registro'] = $validated['fecha_ingreso'];
        Miembro::create($validated);

        return redirect()->route('membresia.index')
            ->with('success', 'Miembro registrado exitosamente en la congregación.');
    }

    public function show(Miembro $miembro)
    {
        $this->autorizarMiembro($miembro);
        $miembro->load('iglesia.circuito');

        return view('membresia.show', compact('miembro'));
    }

    public function edit(Miembro $miembro)
    {
        $this->autorizarMiembro($miembro);
        $user = auth()->user();

        if ($user->isLocal()) {
            $iglesias = Iglesia::where('id', $user->iglesia_id)->get();
        } elseif ($user->isCircuito()) {
            $iglesias = Iglesia::where('circuito_id', $user->circuito_id)->where('estado', 'activo')->get();
        } else {
            $iglesias = Iglesia::where('estado', 'activo')->orderBy('nombre')->get();
        }

        return view('membresia.edit', compact('miembro', 'iglesias'));
    }

    public function update(Request $request, Miembro $miembro)
    {
        $this->autorizarMiembro($miembro);
        $user = auth()->user();

        $validated = $request->validate([
            'iglesia_id' => 'required|exists:iglesias,id',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'ci' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:masculino,femenino',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'categoria' => 'required|in:miembro_pleno,miembro_preparatorio,simpatizante,nino',
            'estado' => 'required|in:activo,inactivo,transferido',
            'fecha_ingreso' => 'nullable|date',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        if ($user->isLocal()) {
            $validated['iglesia_id'] = $user->iglesia_id; // Prevent church tampering
        } elseif ($user->isCircuito()) {
            $iglesia = Iglesia::find($validated['iglesia_id']);
            if (! $iglesia || (int) $iglesia->circuito_id !== (int) $user->circuito_id) {
                abort(403, 'No puede reasignar miembros a iglesias fuera de su circuito asignado.');
            }
        }

        if (! empty($validated['fecha_ingreso'])) {
            $validated['fecha_registro'] = $validated['fecha_ingreso'];
        }

        $miembro->update($validated);

        return redirect()->route('membresia.index')
            ->with('success', 'Datos del miembro actualizados exitosamente.');
    }

    public function destroy(Miembro $miembro)
    {
        $this->autorizarMiembro($miembro);
        $user = auth()->user();

        // La baja es administrativa: se conserva el historial institucional y su trazabilidad.
        $miembro->update([
            'estado' => 'inactivo',
            'fecha_baja' => now()->toDateString(),
            'motivo_baja' => 'Baja registrada por '.$user->rol_display.' ('.$user->name.')',
        ]);

        return redirect()->route('membresia.index')
            ->with('success', 'El miembro ha sido dado de baja; su historial se conserva.');
    }
}
