@extends('layouts.app')
@section('title', 'Nueva comunicación')
@section('page_title', 'Nueva comunicación interna')
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-send-fill text-primary"></i> Destino y contenido</div>
    <div class="card-body">
        <form method="POST" action="{{ route('comunicaciones.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="nivel" class="form-label fw-semibold">Nivel destinatario</label>
                    @if(Auth::user()->isLocal())
                        <input type="hidden" name="nivel" value="iglesia">
                        <input class="form-control" value="Mi iglesia" disabled>
                    @else
                        <select id="nivel" name="nivel" class="form-select" required>
                            <option value="iglesia" @selected(old('nivel') === 'iglesia')>Iglesia</option>
                            <option value="circuito" @selected(old('nivel') === 'circuito')>Circuito</option>
                            @if(Auth::user()->hasAccesoDistrito())
                                <option value="distrito" @selected(old('nivel') === 'distrito')>Todo el distrito</option>
                            @endif
                        </select>
                    @endif
                </div>
                <div class="col-md-4" id="destinoIglesia">
                    <label for="iglesia_id" class="form-label fw-semibold">Iglesia</label>
                    <select id="iglesia_id" name="iglesia_id" class="form-select">
                        <option value="">Seleccione...</option>
                        @foreach($iglesias as $iglesia)
                            <option value="{{ $iglesia->id }}" @selected((string) old('iglesia_id') === (string) $iglesia->id)>{{ $iglesia->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4" id="destinoCircuito">
                    <label for="circuito_id" class="form-label fw-semibold">Circuito</label>
                    <select id="circuito_id" name="circuito_id" class="form-select">
                        <option value="">Seleccione...</option>
                        @foreach($circuitos as $circuito)
                            <option value="{{ $circuito->id }}" @selected((string) old('circuito_id') === (string) $circuito->id)>{{ $circuito->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tipo" class="form-label fw-semibold">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select" required>
                        @foreach(['circular' => 'Circular', 'aviso' => 'Aviso', 'informe' => 'Informe', 'solicitud' => 'Solicitud', 'respuesta' => 'Respuesta'] as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="prioridad" class="form-label fw-semibold">Prioridad</label>
                    <select id="prioridad" name="prioridad" class="form-select" required>
                        <option value="normal" @selected(old('prioridad', 'normal') === 'normal')>Normal</option>
                        <option value="urgente" @selected(old('prioridad') === 'urgente')>Urgente</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="titulo" class="form-label fw-semibold">Asunto</label>
                    <input id="titulo" name="titulo" class="form-control" maxlength="255" required value="{{ old('titulo') }}">
                </div>
                <div class="col-12">
                    <label for="contenido" class="form-label fw-semibold">Mensaje</label>
                    <textarea id="contenido" name="contenido" rows="7" class="form-control" maxlength="5000" required>{{ old('contenido') }}</textarea>
                    <div class="form-text">Este módulo es sólo para comunicación interna del sistema; no realiza envíos masivos externos.</div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('comunicaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-sigem"><i class="bi bi-send me-1"></i> Enviar comunicación</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nivel = document.getElementById('nivel');
    const iglesia = document.getElementById('destinoIglesia');
    const circuito = document.getElementById('destinoCircuito');
    const actualizar = () => {
        const valor = nivel ? nivel.value : 'iglesia';
        iglesia.classList.toggle('d-none', valor !== 'iglesia');
        circuito.classList.toggle('d-none', valor !== 'circuito');
    };
    nivel?.addEventListener('change', actualizar);
    actualizar();
});
</script>
@endpush
