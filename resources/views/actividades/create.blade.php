@extends('layouts.app')
@section('title', 'Nueva actividad')
@section('page_title', 'Registrar actividad')
@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-calendar-plus text-primary"></i> Información de la actividad</div>
    <div class="card-body">
        <form method="POST" action="{{ route('actividades.store') }}">
            @csrf
            @include('actividades._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('actividades.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-sigem"><i class="bi bi-check-lg me-1"></i> Guardar actividad</button>
            </div>
        </form>
    </div>
</div>
@endsection
