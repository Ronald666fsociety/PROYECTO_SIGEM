@php($actividad = $actividad ?? null)
@php($usuario = Auth::user())

<div class="row g-3">
    <div class="col-md-4">
        <label for="nivel" class="form-label fw-semibold">Nivel de la actividad</label>
        @if($usuario->isLocal())
            <input type="hidden" name="nivel" value="iglesia">
            <input class="form-control" value="Iglesia local" disabled>
        @else
            <select id="nivel" name="nivel" class="form-select" required>
                <option value="iglesia" @selected(old('nivel', $actividad?->nivel) === 'iglesia')>Iglesia</option>
                <option value="circuito" @selected(old('nivel', $actividad?->nivel) === 'circuito')>Circuito</option>
                @if($usuario->hasAccesoDistrito())
                    <option value="distrito" @selected(old('nivel', $actividad?->nivel) === 'distrito')>Distrito</option>
                @endif
            </select>
        @endif
    </div>
    <div class="col-md-4" id="campoIglesia">
        <label for="iglesia_id" class="form-label fw-semibold">Iglesia</label>
        <select id="iglesia_id" name="iglesia_id" class="form-select">
            <option value="">Seleccione...</option>
            @foreach($iglesias as $iglesia)
                <option value="{{ $iglesia->id }}" @selected((string) old('iglesia_id', $actividad?->iglesia_id) === (string) $iglesia->id)>{{ $iglesia->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4" id="campoCircuito">
        <label for="circuito_id" class="form-label fw-semibold">Circuito</label>
        <select id="circuito_id" name="circuito_id" class="form-select">
            <option value="">Seleccione...</option>
            @foreach($circuitos as $circuito)
                <option value="{{ $circuito->id }}" @selected((string) old('circuito_id', $actividad?->circuito_id) === (string) $circuito->id)>{{ $circuito->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8">
        <label for="titulo" class="form-label fw-semibold">Título</label>
        <input id="titulo" name="titulo" class="form-control" maxlength="255" required value="{{ old('titulo', $actividad?->titulo) }}">
    </div>
    <div class="col-md-4">
        <label for="tipo" class="form-label fw-semibold">Tipo</label>
        <select id="tipo" name="tipo" class="form-select" required>
            @foreach(['culto' => 'Culto', 'estudio_biblico' => 'Estudio bíblico', 'reunion_administrativa' => 'Reunión administrativa', 'evento_social' => 'Evento social', 'capacitacion' => 'Capacitación', 'mision' => 'Misión', 'otro' => 'Otro'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('tipo', $actividad?->tipo) === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" maxlength="2000">{{ old('descripcion', $actividad?->descripcion) }}</textarea>
    </div>
    <div class="col-md-3">
        <label for="fecha_inicio" class="form-label fw-semibold">Fecha de inicio</label>
        <input id="fecha_inicio" type="date" name="fecha_inicio" class="form-control" required value="{{ old('fecha_inicio', $actividad?->fecha_inicio?->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3">
        <label for="fecha_fin" class="form-label fw-semibold">Fecha final</label>
        <input id="fecha_fin" type="date" name="fecha_fin" class="form-control" value="{{ old('fecha_fin', $actividad?->fecha_fin?->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3">
        <label for="hora_inicio" class="form-label fw-semibold">Hora de inicio</label>
        <input id="hora_inicio" type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio', $actividad?->hora_inicio ? substr($actividad->hora_inicio, 0, 5) : '') }}">
    </div>
    <div class="col-md-3">
        <label for="hora_fin" class="form-label fw-semibold">Hora final</label>
        <input id="hora_fin" type="time" name="hora_fin" class="form-control" value="{{ old('hora_fin', $actividad?->hora_fin ? substr($actividad->hora_fin, 0, 5) : '') }}">
    </div>
    <div class="col-md-5">
        <label for="lugar" class="form-label fw-semibold">Lugar</label>
        <input id="lugar" name="lugar" class="form-control" maxlength="255" value="{{ old('lugar', $actividad?->lugar) }}">
    </div>
    <div class="col-md-3">
        <label for="asistentes" class="form-label fw-semibold">Asistentes</label>
        <input id="asistentes" type="number" min="0" name="asistentes" class="form-control" value="{{ old('asistentes', $actividad?->asistentes) }}">
    </div>
    <div class="col-md-4">
        <label for="estado" class="form-label fw-semibold">Estado</label>
        <select id="estado" name="estado" class="form-select" required>
            @foreach(['programada' => 'Programada', 'en_curso' => 'En curso', 'completada' => 'Completada', 'cancelada' => 'Cancelada'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('estado', $actividad?->estado ?? 'programada') === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nivel = document.getElementById('nivel');
    const campoIglesia = document.getElementById('campoIglesia');
    const campoCircuito = document.getElementById('campoCircuito');
    const actualizar = () => {
        const valor = nivel ? nivel.value : 'iglesia';
        campoIglesia.classList.toggle('d-none', valor !== 'iglesia');
        campoCircuito.classList.toggle('d-none', valor === 'distrito');
    };
    nivel?.addEventListener('change', actualizar);
    actualizar();
});
</script>
@endpush
