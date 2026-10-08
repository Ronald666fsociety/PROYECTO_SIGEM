@extends('layouts.app')

@section('title', 'Conteos Mensuales')
@section('page_title', 'Registro de Conteos Mensuales de Membresia')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('conteos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Año</label>
                <select name="anio" class="form-select form-select-sm">
                    <option value="">Todos los años</option>
                    @foreach($anios as $a)
                        <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Iglesia Local</label>
                <select name="iglesia_id" class="form-select form-select-sm">
                    <option value="">Todas las iglesias</option>
                    @foreach($iglesias as $ig)
                        <option value="{{ $ig->id }}" {{ request('iglesia_id') == $ig->id ? 'selected' : '' }}>
                            {{ $ig->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sigem btn-sm w-100">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Counts Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-clipboard-data-fill text-primary"></i>
            Conteos Mensuales Cerrados ({{ $conteos->total() }} registros)
        </span>
        <span class="badge" style="background:#e0f2fe; color:#0369a1;">
            Base de calculo para Modelo Holt
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Iglesia</th>
                        <th>Circuito</th>
                        <th>Total Activos</th>
                        <th>Plenos</th>
                        <th>Preparatorios</th>
                        <th>Simpatizantes</th>
                        <th>Niños/as</th>
                        <th>Nuevos</th>
                        <th>Bajas</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conteos as $c)
                    <tr>
                        <td class="fw-bold">{{ $c->periodo }}</td>
                        <td>{{ $c->iglesia->nombre ?? '--' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $c->iglesia->circuito->nombre ?? '--' }}</span></td>
                        <td class="fw-bold text-primary">{{ number_format($c->total_activos) }}</td>
                        <td>{{ $c->miembros_plenos }}</td>
                        <td>{{ $c->miembros_preparatorios }}</td>
                        <td>{{ $c->simpatizantes }}</td>
                        <td>{{ $c->ninos }}</td>
                        <td><span class="text-success">+{{ $c->nuevos_ingresos }}</span></td>
                        <td><span class="text-danger">-{{ $c->bajas }}</span></td>
                        <td><span class="badge-status badge-{{ $c->estado }}">{{ ucfirst($c->estado) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">No se encontraron conteos mensuales.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($conteos->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $conteos->links() }}
    </div>
    @endif
</div>
@endsection
