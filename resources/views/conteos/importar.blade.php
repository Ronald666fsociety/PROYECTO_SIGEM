@extends('layouts.app')

@section('title', 'Carga de Datos Históricos')
@section('page_title', 'Carga e Importación de Datos Históricos')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('conteos.index') }}" class="btn btn-sigem-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a Conteos
    </a>
</div>

<!-- Quality Gate Status Card -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-shield-check text-primary"></i>
            Estado Actual de la Compuerta de Calidad (Modelo Holt)
        </span>
        @if($compuerta['aprobada_validacion_real'])
            <span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; font-size:0.85rem;">
                <i class="bi bi-check-circle-fill me-1"></i> Apta para evaluación real
            </span>
        @elseif($compuerta['aprobada'] && $compuerta['contiene_sinteticos'])
            <span class="badge" style="background:#fffbeb; color:#92400e; border:1px solid #fde68a; font-size:0.85rem;">
                <i class="bi bi-beaker-fill me-1"></i> Solo prueba funcional
            </span>
        @else
            <span class="badge" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; font-size:0.85rem;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Calidad pendiente
            </span>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted" style="font-size:0.75rem; text-transform:uppercase; font-weight:600;">Meses Disponibles</div>
                    <div class="fw-bold" style="font-size:1.6rem; color:#1a3a5c;">{{ $compuerta['total_meses'] }}</div>
                    <div class="text-muted" style="font-size:0.75rem;">Mínimo requerido: 36 meses</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted" style="font-size:0.75rem; text-transform:uppercase; font-weight:600;">Total Registros</div>
                    <div class="fw-bold" style="font-size:1.6rem; color:#1a3a5c;">{{ number_format($compuerta['total_registros']) }}</div>
                    <div class="text-muted" style="font-size:0.75rem;">Registros iglesia-mes</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted" style="font-size:0.75rem; text-transform:uppercase; font-weight:600;">Primer Periodo</div>
                    <div class="fw-bold" style="font-size:1.4rem; color:#1a3a5c;">{{ $compuerta['primer_periodo'] }}</div>
                    <div class="text-muted" style="font-size:0.75rem;">Inicio de la serie</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted" style="font-size:0.75rem; text-transform:uppercase; font-weight:600;">Último Periodo</div>
                    <div class="fw-bold" style="font-size:1.4rem; color:#1a3a5c;">{{ $compuerta['ultimo_periodo'] }}</div>
                    <div class="text-muted" style="font-size:0.75rem;">Corte de pronóstico</div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <span class="badge {{ $compuerta['serie_continua'] ? 'bg-success' : 'bg-danger' }}">
                <i class="bi bi-calendar-check me-1"></i> Serie {{ $compuerta['serie_continua'] ? 'continua' : 'con meses faltantes' }}
            </span>
            <span class="badge {{ $compuerta['cobertura_completa'] ? 'bg-success' : 'bg-danger' }}">
                <i class="bi bi-building-check me-1"></i> {{ $compuerta['cobertura_completa'] ? '24 iglesias por corte' : 'Cobertura incompleta' }}
            </span>
            <span class="badge {{ $compuerta['contiene_sinteticos'] ? 'bg-warning text-dark' : 'bg-primary' }}">
                <i class="bi bi-database-check me-1"></i> {{ $compuerta['contiene_sinteticos'] ? 'Contiene datos sintéticos' : 'Sin datos sintéticos' }}
            </span>
        </div>

        <div class="alert alert-info mt-3 mb-0 py-2 d-flex align-items-center gap-2" style="font-size:0.82rem;">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div>
                <strong>Requisito del Proyecto de Grado:</strong> se necesitan al menos <strong>36 totales mensuales distritales continuos</strong>, cada uno construido con los registros de las 24 iglesias. Los primeros 30 meses se usan para desarrollo y los últimos 6 se reservan para comprobación final. No se rellenan meses faltantes ni se convierten totales anuales en datos mensuales inventados.
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Step 1 & 2: Upload Real Data -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-cloud-arrow-up-fill text-primary"></i>
                Paso 2: Cargar Archivo CSV con Datos Reales
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:0.85rem;">
                    Suba el archivo CSV con los conteos históricos de las iglesias. Puede utilizar la opción de reemplazo para borrar los datos ficticios de prueba y habilitar únicamente sus datos reales.
                </p>

                <form method="POST" action="{{ route('conteos.procesar-importar') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem;">Seleccionar Archivo CSV *</label>
                        <input type="file" name="archivo_csv" class="form-control" accept=".csv,.txt" required>
                        <div class="form-text" style="font-size:0.75rem;">Formatos admitidos: .csv codificado en UTF-8 o ANSI separado por comas (,).</div>
                    </div>

                    <div class="mb-4 p-3 rounded" style="background:#fffbeb; border:1px solid #fde68a;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="reemplazar_existentes" value="1" id="checkReemplazar" checked>
                            <label class="form-check-label fw-semibold text-warning-emphasis" for="checkReemplazar" style="font-size:0.85rem;">
                                Reemplazar datos existentes (Borrar datos ficticios previos)
                            </label>
                        </div>
                        <div class="text-muted mt-1" style="font-size:0.75rem;">
                            Esta opción elimina los {{ number_format($compuerta['total_registros']) }} conteos actuales antes de cargar la fuente histórica real. Use una copia respaldada y confirme que el archivo contiene los 24 registros de cada mes.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sigem w-100 py-2">
                        <i class="bi bi-upload me-1"></i> Procesar e Importar Datos Históricos
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Step 1: Download Template & Restore Test Data -->
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i>
                Paso 1: Descargar Plantilla Oficial
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:0.85rem;">
                    Descargue la plantilla CSV estructurada con los códigos oficiales de las 24 iglesias del Distrito Kollasuyo, lista para abrir y rellenar en Microsoft Excel.
                </p>
                <a href="{{ route('conteos.plantilla') }}" class="btn btn-sigem-outline w-100 py-2">
                    <i class="bi bi-download me-1"></i> Descargar Plantilla CSV Oficial (.csv)
                </a>
                <div class="mt-2 text-muted" style="font-size:0.73rem;">
                    Incluye cabeceras obligatorias: <code>codigo_iglesia, anio, mes, total_activos, estado</code>.
                </div>
            </div>
        </div>

        <!-- Testing & Demonstration Card -->
        <div class="card border-dashed" style="border-style:dashed; border-width:2px; background:#fafafa;">
            <div class="card-header bg-transparent border-0 pb-0">
                <i class="bi bi-arrow-repeat text-secondary"></i>
                <strong>Demostración / Pruebas</strong>
            </div>
            <div class="card-body pt-2">
                <p class="text-muted" style="font-size:0.8rem;">
                    Puede restaurar la serie sintética de 45 meses para comprobar pantallas, validaciones y flujo técnico. Sus métricas no deben presentarse como precisión predictiva real.
                </p>
                <form method="POST" action="{{ route('conteos.regenerar') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100" onclick="return confirm('¿Desea restaurar los datos ficticios de prueba de 45 meses?')">
                        <i class="bi bi-magic me-1"></i> Restaurar Serie Ficticia de Prueba
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
