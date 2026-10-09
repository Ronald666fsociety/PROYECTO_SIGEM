@extends('layouts.app')
@section('title', $comunicacion->titulo)
@section('page_title', 'Detalle de comunicación')
@section('content')
<div class="mb-4"><a href="{{ route('comunicaciones.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a></div>
<div class="card">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between gap-3 mb-3">
            <div>
                <div class="text-muted small">{{ ucfirst($comunicacion->tipo) }} · {{ $comunicacion->fecha_envio->format('d/m/Y H:i') }}</div>
                <h3 class="fw-bold mb-1">{{ $comunicacion->titulo }}</h3>
                <div class="text-muted">De: {{ $comunicacion->remitente->name }} · Para: {{ $comunicacion->iglesia?->nombre ?? $comunicacion->circuito?->nombre ?? 'Distrito Kollasuyo' }}</div>
            </div>
            <span class="badge align-self-start text-bg-{{ $comunicacion->prioridad === 'urgente' ? 'danger' : 'secondary' }}">{{ ucfirst($comunicacion->prioridad) }}</span>
        </div>
        <hr>
        <div style="white-space: pre-line; line-height: 1.7;">{{ $comunicacion->contenido }}</div>
        @if((int) $comunicacion->remitente_id === (int) Auth::id())
            <hr>
            <small class="text-muted">Destinatarios registrados: {{ $comunicacion->destinatarios->count() }} · Leídos: {{ $comunicacion->destinatarios->where('leido', true)->count() }}</small>
        @endif
    </div>
</div>
@endsection
