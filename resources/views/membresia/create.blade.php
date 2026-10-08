@extends('layouts.app')

@section('title', 'Nuevo Miembro')
@section('page_title', 'Registrar Nuevo Miembro')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('membresia.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al Listado
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-person-plus-fill text-primary"></i>
        Formulario de Registro de Membresia
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger py-2 mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('membresia.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Nombres *</label>
                    <input type="text" name="nombres" class="form-control" value="{{ old('nombres') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Cedula de Identidad (CI)</label>
                    <input type="text" name="ci" class="form-control" value="{{ old('ci') }}" placeholder="Ej. 1234567-LP">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Iglesia Local *</label>
                    <select name="iglesia_id" class="form-select" required>
                        <option value="">Seleccione iglesia...</option>
                        @foreach($iglesias as $ig)
                            <option value="{{ $ig->id }}" {{ old('iglesia_id') == $ig->id ? 'selected' : '' }}>
                                {{ $ig->nombre }} ({{ $ig->circuito->nombre ?? 'Circuito' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Categoria Institucional *</label>
                    <select name="categoria" class="form-select" required>
                        <option value="miembro_pleno" {{ old('categoria') == 'miembro_pleno' ? 'selected' : '' }}>Miembro Pleno</option>
                        <option value="miembro_preparatorio" {{ old('categoria') == 'miembro_preparatorio' ? 'selected' : '' }}>Miembro Preparatorio</option>
                        <option value="simpatizante" {{ old('categoria') == 'simpatizante' ? 'selected' : '' }}>Simpatizante</option>
                        <option value="nino" {{ old('categoria') == 'nino' ? 'selected' : '' }}>Niño/a</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Genero</label>
                    <select name="genero" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="masculino" {{ old('genero') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                        <option value="femenino" {{ old('genero') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Fecha de Ingreso a la Iglesia</label>
                    <input type="date" name="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', date('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Telefono de Contacto</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej. 71234567">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Correo Electronico</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Direccion / Comunidad</label>
                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}" placeholder="Comunidad, zona o referencia">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Observaciones Pastorales</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                </div>

                <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('membresia.index') }}" class="btn btn-sigem-outline">Cancelar</a>
                    <button type="submit" class="btn btn-sigem">
                        <i class="bi bi-save me-1"></i> Guardar Registro
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
