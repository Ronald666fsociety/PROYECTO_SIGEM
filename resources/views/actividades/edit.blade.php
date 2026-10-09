@extends('layouts.app')
@section('title', 'Editar actividad')
@section('page_title', 'Editar actividad')
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-pencil-square text-primary"></i> {{ $actividad->titulo }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('actividades.update', $actividad) }}">
            @csrf
            @method('PUT')
            @include('actividades._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('actividades.show', $actividad) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-sigem"><i class="bi bi-check-lg me-1"></i> Actualizar actividad</button>
            </div>
        </form>
    </div>
</div>
@endsection
