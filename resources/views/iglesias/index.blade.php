@extends('layouts.app')

@section('title', 'Iglesias y Circuitos')
@section('page_title', 'Padron Distrital de Iglesias y Circuitos')

@section('content')
<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('iglesias.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold" style="font-size:0.8rem;">Filtrar por Circuito</label>
                <select name="circuito_id" class="form-select form-select-sm">
                    <option value="">Todos los circuitos (4)</option>
                    @foreach($circuitos as $c)
                        <option value="{{ $c->id }}" {{ request('circuito_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sigem btn-sm w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Churches Grid -->
<div class="row g-3">
    @foreach($iglesias as $ig)
    <div class="col-xl-4 col-md-6">
        <div class="card h-100" style="transition: transform 0.2s, box-shadow 0.2s; cursor:pointer;" onclick="window.location='{{ route('iglesias.show', $ig) }}'">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div style="width:46px; height:46px; border-radius:12px; background:linear-gradient(135deg, #1a3a5c, #2a5a8c); display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0;">
                        <i class="bi bi-building" style="font-size:1.25rem;"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="fw-bold mb-1 text-truncate" style="font-size:0.92rem;">{{ $ig->nombre }}</h6>
                        <div style="font-size:0.75rem; color:#64748b;">
                            <i class="bi bi-diagram-3 me-1"></i> {{ $ig->circuito->nombre ?? '--' }}
                        </div>
                    </div>
                </div>

                <div class="row g-2 text-center py-2 mb-3 bg-light rounded" style="font-size:0.78rem;">
                    <div class="col-6 border-end">
                        <div class="fw-bold text-primary" style="font-size:1.1rem;">{{ $ig->miembros_activos_count }}</div>
                        <div class="text-muted">Miembros Activos</div>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold text-dark" style="font-size:1.1rem;">{{ $ig->codigo }}</div>
                        <div class="text-muted">Codigo Institucional</div>
                    </div>
                </div>

                <div style="font-size:0.8rem; color:#64748b;" class="mb-1">
                    <i class="bi bi-person me-1"></i> Pastor: <strong>{{ $ig->pastor_nombre ?? 'Por designar' }}</strong>
                </div>
                <div style="font-size:0.8rem; color:#64748b;">
                    <i class="bi bi-geo-alt me-1"></i> Localidad: {{ $ig->localidad ?? 'Kollasuyo' }}
                </div>
            </div>
            <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center pt-0 pb-3">
                <span class="badge-status badge-{{ $ig->estado }}">{{ ucfirst($ig->estado) }}</span>
                <span class="btn btn-sm btn-link text-primary p-0 text-decoration-none" style="font-size:0.8rem;">
                    Ver ficha <i class="bi bi-arrow-right"></i>
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($iglesias->hasPages())
<div class="mt-4">
    {{ $iglesias->links() }}
</div>
@endif
@endsection
