@extends('layouts.app')

@section('title', 'Usuarios y Pastores')
@section('page_title', 'Gestion de Usuarios y Encargados Pastorales')

@section('content')
<!-- Role Architecture Explanation Banner -->
<div class="card mb-4" style="border-left: 4px solid var(--sigem-primary);">
    <div class="card-body d-flex align-items-start gap-3">
        <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg,#1a3a5c,#2a5a8c); display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0;">
            <i class="bi bi-shield-lock-fill" style="font-size:1.3rem;"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-1">Estructura Conexional de Roles (IEMB Distrito Kollasuyo)</h6>
            <p style="font-size:0.82rem; color:#64748b; margin:0; line-height:1.6;">
                Conforme al Estatuto de la IEMB y el Proyecto de Grado, los roles reflejan los tres niveles de responsabilidad:
                <strong>Responsable Local (Pastor/a o Encargado/a)</strong> con acceso circunscrito a su iglesia local;
                <strong>Responsable de Circuito</strong> que supervisa las 6 iglesias de su circuito;
                <strong>Superintendente de Distrito</strong> con acceso total distrital y modelo predictivo Holt; y
                <strong>Administrador</strong> encargado del gobierno de usuarios y configuracion.
            </p>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre o correo..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Nivel Institucional (Rol)</label>
                <select name="rol" class="form-select form-select-sm">
                    <option value="">Todos los roles</option>
                    <option value="local" {{ request('rol') == 'local' ? 'selected' : '' }}>Pastor Local / Encargado</option>
                    <option value="circuito" {{ request('rol') == 'circuito' ? 'selected' : '' }}>Responsable Circuito</option>
                    <option value="distrito" {{ request('rol') == 'distrito' ? 'selected' : '' }}>Superintendente Distrito</option>
                    <option value="admin" {{ request('rol') == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Circuito</label>
                <select name="circuito_id" class="form-select form-select-sm">
                    <option value="">Todos los circuitos</option>
                    @foreach($circuitos as $c)
                        <option value="{{ $c->id }}" {{ request('circuito_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sigem btn-sm flex-grow-1">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
                <a href="{{ route('usuarios.create') }}" class="btn btn-success btn-sm" title="Registrar nuevo usuario">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Pastor/Usuario
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-person-badge-fill text-primary"></i>
            Encargados Pastorales y Usuarios Registrados ({{ $usuarios->total() }})
        </span>
        <a href="{{ route('usuarios.create') }}" class="btn btn-sigem btn-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Agregar Encargado Pastoral
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Nombre y Apellidos</th>
                        <th>Correo Institucional</th>
                        <th>Rol Asignado</th>
                        <th>Iglesia Local Asignada</th>
                        <th>Circuito</th>
                        <th>Telefono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                    <tr>
                        <td class="fw-semibold">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px; height:32px; border-radius:50%; background:{{ $u->rol === 'local' ? '#1a3a5c' : ($u->rol === 'circuito' ? '#0f6faa' : ($u->rol === 'distrito' ? '#c49a2a' : '#1e293b')) }}; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700;">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div>{{ $u->name }}</div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->rol === 'local')
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:600;">Pastor Local</span>
                            @elseif($u->rol === 'circuito')
                                <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:600;">Resp. Circuito</span>
                            @elseif($u->rol === 'distrito')
                                <span class="badge" style="background:#fef08a; color:#854d0e; font-weight:600;">Superintendente</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#334155; font-weight:600;">Administrador</span>
                            @endif
                        </td>
                        <td>
                            @if($u->iglesia)
                                <a href="{{ route('iglesias.show', $u->iglesia) }}" class="text-decoration-none fw-semibold" style="color:var(--sigem-primary);">
                                    {{ $u->iglesia->nombre }}
                                </a>
                            @else
                                <span class="text-muted" style="font-size:0.8rem;">--</span>
                            @endif
                        </td>
                        <td>{{ $u->circuito->nombre ?? ($u->iglesia->circuito->nombre ?? 'Distrital') }}</td>
                        <td>{{ $u->telefono ?? '--' }}</td>
                        <td>
                            <span class="badge-status badge-{{ $u->estado }}">
                                {{ ucfirst($u->estado) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No se encontraron usuarios registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($usuarios->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $usuarios->links() }}
    </div>
    @endif
</div>
@endsection
