@extends('layouts.app')

@section('title', 'Membresia')
@section('page_title', 'Registro General de Membresia')

@section('content')
<!-- Filter & Actions Bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('membresia.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre, apellido o CI..." value="{{ request('buscar') }}">
            </div>
            @if(!auth()->user()->isLocal())
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">
                    {{ auth()->user()->isCircuito() ? 'Iglesia del Circuito' : 'Iglesia' }}
                </label>
                <select name="iglesia_id" class="form-select form-select-sm">
                    <option value="">Todas las iglesias</option>
                    @foreach($iglesias as $ig)
                        <option value="{{ $ig->id }}" {{ request('iglesia_id') == $ig->id ? 'selected' : '' }}>{{ $ig->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Categoria</label>
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="miembro_pleno" {{ request('categoria') == 'miembro_pleno' ? 'selected' : '' }}>Miembros Plenos</option>
                    <option value="miembro_preparatorio" {{ request('categoria') == 'miembro_preparatorio' ? 'selected' : '' }}>Preparatorios</option>
                    <option value="simpatizante" {{ request('categoria') == 'simpatizante' ? 'selected' : '' }}>Simpatizantes</option>
                    <option value="nino" {{ request('categoria') == 'nino' ? 'selected' : '' }}>Niños/as</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    <option value="transferido" {{ request('estado') == 'transferido' ? 'selected' : '' }}>Transferido</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sigem btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
                <a href="{{ route('membresia.create') }}" class="btn btn-success btn-sm" title="Registrar nuevo miembro">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Members Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-people-fill text-primary"></i>
            Listado de Miembros ({{ $miembros->total() }} registros)
        </span>
        <a href="{{ route('membresia.create') }}" class="btn btn-sigem btn-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Miembro
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>CI</th>
                        <th>Iglesia</th>
                        <th>Circuito</th>
                        <th>Categoria</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($miembros as $m)
                    <tr>
                        <td class="fw-semibold">
                            <a href="{{ route('membresia.show', $m) }}" class="text-decoration-none text-dark">
                                {{ $m->nombre_completo }}
                            </a>
                        </td>
                        <td>{{ $m->ci ?? '--' }}</td>
                        <td>{{ $m->iglesia->nombre ?? '--' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $m->iglesia->circuito->nombre ?? '--' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:500;">
                                {{ $m->categoria_display }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status badge-{{ $m->estado }}">
                                {{ ucfirst($m->estado) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('membresia.show', $m) }}" class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('membresia.edit', $m) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('membresia.destroy', $m) }}" class="d-inline" onsubmit="return confirm('¿Confirma que desea {{ auth()->user()->isAdmin() ? 'eliminar' : 'dar de baja' }} a este miembro?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ auth()->user()->isAdmin() ? 'Eliminar de la base de datos' : 'Registrar Baja Institucional' }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No se encontraron miembros con los filtros seleccionados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($miembros->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $miembros->links() }}
    </div>
    @endif
</div>
@endsection
