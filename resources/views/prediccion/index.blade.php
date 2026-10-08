@extends('layouts.app')

@section('title', 'Evaluación predictiva')
@section('page_title', 'Modelo predictivo exploratorio basado en Holt')

@section('content')
@php
    $contieneSinteticos = $serieHistorica->contains(fn ($corte) => (bool) $corte->contiene_sinteticos);
    $nombresMetodos = [
        'holt' => 'Holt lineal',
        'ultimo_valor' => 'Último valor observado',
        'suavizamiento_simple' => 'Suavizamiento exponencial simple',
        'regresion_lineal' => 'Regresión lineal temporal',
    ];
@endphp

<div class="card mb-4" style="border-left:4px solid var(--sigem-accent);">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3">
            <div class="d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#c49a2a,#dbb548);">
                <i class="bi bi-diagram-3-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-2">Protocolo temporal definido en el Proyecto de Grado</h6>
                <p class="mb-2 text-muted" style="font-size:.84rem;line-height:1.6;">
                    SIGEM agrega los cortes de las 24 iglesias en una sola serie mensual distrital. Con 36 meses, utiliza los primeros 30 para desarrollo y reserva los últimos 6 para comprobación final. Dentro del desarrollo aplica siete orígenes expansivos, desde 18 hasta 24 meses, y evalúa horizontes de 1 a 6 meses.
                </p>
                <p class="mb-0 text-muted" style="font-size:.84rem;line-height:1.6;">
                    Holt es el aporte predictivo obligatorio y se compara bajo las mismas condiciones con último valor, suavizamiento exponencial simple y regresión lineal temporal. El resultado se informa con MAE y RMSE; no se presupone que Holt será superior.
                </p>
            </div>
        </div>
    </div>
</div>

@if($contieneSinteticos)
<div class="alert alert-warning d-flex gap-2 align-items-start" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>
        <strong>Modo de prueba funcional.</strong> La serie contiene registros sintéticos. Puede comprobarse el funcionamiento del módulo, pero la precisión solo podrá validarse cuando se carguen datos históricos reales, continuos y completos.
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-play-circle-fill text-primary"></i> Ejecutar evaluación</div>
            <div class="card-body">
                <form method="POST" action="{{ route('prediccion.ejecutar') }}" id="formPrediccion">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="horizonte">Horizonte del pronóstico final</label>
                        <select name="horizonte" id="horizonte" class="form-select" required>
                            @for($mes = 1; $mes <= 6; $mes++)
                                <option value="{{ $mes }}" @selected($mes === 6)>{{ $mes }} {{ $mes === 1 ? 'mes' : 'meses' }}</option>
                            @endfor
                        </select>
                        <div class="form-text">El perfil delimita el horizonte máximo a seis meses.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Aporte integrado</label>
                        <input type="text" class="form-control" value="Holt lineal sin estacionalidad" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Serie disponible</label>
                        <input type="text" class="form-control" value="{{ $serieHistorica->count() }} totales mensuales distritales" readonly>
                        <div class="form-text">El motor verificará continuidad y cobertura de las 24 iglesias antes de evaluar.</div>
                    </div>
                    <button type="submit" class="btn btn-sigem w-100" id="btnEjecutar">
                        <i class="bi bi-play-fill me-1"></i> Evaluar y pronosticar
                    </button>
                </form>
            </div>
        </div>

        @if($ultimaPrediccion)
        <div class="card">
            <div class="card-header"><i class="bi bi-clipboard-data-fill text-primary"></i> Última evaluación</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tipo de datos</span>
                    <span class="badge {{ $ultimaPrediccion->modo_datos === 'prueba_funcional' ? 'bg-warning text-dark' : 'bg-success' }}">
                        {{ $ultimaPrediccion->modo_datos === 'prueba_funcional' ? 'Sintéticos / prueba' : 'Históricos reales' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Menor MAE final</span>
                    <strong>{{ $nombresMetodos[$ultimaPrediccion->mejor_metodo] ?? 'Sin determinar' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">MAE de Holt</span>
                    <strong>{{ number_format((float) $ultimaPrediccion->mae, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">RMSE de Holt</span>
                    <strong>{{ number_format((float) $ultimaPrediccion->rmse, 2) }}</strong>
                </div>
                <a href="{{ route('prediccion.resultado', $ultimaPrediccion) }}" class="btn btn-sigem-outline btn-sm w-100">
                    <i class="bi bi-eye-fill me-1"></i> Ver evaluación detallada
                </a>
            </div>
        </div>
        @endif
    </div>

    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up text-primary"></i> Serie mensual distrital de membresía activa</span>
                <span class="badge bg-light text-dark border">{{ $serieHistorica->count() }} meses</span>
            </div>
            <div class="card-body"><div style="height:380px;"><canvas id="chartHistorico"></canvas></div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clock-history text-primary"></i> Historial de evaluaciones</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Datos</th>
                        <th>Mejor MAE final</th>
                        <th>MAE Holt</th>
                        <th>RMSE Holt</th>
                        <th>Orígenes de desarrollo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($predicciones as $evaluacion)
                    <tr>
                        <td>{{ $evaluacion->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $evaluacion->modo_datos === 'prueba_funcional' ? 'Prueba sintética' : 'Históricos reales' }}</td>
                        <td>{{ $nombresMetodos[$evaluacion->mejor_metodo] ?? 'No registrado' }}</td>
                        <td>{{ number_format((float) $evaluacion->mae, 2) }}</td>
                        <td>{{ number_format((float) $evaluacion->rmse, 2) }}</td>
                        <td>{{ $evaluacion->ventanas_evaluadas }}</td>
                        <td><a href="{{ route('prediccion.resultado', $evaluacion) }}" class="btn btn-sigem-outline btn-sm"><i class="bi bi-eye-fill"></i> Ver</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Todavía no existen evaluaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const serie = {{ Illuminate\Support\Js::from($serieHistorica->map(fn ($corte) => [
        'periodo' => $corte->periodo,
        'total' => (float) $corte->total_activos,
    ])->values()) }};

    new Chart(document.getElementById('chartHistorico').getContext('2d'), {
        type: 'line',
        data: {
            labels: serie.map(corte => corte.periodo),
            datasets: [{
                label: 'Membresía activa registrada',
                data: serie.map(corte => corte.total),
                borderColor: '#1a3a5c',
                backgroundColor: 'rgba(26,58,92,.06)',
                borderWidth: 2,
                fill: true,
                pointRadius: 3,
                tension: .25,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 18, maxRotation: 45 } },
                y: { grid: { color: '#f1f5f9' } }
            }
        }
    });

    document.getElementById('formPrediccion')?.addEventListener('submit', function () {
        const boton = document.getElementById('btnEjecutar');
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Evaluando cuatro métodos...';
    });
});
</script>
@endpush
