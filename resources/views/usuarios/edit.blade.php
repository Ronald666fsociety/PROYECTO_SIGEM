@extends('layouts.app')

@section('title', 'Editar Usuario / Pastor')
@section('page_title', 'Modificar Datos de Encargado Pastoral')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('usuarios.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Usuarios
    </a>
    <span style="font-size:0.85rem; color:#64748b;">{{ $usuario->name }}</span>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-pencil-square text-primary"></i>
        Actualizar Ficha y Designacion Institucional
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

        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Nombre Completo *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $usuario->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Correo Electronico Institucional *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $usuario->email) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Nivel Institucional / Rol *</label>
                    <select name="rol" id="selectRol" class="form-select" required>
                        <option value="local" {{ old('rol', $usuario->rol) == 'local' ? 'selected' : '' }}>
                            Responsable Local (Pastor/a o Encargado/a de Iglesia)
                        </option>
                        <option value="circuito" {{ old('rol', $usuario->rol) == 'circuito' ? 'selected' : '' }}>
                            Responsable de Circuito
                        </option>
                        <option value="distrito" {{ old('rol', $usuario->rol) == 'distrito' ? 'selected' : '' }}>
                            Superintendente de Distrito
                        </option>
                        <option value="admin" {{ old('rol', $usuario->rol) == 'admin' ? 'selected' : '' }}>
                            Administrador del Sistema
                        </option>
                    </select>
                </div>

                <!-- Church Assignment -->
                <div class="col-md-4" id="groupIglesia">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Iglesia Local Asignada</label>
                    <select name="iglesia_id" id="selectIglesia" class="form-select">
                        <option value="">Sin iglesia asignada</option>
                        @foreach($iglesias as $ig)
                            <option value="{{ $ig->id }}" {{ old('iglesia_id', $usuario->iglesia_id) == $ig->id ? 'selected' : '' }}>
                                {{ $ig->nombre }} ({{ $ig->circuito->nombre ?? 'Circuito' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Circuit Assignment -->
                <div class="col-md-4" id="groupCircuito" style="display:none;">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Circuito Asignado</label>
                    <select name="circuito_id" id="selectCircuito" class="form-select">
                        <option value="">Sin circuito asignado</option>
                        @foreach($circuitos as $c)
                            <option value="{{ $c->id }}" {{ old('circuito_id', $usuario->circuito_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Estado *</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" {{ old('estado', $usuario->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $usuario->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Telefono / Celular</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $usuario->telefono) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Cambiar Contraseña (opcional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                </div>

                <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-sigem-outline">Cancelar</a>
                    <button type="submit" class="btn btn-sigem">
                        <i class="bi bi-check-lg me-1"></i> Actualizar Usuario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('selectRol');
    const groupIglesia = document.getElementById('groupIglesia');
    const groupCircuito = document.getElementById('groupCircuito');

    function updateFields() {
        const rol = rolSelect.value;
        if (rol === 'local') {
            groupIglesia.style.display = 'block';
            groupCircuito.style.display = 'none';
        } else if (rol === 'circuito') {
            groupIglesia.style.display = 'none';
            groupCircuito.style.display = 'block';
        } else {
            groupIglesia.style.display = 'none';
            groupCircuito.style.display = 'none';
        }
    }

    rolSelect.addEventListener('change', updateFields);
    updateFields();
});
</script>
@endpush
