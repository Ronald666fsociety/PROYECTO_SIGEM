@extends('layouts.app')
@section('title', 'Circuitos')
@section('page_title', 'Organización por circuitos')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><p class="text-muted mb-0">Estructura institucional del Distrito Kollasuyo.</p><a href="{{ route('circuitos.create') }}" class="btn btn-sigem btn-sm"><i class="bi bi-plus-lg me-1"></i> Nuevo circuito</a></div>
<div class="row g-3">
@forelse($circuitos as $circuito)
<div class="col-lg-6"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between"><div><small class="text-muted">{{ $circuito->codigo }}</small><h5 class="fw-bold">{{ $circuito->nombre }}</h5></div><span class="badge text-bg-{{ $circuito->estado === 'activo' ? 'success' : 'secondary' }} align-self-start">{{ ucfirst($circuito->estado) }}</span></div><p class="text-muted">{{ $circuito->descripcion ?: 'Sin descripción.' }}</p><div class="d-flex gap-4"><span><strong>{{ $circuito->iglesias_count }}</strong> iglesias</span><span><strong>{{ $circuito->usuarios_count }}</strong> usuarios</span></div></div><div class="card-footer bg-white text-end"><a href="{{ route('circuitos.edit', $circuito) }}" class="btn btn-sm btn-outline-primary">Editar</a></div></div></div>
@empty
<div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">No hay circuitos registrados.</div></div></div>
@endforelse
</div>
<div class="mt-3">{{ $circuitos->links() }}</div>
@endsection
