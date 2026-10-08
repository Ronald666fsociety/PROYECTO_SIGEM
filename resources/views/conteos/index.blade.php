@extends('layouts.app')

@section('title', 'Conteos Mensuales')
@section('page_title', 'Conteos Mensuales y Serie Histórica')

@section('content')
<!-- Header Stats & Actions -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card {{ $compuertaAprobada ? 'kpi-primary' : 'kpi-accent' }} h-100">
            <div class="kpi-icon"><i class="bi bi-calendar-range"></i></div>
            <div class="kpi-value">{{ $totalMesesDistritales }}</div>
            <div class="kpi-label">Meses Cerrados en Serie Distrital</div>
            <div class="kpi-trend">
                @if($compuertaAprobada)
                    <i class="bi bi-shield-check"></i>
                    <span>Serie real apta: continua y con 24 iglesias</span>
                @elseif($resumenCompuerta['aprobada'] && $resumenCompuerta['contiene_sinteticos'])
                    <i class="bi bi-beaker"></i>
                    <span>Serie sintética: solo prueba funcional</span>
                @else
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Calidad pendiente: meses, continuidad o cobertura</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kpi-card kpi-info h-100">
            <div class="kpi-icon"><i class="bi bi-clipboard-data-fill"></i></div>
            <div class="kpi-value">{{ number_format($conteos->total()) }}</div>
            <div class="kpi-label">Total Registros Iglesia-Mes</div>
            <div class="kpi-trend">
                <i class="bi bi-diagram-3-fill"></i>
                <span>24 iglesias en 4 circuitos activos</span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 mb-0 shadow-sm d-flex flex-column justify-content-between p-3" style="background:#fff; border:1px solid var(--sigem-border);">
            <div class="fw-bold text-muted text-uppercase mb-2" style="font-size:0.75rem; letter-spacing:0.5px;">
                <i class="bi bi-gear-fill me-1 text-primary"></i> Acciones de Gestión de Datos
            </div>
            <div class="d-flex flex-column gap-2">
                @if(auth()->user()->hasAccesoDistrito())
                <a href="{{ route('conteos.importar') }}" class="btn btn-sigem-outline btn-sm d-flex align-items-center justify-content-center gap-2 py-2">
                    <i class="bi bi-cloud-arrow-up-fill text-primary"></i>
                    <span>Cargar Datos Históricos (CSV / Reemplazar)</span>
                </a>
                @endif
                <a href="{{ route('conteos.create') }}" class="btn btn-sigem btn-sm d-flex align-items-center justify-content-center gap-2 py-2">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Registrar Conteo Mensual Manual</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('conteos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Año</label>
                <select name="anio" class="form-select form-select-sm">
                    <option value="">Todos los años</option>
                    @foreach($anios as $a)
                        <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Mes</label>
                <select name="mes" class="form-select form-select-sm">
                    <option value="">Todos los meses</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('mes') == $m ? 'selected' : '' }}>Mes {{ $m }}</option>
                    @endfor
                </select>
            </div>
            @if(!auth()->user()->isLocal())
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Iglesia Local</label>
                <select name="iglesia_id" class="form-select form-select-sm">
                    <option value="">Todas las iglesias</option>
                    @foreach($iglesias as $ig)
                        <option value="{{ $ig->id }}" {{ request('iglesia_id') == $ig->id ? 'selected' : '' }}>
                            {{ $ig->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos los estados</option>
                    <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado (Válido Holt)</option>
                    <option value="validado" {{ request('estado') == 'validado' ? 'selected' : '' }}>Validado</option>
                    <option value="borrador" {{ request('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sigem btn-sm w-100">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-clipboard-data-fill text-primary"></i>
            Listado de Conteos Mensuales ({{ $conteos->total() }} registros)
        </span>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasAccesoDistrito())
            <a href="{{ route('prediccion.index') }}" class="btn btn-sm btn-sigem-outline">
                <i class="bi bi-graph-up-arrow me-1"></i> Ir a Modelo Predictivo Holt
            </a>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Fecha Corte</th>
                        <th>Iglesia</th>
                        <th>Circuito</th>
                        <th class="text-end">Membresía Activa</th>
                        <th class="text-center">Inactivos</th>
                        <th class="text-center">Nuevos</th>
                        <th class="text-center">Bajas</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conteos as $c)
                    <tr>
                        <td class="fw-bold">{{ $c->periodo }}</td>
                        <td class="text-muted" style="font-size:0.8rem;">{{ $c->fecha_corte ? $c->fecha_corte->format('d/m/Y') : '--' }}</td>
                        <td>{{ $c->iglesia->nombre ?? '--' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $c->iglesia->circuito->nombre ?? '--' }}</span></td>
                        <td class="fw-bold text-end text-primary" style="font-size:1rem;">{{ number_format($c->total_activos) }}</td>
                        <td class="text-center text-muted">{{ $c->total_inactivos }}</td>
                        <td class="text-center"><span class="badge" style="background:#ecfdf5; color:#065f46;">+{{ $c->total_nuevos }}</span></td>
                        <td class="text-center"><span class="badge" style="background:#fef2f2; color:#991b1b;">-{{ $c->total_bajas }}</span></td>
                        <td class="text-center">
                            @if(in_array($c->estado, ['cerrado', 'validado']))
                                <span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;">
                                    <i class="bi bi-check-circle me-1"></i> {{ ucfirst($c->estado) }}
                                </span>
                            @else
                                <span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                                    {{ ucfirst($c->estado) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No se encontraron conteos registrados con los filtros aplicados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($conteos->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $conteos->links() }}
    </div>
    @endif
</div>
@endsection
