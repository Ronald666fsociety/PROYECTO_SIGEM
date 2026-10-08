@extends('layouts.app')

@section('title', $miembro->nombre_completo)
@section('page_title', 'Ficha del Miembro')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <a href="{{ route('membresia.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Membresia
    </a>
    <a href="{{ route('membresia.edit', $miembro) }}" class="btn btn-sigem btn-sm">
        <i class="bi bi-pencil me-1"></i> Editar Datos
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width:64px; height:64px; border-radius:16px; background:linear-gradient(135deg,#1a3a5c,#2a5a8c); display:flex; align-items:center; justify-content:center; color:#fff;">
                <i class="bi bi-person-fill" style="font-size:1.8rem;"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">{{ $miembro->nombre_completo }}</h4>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge-status badge-{{ $miembro->estado }}">{{ ucfirst($miembro->estado) }}</span>
                    <span class="badge" style="background:#e0f2fe; color:#0369a1;">{{ $miembro->categoria_display }}</span>
                    <span class="text-muted" style="font-size:0.8rem;">CI: {{ $miembro->ci ?? 'Sin CI' }}</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">Informacion Institucional</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-semibold text-muted" style="width:40%;">Iglesia Local:</td>
                        <td>{{ $miembro->iglesia->nombre ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Circuito:</td>
                        <td>{{ $miembro->iglesia->circuito->nombre ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Categoria:</td>
                        <td>{{ $miembro->categoria_display }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Fecha de Ingreso:</td>
                        <td>{{ $miembro->fecha_ingreso?->format('d/m/Y') ?? '--' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">Datos Personales y de Contacto</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-semibold text-muted" style="width:40%;">Genero:</td>
                        <td>{{ ucfirst($miembro->genero ?? '--') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Fecha Nacimiento:</td>
                        <td>{{ $miembro->fecha_nacimiento?->format('d/m/Y') ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Telefono:</td>
                        <td>{{ $miembro->telefono ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Correo:</td>
                        <td>{{ $miembro->email ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Direccion:</td>
                        <td>{{ $miembro->direccion ?? '--' }}</td>
                    </tr>
                </table>
            </div>

            @if($miembro->observaciones)
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-2">Observaciones Pastorales</h6>
                <div class="p-3 bg-light rounded" style="font-size:0.85rem; color:#475569;">
                    {{ $miembro->observaciones }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
