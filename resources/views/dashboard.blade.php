@extends('layouts.app')

@section('title', 'Panel Principal')
@section('page_title')
    @if(Auth::user()->isLocal())
        Panel Pastoral &bull; {{ Auth::user()->iglesia->nombre ?? 'Mi Iglesia' }}
    @elseif(Auth::user()->isCircuito())
        Coordinación del Circuito &bull; {{ Auth::user()->circuito->nombre ?? 'Mi Circuito' }}
    @else
        Panel de Control Distrital
    @endif
@endsection

@section('content')
<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-primary">
            <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-value">{{ number_format($totalMembresiaActual) }}</div>
            <div class="kpi-label">
                @if(Auth::user()->isLocal())
                    Membresía Activa Local
                @elseif(Auth::user()->isCircuito())
                    Membresía Activa del Circuito
                @else
                    Membresía Activa Distrital
                @endif
            </div>
            <div class="kpi-trend">
                <i class="bi bi-{{ $tendencia >= 0 ? 'arrow-up-short' : 'arrow-down-short' }}"></i>
                <span>{{ abs($tendencia) }}% vs mes anterior ({{ $periodoActual }})</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-success">
            <div class="kpi-icon"><i class="bi bi-building"></i></div>
            <div class="kpi-value">{{ $totalIglesias }}</div>
            <div class="kpi-label">
                @if(Auth::user()->isLocal())
                    Mi Iglesia Asignada
                @elseif(Auth::user()->isCircuito())
                    Iglesias del Circuito
                @else
                    Iglesias Activas
                @endif
            </div>
            <div class="kpi-trend">
                <i class="bi bi-diagram-3-fill"></i>
                <span>
                    @if(Auth::user()->isLocal())
                        {{ Auth::user()->circuito->nombre ?? 'Circuito' }}
                    @elseif(Auth::user()->isCircuito())
                        {{ $totalCircuitos }} Circuito asignado
                    @else
                        {{ $totalCircuitos }} Circuitos consolidados
                    @endif
                </span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card kpi-info">
            <div class="kpi-icon"><i class="bi bi-person-plus-fill"></i></div>
            <div class="kpi-value">{{ $nuevosEsteAnio }}</div>
            <div class="kpi-label">Nuevos Miembros {{ date('Y') }}</div>
            <div class="kpi-trend">
                <i class="bi bi-person-dash-fill"></i>
                <span>{{ $bajasEsteAnio }} bajas registradas</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        @if(Auth::user()->hasAccesoDistrito())
        <div class="kpi-card kpi-accent">
            <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="kpi-value">
                @if($ultimaPrediccion)
                    @if($ultimaPrediccion->modo_datos === 'prueba_funcional')
                        <span style="font-size:1.15rem;">PRUEBA</span>
                    @elseif($ultimaPrediccion->es_viable)
                        <span style="font-size:1.15rem;">FAVORABLE</span>
                    @else
                        <span style="font-size:1.15rem;">REVISAR</span>
                    @endif
                @else
                    --
                @endif
            </div>
            <div class="kpi-label">Modelo Predictivo Holt</div>
            <div class="kpi-trend">
                <i class="bi bi-shield-check"></i>
                <span>
                    @if($ultimaPrediccion)
                        {{ $ultimaPrediccion->estado_evaluacion_display }} &bull; MAE: {{ number_format($ultimaPrediccion->mae, 2) }}
                    @else
                        Pendiente de ejecución
                    @endif
                </span>
            </div>
        </div>
        @else
        <div class="kpi-card kpi-accent">
            <div class="kpi-icon"><i class="bi bi-clipboard-check"></i></div>
            <div class="kpi-value">{{ $serieReciente->count() }}</div>
            <div class="kpi-label">Cortes Mensuales Registrados</div>
            <div class="kpi-trend">
                <i class="bi bi-calendar-check"></i>
                <span>Último corte: {{ $periodoActual }}</span>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- Time Series Line Chart -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-graph-up text-primary"></i>
                    @if(Auth::user()->isLocal())
                        Evolución de Membresía Local (Últimos 12 Meses)
                    @elseif(Auth::user()->isCircuito())
                        Evolución de Membresía del Circuito (Últimos 12 Meses)
                    @else
                        Evolución Mensual de Membresía Distrital (Últimos 12 Meses)
                    @endif
                </span>
                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:0.75rem;">
                    {{ Auth::user()->isLocal() ? (Auth::user()->iglesia->nombre ?? 'Local') : (Auth::user()->isCircuito() ? (Auth::user()->circuito->nombre ?? 'Circuito') : 'Kollasuyo') }}
                </span>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="chartEvolucion"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Donut Chart -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart-fill text-primary"></i>
                Distribución por Categoría
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width: 230px; height: 230px;">
                    <canvas id="chartCategorias"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->hasAccesoDistrito())
