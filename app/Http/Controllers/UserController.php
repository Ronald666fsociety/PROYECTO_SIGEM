<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Iglesia;
use App\Models\Circuito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['iglesia', 'circuito']);

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }
        if ($request->filled('circuito_id')) {
            $query->where('circuito_id', $request->circuito_id);
        }
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $usuarios = $query->orderBy('rol')->orderBy('name')->paginate(20);
        $circuitos = Circuito::where('estado', 'activo')->get();

        return view('usuarios.index', compact('usuarios', 'circuitos'));
    }

    public function create()
    {
        $iglesias = Iglesia::where('estado', 'activo')->orderBy('nombre')->get();
        $circuitos = Circuito::where('estado', 'activo')->orderBy('nombre')->get();

        return view('usuarios.create', compact('iglesias', 'circuitos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email|max:100',
            'password' => 'required|string|min:6',
            'rol' => 'required|in:admin,distrito,circuito,local',
            'iglesia_id' => 'nullable|required_if:rol,local|exists:iglesias,id',
            'circuito_id' => 'nullable|required_if:rol,circuito|exists:circuitos,id',
            'telefono' => 'nullable|string|max:20',
        ]);

        // If local role, assign circuit automatically from church
        if ($validated['rol'] === 'local' && !empty($validated['iglesia_id'])) {
            $iglesia = Iglesia::find($validated['iglesia_id']);
            if ($iglesia) {
                $validated['circuito_id'] = $iglesia->circuito_id;
                // Synchronize pastor name in church record
                $iglesia->update(['pastor_nombre' => $validated['name']]);
            }
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['estado'] = 'activo';

        User::create($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Encargado pastoral / Usuario registrado exitosamente.');
    }

    public function edit(User $usuario)
    {
        $iglesias = Iglesia::where('estado', 'activo')->orderBy('nombre')->get();
        $circuitos = Circuito::where('estado', 'activo')->orderBy('nombre')->get();

        return view('usuarios.edit', compact('usuario', 'iglesias', 'circuitos'));
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:100|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6',
            'rol' => 'required|in:admin,distrito,circuito,local',
            'iglesia_id' => 'nullable|required_if:rol,local|exists:iglesias,id',
            'circuito_id' => 'nullable|required_if:rol,circuito|exists:circuitos,id',
            'telefono' => 'nullable|string|max:20',
            'estado' => 'required|in:activo,inactivo',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($validated['rol'] === 'local' && !empty($validated['iglesia_id'])) {
            $iglesia = Iglesia::find($validated['iglesia_id']);
            if ($iglesia) {
                $validated['circuito_id'] = $iglesia->circuito_id;
                $iglesia->update(['pastor_nombre' => $validated['name']]);
            }
        }

        $usuario->update($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Datos del usuario / pastor actualizados exitosamente.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puede deshabilitar su propia cuenta activa.');
        }

        $usuario->update(['estado' => 'inactivo']);

        return redirect()->route('usuarios.index')
            ->with('success', 'El usuario ha sido marcado como inactivo.');
    }
}
