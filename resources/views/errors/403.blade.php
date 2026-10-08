@extends('layouts.app')

@section('title', 'Acceso Denegado (403)')
@section('page_title', 'Control de Acceso Institucional')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-7 col-lg-6">
        <div class="card text-center p-4 shadow-sm border-0" style="background:#fff; border-radius:12px;">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:72px; height:72px; border-radius:50%; background:#fef2f2; color:#dc2626;">
                <i class="bi bi-shield-slash" style="font-size:2.4rem;"></i>
            </div>

            <h4 class="fw-bold text-dark mb-2">Acceso Restringido por Rol Institucional</h4>
            <div class="badge mx-auto mb-3" style="background:#fef3c7; color:#92400e; font-size:0.8rem; border:1px solid #fde68a;">
                Código de Respuesta HTTP: 403 Forbidden
            </div>

            <p class="text-muted" style="font-size:0.88rem; line-height:1.6;">
                {{ $exception->getMessage() ?: 'No cuenta con los privilegios institucionales necesarios para visualizar este módulo o ejecutar esta operación.' }}
            </p>

            <div class="p-3 my-3 rounded text-start" style="background:#f8fafc; border:1px solid var(--sigem-border); font-size:0.8rem;">
                <div class="fw-semibold text-dark mb-1">
                    <i class="bi bi-info-circle text-primary me-1"></i> Principio de Conexionalidad y Gobierno de Datos (IEMB):
                </div>
                <div class="text-muted">
                    Cada usuario está restringido exclusivamente al ámbito territorial y administrativo asignado a su responsabilidad:
                    <ul class="mb-0 mt-1 ps-3">
                        <li><strong>Pastores Locales:</strong> Exclusivamente su propia iglesia asignada.</li>
                        <li><strong>Responsables de Circuito:</strong> Las 6 iglesias de su circuito.</li>
                        <li><strong>Superintendencia y Administrador:</strong> Operaciones distritales consolidadas y pronóstico Holt.</li>
                    </ul>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2 mt-3">
                <a href="{{ route('dashboard') }}" class="btn btn-sigem">
                    <i class="bi bi-house me-1"></i> Volver a mi Panel Principal
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
