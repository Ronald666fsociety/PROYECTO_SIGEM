<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComunicacionRequest;
use App\Models\Circuito;
use App\Models\Comunicacion;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ComunicacionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $recibidas = Comunicacion::query()
            ->with(['remitente', 'iglesia', 'circuito'])
            ->whereHas('destinatarios', fn (Builder $query) => $query->where('destinatario_id', $user->id))
            ->withExists(['destinatarios as leida_por_usuario' => fn (Builder $query) => $query
                ->where('destinatario_id', $user->id)
                ->where('leido', true)])
            ->latest('fecha_envio')
            ->paginate(10, ['*'], 'recibidas');

        $enviadas = Comunicacion::query()
            ->with(['iglesia', 'circuito'])
            ->withCount('destinatarios')
            ->where('remitente_id', $user->id)
            ->latest('fecha_envio')
            ->paginate(10, ['*'], 'enviadas');

        return view('comunicaciones.index', compact('recibidas', 'enviadas'));
    }

    public function create(Request $request): View
    {
        [$iglesias, $circuitos] = $this->catalogosPermitidos($request->user());

        return view('comunicaciones.create', compact('iglesias', 'circuitos'));
    }

    public function store(StoreComunicacionRequest $request): RedirectResponse
    {
        $data = $this->normalizeScope($request->validated(), $request->user());
        $destinatarios = $this->destinatarios($data, $request->user());

        if ($destinatarios->isEmpty()) {
            throw ValidationException::withMessages([
                'nivel' => 'No existen otros usuarios activos en el destino seleccionado.',
            ]);
        }

        $comunicacion = DB::transaction(function () use ($data, $destinatarios, $request) {
            $comunicacion = Comunicacion::create([
                ...$data,
                'remitente_id' => $request->user()->id,
                'estado' => 'enviada',
                'fecha_envio' => now(),
            ]);

            $comunicacion->destinatarios()->createMany(
                $destinatarios->map(fn (int $id) => ['destinatario_id' => $id])->all()
            );

            return $comunicacion;
        });

        return redirect()
            ->route('comunicaciones.show', $comunicacion)
            ->with('success', 'La comunicación interna fue enviada correctamente.');
    }

    public function show(Request $request, Comunicacion $comunicacion): View
    {
        $user = $request->user();
        $destinatario = $comunicacion->destinatarios()->where('destinatario_id', $user->id)->first();
        abort_unless((int) $comunicacion->remitente_id === (int) $user->id || $destinatario, 403);

        if ($destinatario && ! $destinatario->leido) {
            $destinatario->update(['leido' => true, 'fecha_lectura' => now()]);
        }

        $comunicacion->load(['remitente', 'iglesia', 'circuito', 'destinatarios.destinatario']);

        return view('comunicaciones.show', compact('comunicacion'));
    }

    /** @return array{0: \Illuminate\Database\Eloquent\Collection<int, Iglesia>, 1: \Illuminate\Database\Eloquent\Collection<int, Circuito>} */
    private function catalogosPermitidos(User $user): array
    {
        $iglesias = Iglesia::query()
            ->when($user->isLocal(), fn (Builder $query) => $query->whereKey($user->iglesia_id))
            ->when($user->isCircuito(), fn (Builder $query) => $query->where('circuito_id', $user->circuito_id))
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $circuitos = Circuito::query()
            ->when(! $user->hasAccesoDistrito(), fn (Builder $query) => $query->whereKey($user->circuito_id))
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        return [$iglesias, $circuitos];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function normalizeScope(array $data, User $user): array
    {
        if ($user->isLocal()) {
            $data['nivel'] = 'iglesia';
            $data['iglesia_id'] = $user->iglesia_id;
            $data['circuito_id'] = $user->circuito_id;

            return $data;
        }

        if ($user->isCircuito()) {
            abort_if($data['nivel'] === 'distrito', 403);
            $data['circuito_id'] = $user->circuito_id;
            if ($data['nivel'] === 'iglesia') {
                $iglesia = Iglesia::findOrFail($data['iglesia_id']);
                abort_unless($user->puedeGestionarIglesia($iglesia), 403);
            } else {
                $data['iglesia_id'] = null;
            }

            return $data;
        }

        if ($data['nivel'] === 'iglesia') {
            $iglesia = Iglesia::findOrFail($data['iglesia_id']);
            $data['circuito_id'] = $iglesia->circuito_id;
        } elseif ($data['nivel'] === 'circuito') {
            $data['iglesia_id'] = null;
        } else {
            $data['iglesia_id'] = null;
            $data['circuito_id'] = null;
        }

        return $data;
    }

    /** @param array<string, mixed> $data @return \Illuminate\Support\Collection<int, int> */
    private function destinatarios(array $data, User $remitente): Collection
    {
        $query = User::query()->where('estado', 'activo')->whereKeyNot($remitente->id);

        if ($data['nivel'] === 'iglesia') {
            $query->where('iglesia_id', $data['iglesia_id']);
        } elseif ($data['nivel'] === 'circuito') {
            $query->where(function (Builder $scope) use ($data) {
                $scope->where('circuito_id', $data['circuito_id'])
                    ->orWhereHas('iglesia', fn (Builder $churches) => $churches->where('circuito_id', $data['circuito_id']));
            });
        }

        return $query->pluck('id');
    }
}
