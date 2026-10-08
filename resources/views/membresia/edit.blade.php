@extends('layouts.app')

@section('title', 'Editar Miembro')
@section('page_title', 'Modificar Datos de Miembro')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('membresia.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al Listado
    </a>
    <span style="font-size:0.85rem; color:#64748b;">{{ $miembro->nombre_completo }}</span>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-pencil-square text-primary"></i>
        Actualizar Ficha de Miembro
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

        <form method="POST" action="{{ route('membresia.update', $miembro) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Nombres *</label>
                    <input type="text" name="nombres" class="form-control" value="{{ old('nombres', $miembro->nombres) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos', $miembro->apellidos) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">CI</label>
                    <input type="text" name="ci" class="form-control" value="{{ old('ci', $miembro->ci) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Iglesia Local *</label>
                    <select name="iglesia_id" class="form-select" required>
                        @foreach($iglesias as $ig)
                            <option value="{{ $ig->id }}" {{ old('iglesia_id', $miembro->iglesia_id) == $ig->id ? 'selected' : '' }}>
                                {{ $ig->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Categoria Institucional *</label>
                    <select name="categoria" class="form-select" required>
                        <option value="miembro_pleno" {{ old('categoria', $miembro->categoria) == 'miembro_pleno' ? 'selected' : '' }}>Miembro Pleno</option>
                        <option value="miembro_preparatorio" {{ old('categoria', $miembro->categoria) == 'miembro_preparatorio' ? 'selected' : '' }}>Miembro Preparatorio</option>
                        <option value="simpatizante" {{ old('categoria', $miembro->categoria) == 'simpatizante' ? 'selected' : '' }}>Simpatizante</option>
                        <option value="nino" {{ old('categoria', $miembro->categoria) == 'nino' ? 'selected' : '' }}>Niño/a</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Estado *</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" {{ old('estado', $miembro->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $miembro->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        <option value="transferido" {{ old('estado', $miembro->estado) == 'transferido' ? 'selected' : '' }}>Transferido</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Genero</label>
                    <select name="genero" class="form-select">
                        <option value="masculino" {{ old('genero', $miembro->genero) == 'masculino' ? 'selected' : '' }}>Masculino</option>
                        <option value="femenino" {{ old('genero', $miembro->genero) == 'femenino' ? 'selected' : '' }}>Femenino</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', $miembro->fecha_nacimiento?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Telefono</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $miembro->telefono) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Correo</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $miembro->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Direccion</label>
                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $miembro->direccion) }}">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $miembro->observaciones) }}</textarea>
                </div>

                <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('membresia.index') }}" class="btn btn-sigem-outline">Cancelar</a>
                    <button type="submit" class="btn btn-sigem">
                        <i class="bi bi-check-lg me-1"></i> Actualizar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
