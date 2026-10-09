@extends('layouts.app')
@section('title', $actividad->titulo)
@section('page_title', 'Detalle de actividad')
@section('content')
@php
    $usuario = Auth::user();
    $puedeEditar = $usuario->hasAccesoDistrito()
        || ($usuario->isCircuito() && $actividad->nivel !== 'distrito' && (int) $actividad->circuito_id === (int) $usuario->circuito_id)
        || ($usuario->isLocal() && $actividad->nivel === 'iglesia' && (int) $actividad->iglesia_id === (int) $usuario->iglesia_id);
@endphp
<div class="d-flex justify-content-between gap-2 mb-4">
    <a href="{{ route('actividades.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    @if($puedeEditar)
        <a href="{{ route('actividades.edit', $actividad) }}" class="btn btn-sigem btn-sm"><i class="bi bi-pencil me-1"></i> Editar</a>
    @endif
</div>
<div class="card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div><div class="text-uppercase text-muted small">{{ ucfirst($actividad->nivel) }}</div><h3 class="fw-bold mb-1">{{ $actividad->titulo }}</h3></div>
            <span class="badge text-bg-primary">{{ ucfirst(str_replace('_', ' ', $actividad->estado)) }}</span>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Fecha</small><div class="fw-semibold">{{ $actividad->fecha_inicio->format('d/m/Y') }}</div></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Horario</small><div class="fw-semibold">{{ $actividad->hora_inicio ? substr($actividad->hora_inicio, 0, 5) : 'Sin definir' }}</div></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Ámbito</small><div class="fw-semibold">{{ $actividad->iglesia?->nombre ?? $actividad->circuito?->nombre ?? 'Distrito Kollasuyo' }}</div></div></div>
            <div class="col-md-3"><div class="border rounded p-3"><small class="text-muted">Asistentes</small><div class="fw-semibold">{{ $actividad->asistentes ?? 'Por registrar' }}</div></div></div>
        </div>
        <h6 class="fw-bold">Descripción</h6>
        <p class="text-muted mb-0">{{ $actividad->descripcion ?: 'Sin descripción adicional.' }}</p>
    </div>
</div>
@endsection
