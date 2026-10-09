@extends('layouts.app')
@section('title', 'Actividades')
@section('page_title', 'Actividades institucionales')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
    <form method="GET" class="d-flex gap-2">
        <select name="nivel" class="form-select form-select-sm">
            <option value="">Todos los niveles</option>
            @foreach(['iglesia' => 'Iglesia', 'circuito' => 'Circuito', 'distrito' => 'Distrito'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(request('nivel') === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
        <select name="estado" class="form-select form-select-sm">
            <option value="">Todos los estados</option>
            @foreach(['programada' => 'Programada', 'en_curso' => 'En curso', 'completada' => 'Completada', 'cancelada' => 'Cancelada'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary btn-sm">Filtrar</button>
    </form>
    <a href="{{ route('actividades.create') }}" class="btn btn-sigem btn-sm"><i class="bi bi-plus-lg me-1"></i> Nueva actividad</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Fecha</th><th>Actividad</th><th>Ámbito</th><th>Tipo</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse($actividades as $actividad)
                <tr>
                    <td class="text-nowrap"><strong>{{ $actividad->fecha_inicio->format('d/m/Y') }}</strong><br><small class="text-muted">{{ $actividad->hora_inicio ? substr($actividad->hora_inicio, 0, 5) : 'Sin hora' }}</small></td>
                    <td><strong>{{ $actividad->titulo }}</strong><br><small class="text-muted">{{ $actividad->lugar ?: 'Lugar por confirmar' }}</small></td>
                    <td>{{ $actividad->iglesia?->nombre ?? $actividad->circuito?->nombre ?? 'Distrito Kollasuyo' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $actividad->tipo)) }}</td>
                    <td><span class="badge text-bg-{{ $actividad->estado === 'completada' ? 'success' : ($actividad->estado === 'cancelada' ? 'danger' : 'primary') }}">{{ ucfirst(str_replace('_', ' ', $actividad->estado)) }}</span></td>
                    <td class="text-end"><a href="{{ route('actividades.show', $actividad) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">No hay actividades para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $actividades->links() }}</div>
@endsection
