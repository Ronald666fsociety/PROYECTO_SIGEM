@extends('layouts.app')

@section('title', $iglesia->nombre)
@section('page_title', $iglesia->nombre)

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('iglesias.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Iglesias
    </a>
</div>

<!-- Header Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width:60px; height:60px; border-radius:14px; background:linear-gradient(135deg, #1a3a5c, #2a5a8c); display:flex; align-items:center; justify-content:center; color:#fff;">
                <i class="bi bi-building" style="font-size:1.6rem;"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-1">{{ $iglesia->nombre }}</h4>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge-status badge-{{ $iglesia->estado }}">{{ ucfirst($iglesia->estado) }}</span>
                    <span class="badge" style="background:#e0f2fe; color:#0369a1;">{{ $iglesia->circuito->nombre ?? '--' }}</span>
                    <span class="text-muted" style="font-size:0.82rem;">Codigo: {{ $iglesia->codigo }}</span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="p-3 border rounded text-center">
                    <div class="fw-bold text-primary" style="font-size:1.5rem;">{{ $miembrosActivos }}</div>
                    <div style="font-size:0.75rem; color:#64748b;">Membresia Activa</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 border rounded text-center">
                    <div class="fw-bold text-dark" style="font-size:1.15rem;">{{ $iglesia->pastor_nombre ?? 'Por designar' }}</div>
                    <div style="font-size:0.75rem; color:#64748b;">
                        Pastor Asignado
                        @if(Auth::user()?->hasAccesoDistrito())
                            &bull; <a href="{{ route('usuarios.create') }}" class="text-primary text-decoration-none" title="Designar o reasignar pastor">Cambiar</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 border rounded text-center">
                    <div class="fw-bold text-dark" style="font-size:1.2rem;">{{ $iglesia->localidad ?? 'Kollasuyo' }}</div>
                    <div style="font-size:0.75rem; color:#64748b;">Localidad / Municipio</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 border rounded text-center">
                    <div class="fw-bold text-dark" style="font-size:1.2rem;">{{ $iglesia->fecha_fundacion?->format('d/m/Y') ?? '1970' }}</div>
                    <div style="font-size:0.75rem; color:#64748b;">Fundacion</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Category Breakdown Table -->
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart-fill text-primary"></i>
                Composicion de la Membresia
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th class="text-end">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($miembrosPorCategoria as $cat)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $cat->categoria)) }}</td>
                            <td class="text-end fw-bold">{{ $cat->total }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Sin datos registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Counts Chart -->
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-graph-up text-primary"></i>
                Evolucion Ultimos 12 Meses
            </div>
            <div class="card-body">
                <div style="height: 250px;">
                    <canvas id="chartIglesia"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartIglesia').getContext('2d');
    const labels = [
        @foreach($conteos as $c)
            "{{ $c->periodo }}",
        @endforeach
    ];
    const data = [
        @foreach($conteos as $c)
            {{ $c->total_activos }},
        @endforeach
    ];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Membresia Activa',
                data: data,
                borderColor: '#1a3a5c',
                backgroundColor: 'rgba(26, 58, 92, 0.08)',
                borderWidth: 2,
                fill: true,
                pointRadius: 3,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 10 } } },
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Inter', size: 10 } } }
            }
        }
    });
});
</script>
@endpush
