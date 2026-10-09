@php($circuito = $circuito ?? null)
<div class="row g-3">
    <div class="col-md-8"><label for="nombre" class="form-label fw-semibold">Nombre</label><input id="nombre" name="nombre" class="form-control" required maxlength="255" value="{{ old('nombre', $circuito?->nombre) }}"></div>
    <div class="col-md-4"><label for="codigo" class="form-label fw-semibold">Código</label><input id="codigo" name="codigo" class="form-control" required maxlength="50" value="{{ old('codigo', $circuito?->codigo) }}"></div>
    <div class="col-12"><label for="descripcion" class="form-label fw-semibold">Descripción</label><textarea id="descripcion" name="descripcion" class="form-control" rows="3">{{ old('descripcion', $circuito?->descripcion) }}</textarea></div>
    <div class="col-md-5"><label for="responsable_nombre" class="form-label fw-semibold">Responsable</label><input id="responsable_nombre" name="responsable_nombre" class="form-control" maxlength="255" value="{{ old('responsable_nombre', $circuito?->responsable_nombre) }}"></div>
    <div class="col-md-4"><label for="telefono" class="form-label fw-semibold">Teléfono</label><input id="telefono" name="telefono" class="form-control" maxlength="30" value="{{ old('telefono', $circuito?->telefono) }}"></div>
    <div class="col-md-3"><label for="estado" class="form-label fw-semibold">Estado</label><select id="estado" name="estado" class="form-select"><option value="activo" @selected(old('estado', $circuito?->estado ?? 'activo') === 'activo')>Activo</option><option value="inactivo" @selected(old('estado', $circuito?->estado) === 'inactivo')>Inactivo</option></select></div>
</div>
