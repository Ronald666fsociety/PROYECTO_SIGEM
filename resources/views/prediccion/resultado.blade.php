@extends('layouts.app')

@section('title', 'Resultado predictivo')
@section('page_title', 'Evaluación temporal y pronóstico Holt')

@section('content')
@php
    $validacion = $prediccion->resultados_validacion ?? [];
    $protocolo = $validacion['protocolo'] ?? [];
    $resumenFinal = collect($validacion['resumen_comprobacion_final'] ?? []);
    if ($resumenFinal->isEmpty()) {
        $resumenFinal = collect([
            ['metodo' => 'holt', 'nombre_metodo' => 'Holt lineal', 'mae' => $prediccion->mae, 'rmse' => $prediccion->rmse],
            ['metodo' => 'ultimo_valor', 'nombre_metodo' => 'Último valor observado', 'mae' => $prediccion->mae_linea_base, 'rmse' => $prediccion->rmse_linea_base],
            ['metodo' => 'suavizamiento_simple', 'nombre_metodo' => 'Suavizamiento exponencial simple', 'mae' => $prediccion->mae_suavizamiento_simple, 'rmse' => $prediccion->rmse_suavizamiento_simple],
            ['metodo' => 'regresion_lineal', 'nombre_metodo' => 'Regresión lineal temporal', 'mae' => $prediccion->mae_regresion_lineal, 'rmse' => $prediccion->rmse_regresion_lineal],
        ])->filter(fn ($fila) => $fila['mae'] !== null);
    }
    $esPrueba = $prediccion->modo_datos === 'prueba_funcional';
    $esViable = $prediccion->es_viable;
    $colorEstado = $esPrueba ? '#d97706' : ($esViable ? '#198754' : '#c2410c');
    $fondoEstado = $esPrueba ? '#fef3c7' : ($esViable ? '#dcfce7' : '#ffedd5');
    $iconoEstado = $esPrueba ? 'beaker-fill' : ($esViable ? 'check-circle-fill' : 'exclamation-triangle-fill');
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('prediccion.index') }}" class="btn btn-sigem-outline btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        <span class="text-muted" style="font-size:.85rem;">Evaluación del {{ $prediccion->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <button onclick="window.print()" class="btn btn-sigem-outline btn-sm"><i class="bi bi-printer me-1"></i> Imprimir</button>
</div>

<div class="card mb-4" style="border-left:4px solid {{ $colorEstado }};">
    <div class="card-body d-flex align-items-start gap-3">
        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;border-radius:12px;background:{{ $fondoEstado }};color:{{ $colorEstado }};">
            <i class="bi bi-{{ $iconoEstado }} fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-1">{{ $prediccion->estado_evaluacion_display }}</h5>
            <p class="text-muted mb-0" style="font-size:.85rem;">
                @if($esPrueba)
                    Resultado generado con datos sintéticos para verificar el funcionamiento de SIGEM. La precisión deberá evaluarse nuevamente con los históricos reales, continuos y autorizados de las 24 iglesias.
                @else
                    {{ $prediccion->observaciones }}
                @endif
            </p>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-bar-chart-fill text-primary"></i> Contraposición de métodos en los seis meses reservados</div>
    <div class="card-body">
        <p class="text-muted" style="font-size:.82rem;">
            Los cuatro métodos reciben la misma información y se comparan sobre los mismos horizontes. Un MAE menor representa menor error absoluto promedio; el RMSE complementa el análisis al penalizar con mayor fuerza los errores grandes.
        </p>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Método</th><th>Función en el estudio</th><th>MAE</th><th>RMSE</th><th>Resultado</th></tr></thead>
                <tbody>
                    @foreach($resumenFinal as $metodo)
                    <tr class="{{ $metodo['metodo'] === $prediccion->mejor_metodo ? 'table-success' : '' }}">
                        <td class="fw-semibold">{{ $metodo['nombre_metodo'] }}</td>
                        <td>
                            @if($metodo['metodo'] === 'holt')
                                Aporte predictivo integrado en SIGEM
                            @else
                                Método de comparación
                            @endif
                        </td>
                        <td>{{ number_format((float) $metodo['mae'], 2) }}</td>
                        <td>{{ number_format((float) $metodo['rmse'], 2) }}</td>
                        <td>
                            @if($metodo['metodo'] === $prediccion->mejor_metodo)
                                <span class="badge bg-success">Menor MAE final</span>
                            @else
                                <span class="text-muted">Comparado</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="alert alert-light border mt-3 mb-0" style="font-size:.8rem;">
            <strong>Interpretación:</strong> Holt continúa siendo el modelo implementado porque constituye el aporte definido en el perfil. La comparación aporta sustento empírico y evita afirmar anticipadamente que será el método más preciso.
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6"><div class="pred-metric"><div class="pred-metric-value text-primary">{{ number_format((float) $prediccion->mae, 2) }}</div><div class="pred-metric-label">MAE de Holt</div></div></div>
    <div class="col-md-3 col-6"><div class="pred-metric"><div class="pred-metric-value text-primary">{{ number_format((float) $prediccion->rmse, 2) }}</div><div class="pred-metric-label">RMSE de Holt</div></div></div>
    <div class="col-md-3 col-6"><div class="pred-metric"><div class="pred-metric-value text-primary">{{ $protocolo['numero_origenes_desarrollo'] ?? $prediccion->ventanas_evaluadas }}</div><div class="pred-metric-label">Orígenes expansivos</div></div></div>
    <div class="col-md-3 col-6"><div class="pred-metric"><div class="pred-metric-value text-primary">6</div><div class="pred-metric-label">Meses reservados</div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-graph-up-arrow text-primary"></i> Pronóstico exploratorio de membresía con Holt</span>
        <span class="badge bg-light text-dark border">Hasta {{ $prediccion->horizonte_meses }} meses</span>
    </div>
    <div class="card-body">
        <div style="height:380px;"><canvas id="chartForecast"></canvas></div>
        <p class="text-muted mt-2 mb-0" style="font-size:.76rem;">
            La banda mostrada es un intervalo exploratorio aproximado construido con el RMSE de la comprobación final. No se presenta como intervalo de confianza del 95 %.
        </p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-table text-primary"></i> Pronóstico final de Holt</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead><tr><th>Mes</th><th>Estimación</th><th>Límite aprox. inferior</th><th>Límite aprox. superior</th><th>Cambio absoluto</th><th>Cambio porcentual</th></tr></thead>
                        <tbody>
                            @foreach($prediccion->valores as $valor)
                            <tr>
                                <td class="fw-bold">{{ $valor->periodo }}</td>
                                <td class="fw-bold text-primary">{{ number_format((float) $valor->valor_predicho, 2) }}</td>
                                <td>{{ number_format((float) $valor->intervalo_inferior, 2) }}</td>
                                <td>{{ number_format((float) $valor->intervalo_superior, 2) }}</td>
                                <td>{{ (float) $valor->crecimiento_absoluto >= 0 ? '+' : '' }}{{ number_format((float) $valor->crecimiento_absoluto, 2) }}</td>
                                <td>
                                    @if($valor->crecimiento_porcentual !== null)
                                        {{ (float) $valor->crecimiento_porcentual >= 0 ? '+' : '' }}{{ number_format((float) $valor->crecimiento_porcentual, 2) }}%
                                    @else
                                        No calculable
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-calendar-range text-primary"></i> Trazabilidad del protocolo</div>
            <div class="card-body">
                <table class="table table-sm mb-3">
                    <tr><td class="text-muted">Unidad de análisis</td><td>Total mensual distrital</td></tr>
                    <tr><td class="text-muted">Meses disponibles</td><td>{{ $protocolo['meses_totales'] ?? $prediccion->meses_entrenamiento }}</td></tr>
                    <tr><td class="text-muted">Desarrollo</td><td>{{ $protocolo['meses_desarrollo'] ?? 'No registrado' }} meses</td></tr>
                    <tr><td class="text-muted">Comprobación final</td><td>{{ $protocolo['meses_comprobacion_final'] ?? 6 }} meses</td></tr>
                    <tr><td class="text-muted">Horizontes evaluados</td><td>1 a 6 meses</td></tr>
                    <tr><td class="text-muted">Corte de datos</td><td>{{ $prediccion->fecha_corte_datos?->format('m/Y') ?? 'No registrado' }}</td></tr>
                    <tr><td class="text-muted">Nivel α</td><td>{{ $prediccion->alpha ?? 'Optimizado' }}</td></tr>
                    <tr><td class="text-muted">Tendencia β</td><td>{{ $prediccion->beta ?? 'Optimizado' }}</td></tr>
                </table>
                <div class="p-3 bg-light rounded text-muted" style="font-size:.76rem;">
                    Los parámetros se ajustan dentro de cada ventana de entrenamiento. Los seis meses finales no se utilizan para seleccionar ni ajustar el protocolo.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const historico = {{ Illuminate\Support\Js::from($serieHistorica->take(-12)->map(fn ($corte) => [
        'periodo' => $corte->periodo,
        'valor' => (float) $corte->total_activos,
    ])->values()) }};
    const pronostico = {{ Illuminate\Support\Js::from($prediccion->valores->map(fn ($valor) => [
        'periodo' => $valor->periodo,
        'valor' => (float) $valor->valor_predicho,
        'inferior' => (float) $valor->intervalo_inferior,
        'superior' => (float) $valor->intervalo_superior,
    ])->values()) }};
    const etiquetas = historico.map(punto => punto.periodo).concat(pronostico.map(punto => punto.periodo));
    const reales = historico.map(punto => punto.valor).concat(pronostico.map(() => null));
    const vaciosHistoricos = historico.map(() => null);
    const ultimoReal = historico.length ? historico[historico.length - 1].valor : null;
    const conectar = vaciosHistoricos.slice();
    const superior = vaciosHistoricos.slice();
    const inferior = vaciosHistoricos.slice();
    if (historico.length) {
        conectar[historico.length - 1] = ultimoReal;
        superior[historico.length - 1] = ultimoReal;
        inferior[historico.length - 1] = ultimoReal;
    }
    pronostico.forEach(punto => {
        conectar.push(punto.valor);
        superior.push(punto.superior);
        inferior.push(punto.inferior);
    });

    new Chart(document.getElementById('chartForecast').getContext('2d'), {
        type: 'line',
        data: {
            labels: etiquetas,
            datasets: [
                { label: 'Serie registrada', data: reales, borderColor: '#1a3a5c', backgroundColor: '#1a3a5c', borderWidth: 2.5, pointRadius: 3 },
                { label: 'Pronóstico Holt', data: conectar, borderColor: '#c49a2a', backgroundColor: '#c49a2a', borderWidth: 2.5, borderDash: [5, 5], pointRadius: 4 },
                { label: 'Límite aproximado superior', data: superior, borderColor: 'rgba(196,154,42,.35)', backgroundColor: 'rgba(196,154,42,.12)', borderWidth: 1, pointRadius: 0, fill: '+1' },
                { label: 'Límite aproximado inferior', data: inferior, borderColor: 'rgba(196,154,42,.35)', backgroundColor: 'transparent', borderWidth: 1, pointRadius: 0 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' } } }
        }
    });
});
</script>
@endpush
