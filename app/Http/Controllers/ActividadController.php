<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveActividadRequest;
use App\Models\Actividad;
use App\Models\Circuito;
use App\Models\Iglesia;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActividadController extends Controller
{
    public function index(Request $request): View
    {
        $query = Actividad::query()->with(['iglesia', 'circuito', 'creador']);
        $this->applyVisibilityScope($query, $request->user());

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->string('nivel'));
        }

        $actividades = $query
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('hora_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('actividades.index', compact('actividades'));
    }

    public function create(Request $request): View
    {
        [$iglesias, $circuitos] = $this->catalogosPermitidos($request->user());

        return view('actividades.create', compact('iglesias', 'circuitos'));
    }

    public function store(SaveActividadRequest $request): RedirectResponse
    {
        $data = $this->normalizeScope($request->validated(), $request->user());
        $data['creado_por'] = $request->user()->id;

        $actividad = Actividad::create($data);

        return redirect()
            ->route('actividades.show', $actividad)
            ->with('success', 'La actividad fue registrada correctamente.');
    }

    public function show(Request $request, Actividad $actividad): View
    {
        abort_unless($this->canView($request->user(), $actividad), 403);
        $actividad->load(['iglesia', 'circuito', 'creador']);

        return view('actividades.show', compact('actividad'));
    }

    public function edit(Request $request, Actividad $actividad): View
    {
        abort_unless($this->canManage($request->user(), $actividad), 403);
        [$iglesias, $circuitos] = $this->catalogosPermitidos($request->user());

        return view('actividades.edit', compact('actividad', 'iglesias', 'circuitos'));
    }

    public function update(SaveActividadRequest $request, Actividad $actividad): RedirectResponse
    {
        abort_unless($this->canManage($request->user(), $actividad), 403);
        $actividad->update($this->normalizeScope($request->validated(), $request->user()));

        return redirect()
            ->route('actividades.show', $actividad)
            ->with('success', 'La actividad fue actualizada correctamente.');
    }

    public function destroy(Request $request, Actividad $actividad): RedirectResponse
    {
        abort_unless($this->canManage($request->user(), $actividad), 403);
        $actividad->update(['estado' => 'cancelada']);

        return redirect()
            ->route('actividades.index')
            ->with('success', 'La actividad fue marcada como cancelada; se conserva su trazabilidad.');
    }

    private function applyVisibilityScope(Builder $query, User $user): void
    {
        if ($user->hasAccesoDistrito()) {
            return;
        }

        if ($user->isCircuito()) {
            $query->where(function (Builder $scope) use ($user) {
                $scope->where('nivel', 'distrito')
                    ->orWhere('circuito_id', $user->circuito_id)
                    ->orWhereHas('iglesia', fn (Builder $iglesias) => $iglesias->where('circuito_id', $user->circuito_id));
            });

            return;
        }

        $query->where(function (Builder $scope) use ($user) {
            $scope->where('nivel', 'distrito')
                ->orWhere('circuito_id', $user->circuito_id)
                ->orWhere('iglesia_id', $user->iglesia_id);
        });
    }

    /** @return array{0: Collection<int, Iglesia>, 1: Collection<int, Circuito>} */
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

    private function canView(User $user, Actividad $actividad): bool
    {
        return $user->hasAccesoDistrito()
            || $actividad->nivel === 'distrito'
            || ($actividad->circuito_id && (int) $actividad->circuito_id === (int) $user->circuito_id)
            || ($actividad->iglesia_id && $actividad->iglesia && $user->puedeGestionarIglesia($actividad->iglesia));
    }

    private function canManage(User $user, Actividad $actividad): bool
    {
        if ($user->hasAccesoDistrito()) {
            return true;
        }

        if ($user->isCircuito()) {
            return $actividad->nivel !== 'distrito'
                && ((int) $actividad->circuito_id === (int) $user->circuito_id
                    || ($actividad->iglesia && $user->puedeGestionarIglesia($actividad->iglesia)));
        }

        return $actividad->nivel === 'iglesia'
            && (int) $actividad->iglesia_id === (int) $user->iglesia_id;
    }
}
