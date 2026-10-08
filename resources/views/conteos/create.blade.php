@extends('layouts.app')

@section('title', 'Nuevo Conteo Mensual')
@section('page_title', 'Registrar Conteo Mensual de Membresía')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('conteos.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Conteos
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-plus-fill text-primary"></i>
                Formulario de Corte y Conteo de Membresía
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

                <form method="POST" action="{{ route('conteos.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Iglesia Local *</label>
                            @if(auth()->user()->isLocal())
                                <input type="hidden" name="iglesia_id" value="{{ auth()->user()->iglesia_id }}">
                                <input type="text" class="form-control" value="{{ auth()->user()->iglesia->nombre ?? 'Mi Iglesia' }}" readonly>
                            @else
                                <select name="iglesia_id" class="form-select" required>
                                    <option value="">Seleccione iglesia...</option>
                                    @foreach($iglesias as $ig)
                                        <option value="{{ $ig->id }}" {{ old('iglesia_id') == $ig->id ? 'selected' : '' }}>
                                            {{ $ig->nombre }} ({{ $ig->circuito->nombre ?? 'Circuito' }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Año *</label>
                            <input type="number" name="anio" class="form-control" value="{{ old('anio', $anioActual) }}" min="2000" max="2050" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Mes *</label>
                            <select name="mes" class="form-select" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ old('mes', $mesActual) == $m ? 'selected' : '' }}>
                                        Mes {{ $m }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Total Membresía Activa (Corte del Mes) *</label>
                            <input type="number" name="total_activos" class="form-control" value="{{ old('total_activos') }}" placeholder="Ej. 65" min="0" required>
                            <div class="form-text" style="font-size:0.75rem;">Variable principal utilizada para el cálculo de la serie de tiempo de Holt.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Total Inactivos / Pasivos</label>
                            <input type="number" name="total_inactivos" class="form-control" value="{{ old('total_inactivos', 0) }}" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Nuevos Ingresos / Bautismos</label>
                            <input type="number" name="total_nuevos" class="form-control" value="{{ old('total_nuevos', 0) }}" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Transferidos (Conexionales)</label>
                            <input type="number" name="total_transferidos" class="form-control" value="{{ old('total_transferidos', 0) }}" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Bajas del Mes</label>
                            <input type="number" name="total_bajas" class="form-control" value="{{ old('total_bajas', 0) }}" min="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Fecha Formal de Corte</label>
                            <input type="date" name="fecha_corte" class="form-control" value="{{ old('fecha_corte', date('Y-m-d')) }}">
                            <div class="form-text" style="font-size:0.75rem;">Por defecto se asume el último día del mes evaluado.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:0.82rem;">Estado del Conteo *</label>
                            <select name="estado" class="form-select" required>
                                <option value="cerrado" {{ old('estado', 'cerrado') == 'cerrado' ? 'selected' : '' }}>
                                    Cerrado (Aprobado para cálculo predictivo)
                                </option>
                                <option value="validado" {{ old('estado') == 'validado' ? 'selected' : '' }}>
                                    Validado por Circuito
                                </option>
                                <option value="borrador" {{ old('estado') == 'borrador' ? 'selected' : '' }}>
                                    Borrador (Preliminar)
                                </option>
                            </select>
                            <div class="form-text" style="font-size:0.75rem;">Solo estados 'cerrado' y 'validado' ingresan al modelo de predicción.</div>
                        </div>

                        <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('conteos.index') }}" class="btn btn-sigem-outline">Cancelar</a>
                            <button type="submit" class="btn btn-sigem">
                                <i class="bi bi-save me-1"></i> Guardar Conteo Mensual
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
