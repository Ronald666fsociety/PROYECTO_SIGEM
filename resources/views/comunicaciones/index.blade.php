@extends('layouts.app')
@section('title', 'Comunicación interna')
@section('page_title', 'Comunicación interna')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Circulares, avisos, informes y solicitudes dentro de SIGEM.</p>
    <a href="{{ route('comunicaciones.create') }}" class="btn btn-sigem btn-sm"><i class="bi bi-pencil-square me-1"></i> Redactar</a>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-inbox-fill text-primary"></i> Recibidas</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Fecha</th><th>Remitente</th><th>Asunto</th><th>Destino</th><th>Prioridad</th></tr></thead>
            <tbody>
                @forelse($recibidas as $mensaje)
                <tr class="{{ $mensaje->leida_por_usuario ? '' : 'table-primary' }}" role="button" onclick="window.location='{{ route('comunicaciones.show', $mensaje) }}'">
                    <td class="text-nowrap">{{ $mensaje->fecha_envio->format('d/m/Y H:i') }}</td>
                    <td>{{ $mensaje->remitente->name }}</td>
                    <td><strong>{{ $mensaje->titulo }}</strong><br><small class="text-muted">{{ ucfirst($mensaje->tipo) }}</small></td>
                    <td>{{ $mensaje->iglesia?->nombre ?? $mensaje->circuito?->nombre ?? 'Distrito' }}</td>
                    <td><span class="badge text-bg-{{ $mensaje->prioridad === 'urgente' ? 'danger' : 'secondary' }}">{{ ucfirst($mensaje->prioridad) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No tiene comunicaciones recibidas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recibidas->hasPages())<div class="card-footer">{{ $recibidas->links() }}</div>@endif
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-send-check-fill text-primary"></i> Enviadas</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Fecha</th><th>Asunto</th><th>Destino</th><th>Destinatarios</th><th></th></tr></thead>
            <tbody>
                @forelse($enviadas as $mensaje)
                <tr>
                    <td class="text-nowrap">{{ $mensaje->fecha_envio->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $mensaje->titulo }}</strong><br><small class="text-muted">{{ ucfirst($mensaje->tipo) }}</small></td>
                    <td>{{ $mensaje->iglesia?->nombre ?? $mensaje->circuito?->nombre ?? 'Distrito' }}</td>
                    <td>{{ $mensaje->destinatarios_count }}</td>
                    <td class="text-end"><a href="{{ route('comunicaciones.show', $mensaje) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No ha enviado comunicaciones.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($enviadas->hasPages())<div class="card-footer">{{ $enviadas->links() }}</div>@endif
</div>
@endsection
