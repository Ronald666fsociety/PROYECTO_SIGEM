@extends('layouts.app')

@section('title', 'Nuevo Encargado Pastoral')
@section('page_title', 'Registrar Encargado Pastoral / Usuario')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('usuarios.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Usuarios
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-person-plus-fill text-primary"></i>
        Formulario de Designacion y Alta de Encargado Pastoral
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

        <form method="POST" action="{{ route('usuarios.store') }}" id="formUsuario">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Nombre Completo del Pastor o Encargado *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Ej. Rev. Mario Mamani Condori" required>
                    <div class="form-text" style="font-size:0.75rem;">Si es Pastor Local, este nombre se actualizara en el padron de la iglesia asignada.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Correo Electronico Institucional *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="pastor@sigem.bo" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Nivel Institucional / Rol *</label>
                    <select name="rol" id="selectRol" class="form-select" required>
                        <option value="">Seleccione rol institucional...</option>
                        <option value="local" {{ old('rol', 'local') == 'local' ? 'selected' : '' }}>
                            Responsable Local (Pastor/a o Encargado/a de Iglesia)
                        </option>
                        <option value="circuito" {{ old('rol') == 'circuito' ? 'selected' : '' }}>
                            Responsable de Circuito
                        </option>
                        <option value="distrito" {{ old('rol') == 'distrito' ? 'selected' : '' }}>
                            Superintendente de Distrito
                        </option>
                        <option value="admin" {{ old('rol') == 'admin' ? 'selected' : '' }}>
                            Administrador del Sistema
                        </option>
                    </select>
                </div>

                <!-- Church Assignment (Required for Local Pastor) -->
                <div class="col-md-4" id="groupIglesia">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Iglesia Local Asignada *</label>
                    <select name="iglesia_id" id="selectIglesia" class="form-select">
                        <option value="">Seleccione iglesia del distrito...</option>
                        @foreach($iglesias as $ig)
                            <option value="{{ $ig->id }}" {{ old('iglesia_id') == $ig->id ? 'selected' : '' }}>
                                {{ $ig->nombre }} ({{ $ig->circuito->nombre ?? 'Circuito' }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text" style="font-size:0.75rem;">El pastor tendra acceso restringido exclusivamente a esta iglesia.</div>
                </div>

                <!-- Circuit Assignment -->
                <div class="col-md-4" id="groupCircuito" style="display:none;">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Circuito Asignado *</label>
                    <select name="circuito_id" id="selectCircuito" class="form-select">
                        <option value="">Seleccione circuito...</option>
                        @foreach($circuitos as $c)
                            <option value="{{ $c->id }}" {{ old('circuito_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Telefono / Celular</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej. 72012345">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:0.82rem;">Contraseña de Acceso *</label>
                    <input type="password" name="password" class="form-control" value="password" required>
                    <div class="form-text" style="font-size:0.75rem;">Contraseña inicial por defecto: <code>password</code> (el usuario podra cambiarla).</div>
                </div>

                <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-sigem-outline">Cancelar</a>
                    <button type="submit" class="btn btn-sigem">
                        <i class="bi bi-save me-1"></i> Designar y Guardar Usuario
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
    const selectIglesia = document.getElementById('selectIglesia');
    const selectCircuito = document.getElementById('selectCircuito');

    function updateFields() {
        const rol = rolSelect.value;
        if (rol === 'local') {
            groupIglesia.style.display = 'block';
            selectIglesia.required = true;
            groupCircuito.style.display = 'none';
            selectCircuito.required = false;
        } else if (rol === 'circuito') {
            groupIglesia.style.display = 'none';
            selectIglesia.required = false;
            groupCircuito.style.display = 'block';
            selectCircuito.required = true;
        } else {
            groupIglesia.style.display = 'none';
            selectIglesia.required = false;
            groupCircuito.style.display = 'none';
            selectCircuito.required = false;
        }
    }

    rolSelect.addEventListener('change', updateFields);
    updateFields();
});
</script>
@endpush