<!-- Circuits & Model Status Row (Exclusivo Nivel Distrital) -->
<div class="row g-3 mb-4">
    <!-- Circuit Breakdown Bar Chart -->
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-bar-chart-fill text-primary"></i>
                Membresía Activa por Circuito
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="chartCircuitos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Model Status & Quick Actions -->
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-cpu-fill text-primary"></i>
                    Estado del Modelo Predictivo Holt
                </span>
                <a href="{{ route('prediccion.index') }}" class="btn btn-sigem btn-sm">
                    Ir al Modelo <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @if($ultimaPrediccion)
                @php
                    $esPruebaPredictiva = $ultimaPrediccion->modo_datos === 'prueba_funcional';
                    $esViablePredictiva = $ultimaPrediccion->es_viable;
                    $fondoPredictivo = $esPruebaPredictiva ? '#fffbeb' : ($esViablePredictiva ? '#f0fdf4' : '#fff7ed');
                    $bordePredictivo = $esPruebaPredictiva ? '#fde68a' : ($esViablePredictiva ? '#bbf7d0' : '#fed7aa');
                    $colorPredictivo = $esPruebaPredictiva ? '#92400e' : ($esViablePredictiva ? '#166534' : '#9a3412');
                    $iconoPredictivo = $esPruebaPredictiva ? 'beaker' : ($esViablePredictiva ? 'check-lg' : 'exclamation-triangle');
                @endphp
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background: {{ $fondoPredictivo }}; border: 1px solid {{ $bordePredictivo }};">
                    <div style="width:42px; height:42px; border-radius:10px; background:{{ $colorPredictivo }}; display:flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="bi bi-{{ $iconoPredictivo }}" style="font-size:1.4rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:{{ $colorPredictivo }};">{{ $ultimaPrediccion->estado_evaluacion_display }}</div>
                        <div style="font-size:0.78rem; color:{{ $colorPredictivo }};">
                            Ultimo ajuste: {{ $ultimaPrediccion->created_at->format('d/m/Y H:i') }} &bull; Horizonte: {{ $ultimaPrediccion->horizonte_meses }} meses
                        </div>
                    </div>
                </div>

                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fw-bold text-dark">{{ number_format($ultimaPrediccion->mae, 2) }}</div>
                            <div style="font-size:0.7rem; color:#64748b;">MAE Holt</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fw-bold text-dark">{{ number_format($ultimaPrediccion->rmse, 2) }}</div>
                            <div style="font-size:0.7rem; color:#64748b;">RMSE Holt</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <div class="fw-bold text-dark">{{ $ultimaPrediccion->ventanas_evaluadas }}</div>
                            <div style="font-size:0.7rem; color:#64748b;">Orígenes evaluados</div>
                        </div>
                    </div>
                </div>

                <div style="font-size:0.8rem; color:#64748b;" class="mb-3">
                    @if($esPruebaPredictiva)
                        Resultado calculado con datos sintéticos para comprobar el funcionamiento. No constituye evidencia de precisión predictiva real.
                    @else
                        {{ $ultimaPrediccion->observaciones }}
                    @endif
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-graph-up-arrow" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    <p class="mt-2 mb-3" style="font-size:0.85rem;">No se ha ejecutado el modelo Holt en esta sesion.</p>
                    <a href="{{ route('prediccion.index') }}" class="btn btn-sigem btn-sm">
                        <i class="bi bi-play-circle-fill me-1"></i> Ejecutar Pronostico Holt
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Upcoming Activities -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-calendar-event-fill text-primary"></i>
        Proximas Actividades Registradas en el Distrito
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Actividad</th>
                        <th>Nivel</th>
                        <th>Lugar</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actividades as $act)
                    <tr>
                        <td class="fw-semibold">{{ $act->fecha_inicio->format('d/m/Y') }}</td>
                        <td>{{ $act->titulo }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($act->nivel) }}</span></td>
                        <td>{{ $act->lugar ?? 'Por definir' }}</td>
                        <td><span class="badge-status badge-activo">{{ ucfirst($act->estado) }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No hay actividades proximas programadas.</td>
                    </tr>
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
    // 1. Line Chart: Monthly Evolution
    const ctxEvolucion = document.getElementById('chartEvolucion').getContext('2d');
    const serieLabels = [
        @foreach($serieReciente as $s)
            "{{ $s->periodo }}",
        @endforeach
    ];
    const serieData = [
        @foreach($serieReciente as $s)
            {{ $s->total_activos }},
        @endforeach
    ];

    new Chart(ctxEvolucion, {
        type: 'line',
        data: {
            labels: serieLabels,
            datasets: [{
                label: 'Membresia Activa Distrital',
                data: serieData,
                borderColor: '#1a3a5c',
                backgroundColor: 'rgba(26, 58, 92, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#c49a2a',
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0d1f33',
                    titleFont: { family: 'Inter', size: 12 },
                    bodyFont: { family: 'Inter', size: 13, weight: 'bold' },
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }
                }
            }
        }
    });

    // 2. Donut Chart: Categories
    const ctxCategorias = document.getElementById('chartCategorias').getContext('2d');
    new Chart(ctxCategorias, {
        type: 'doughnut',
        data: {
            labels: ['Plenos', 'Preparatorios', 'Simpatizantes', 'Niños/as'],
            datasets: [{
                data: [
                    {{ $porCategoria['miembro_pleno'] ?? 0 }},
                    {{ $porCategoria['miembro_preparatorio'] ?? 0 }},
                    {{ $porCategoria['simpatizante'] ?? 0 }},
                    {{ $porCategoria['nino'] ?? 0 }}
                ],
                backgroundColor: ['#1a3a5c', '#0f6faa', '#c49a2a', '#146c43'],
                borderWidth: 2,
                borderColor: '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { family: 'Inter', size: 11 }, padding: 12 }
                }
            },
            cutout: '65%'
        }
    });

    // 3. Bar Chart: Circuits
    const canvasCircuitos = document.getElementById('chartCircuitos');
    if (canvasCircuitos) {
        const ctxCircuitos = canvasCircuitos.getContext('2d');
        const circuitosLabels = [
            @foreach($porCircuito as $c)
                "{{ $c['nombre'] }}",
            @endforeach
        ];
        const circuitosData = [
            @foreach($porCircuito as $c)
                {{ $c['total'] }},
            @endforeach
        ];

        new Chart(ctxCircuitos, {
            type: 'bar',
            data: {
                labels: circuitosLabels,
                datasets: [{
                    label: 'Membresia Activa',
                    data: circuitosData,
                    backgroundColor: '#2a5a8c',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 10 }, color: '#64748b' }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }
                    }
                }
            }
        });
    }
});
</script>
@endpush
