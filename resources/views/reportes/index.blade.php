@extends('layouts.app')
@section('title', 'Reportes e indicadores')
@section('page_title', 'Reportes e indicadores de membresía')
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-lg-2"><label for="anio" class="form-label fw-semibold">Gestión</label><select id="anio" name="anio" class="form-select">@for($gestion = (int) date('Y'); $gestion >= 2023; $gestion--)<option value="{{ $gestion }}" @selected($anio === $gestion)>{{ $gestion }}</option>@endfor</select></div>
            <div class="col-sm-4 col-lg-2"><button class="btn btn-sigem w-100"><i class="bi bi-funnel me-1"></i> Consultar</button></div>
            <div class="col-sm-4 col-lg-3"><a href="{{ route('reportes.exportar', ['anio' => $anio]) }}" class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV verificable</a></div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Último mes con registro</small><div class="fs-3 fw-bold text-primary">{{ $ultimoMes ? str_pad($ultimoMes, 2, '0', STR_PAD_LEFT).'/'.$anio : 'Sin datos' }}</div></div></div></div>
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Membresía activa consolidada</small><div class="fs-3 fw-bold">{{ number_format($totalActual) }}</div></div></div></div>
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Iglesias con dato en el corte</small><div class="fs-3 fw-bold">{{ $iglesiasConDato }}</div></div></div></div>
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Cobertura del corte</small><div class="fs-3 fw-bold {{ $cobertura === 100.0 ? 'text-success' : 'text-warning' }}">{{ number_format($cobertura, 1) }}%</div></div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8"><div class="card h-100"><div class="card-header"><i class="bi bi-graph-up text-primary"></i> Serie mensual consolidada</div><div class="card-body"><div style="height:300px"><canvas id="serieAnual"></canvas></div></div></div></div>
    <div class="col-xl-4"><div class="card h-100"><div class="card-header"><i class="bi bi-shield-check text-primary"></i> Procedencia de los registros</div><div class="card-body">@forelse($fuentes as $fuente => $total)<div class="d-flex justify-content-between border-bottom py-2"><span>{{ ucfirst(str_replace('_', ' ', $fuente)) }}</span><strong>{{ $total }}</strong></div>@empty<p class="text-muted">No existen registros para la gestión.</p>@endforelse<div class="alert alert-info small mt-3 mb-0">Los datos sintéticos sirven para pruebas funcionales; no validan la precisión académica de Holt.</div></div></div></div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-table text-primary"></i> Detalle del último corte disponible</div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Iglesia</th><th>Circuito</th><th class="text-end">Activos</th><th>Estado</th><th>Fuente</th></tr></thead><tbody>
        @forelse($detalle as $fila)
        <tr><td><strong>{{ $fila['iglesia']->nombre }}</strong><br><small class="text-muted">{{ $fila['iglesia']->codigo }}</small></td><td>{{ $fila['iglesia']->circuito?->nombre }}</td><td class="text-end fw-bold">{{ $fila['conteo']?->total_activos ?? '—' }}</td><td>{{ $fila['conteo'] ? ucfirst($fila['conteo']->estado) : 'Sin registro' }}</td><td>{{ $fila['conteo']?->fuente_datos ? ucfirst(str_replace('_', ' ', $fila['conteo']->fuente_datos)) : '—' }}</td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-4">No hay iglesias disponibles para este usuario.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    new Chart(document.getElementById('serieAnual'), {
        type: 'line',
        data: {
            labels: @json($etiquetasSerie),
            datasets: [{ label: 'Membresía activa', data: @json($valoresSerie), borderColor: '#1a3a5c', backgroundColor: 'rgba(26,58,92,.1)', fill: true, tension: .25 }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endpush
