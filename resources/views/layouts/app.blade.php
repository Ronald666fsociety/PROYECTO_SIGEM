<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIGEM') - Sistema de Gestion Eclesial</title>
    <meta name="description" content="SIGEM - Sistema de Informacion y Gestion Eclesial con Modelo Predictivo Holt &bull; Distrito Kollasuyo, IEMB">

    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1a3a5c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIGEM">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --sigem-primary: #1a3a5c;
            --sigem-primary-light: #2a5a8c;
            --sigem-primary-dark: #0d1f33;
            --sigem-accent: #c49a2a;
            --sigem-accent-light: #dbb548;
            --sigem-success: #198754;
            --sigem-info: #0f6faa;
            --sigem-warning: #c49a2a;
            --sigem-danger: #a0342e;
            --sigem-bg: #f8fafc;
            --sigem-card-bg: #ffffff;
            --sigem-sidebar-bg: #0d1f33;
            --sigem-sidebar-hover: #1a3a5c;
            --sigem-text: #1e293b;
            --sigem-text-muted: #64748b;
            --sigem-border: #e2e8f0;
            --sigem-radius: 12px;
            --sigem-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --sigem-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--sigem-bg);
            color: var(--sigem-text);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Overlay for Mobile */
        .sigem-sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
        }

        /* Sidebar */
        .sigem-sidebar {
            width: 270px;
            min-height: 100vh;
            background: var(--sigem-sidebar-bg);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sigem-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sigem-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--sigem-accent), var(--sigem-accent-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .sigem-brand-text h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
            margin: 0;
            line-height: 1.2;
        }

        .sigem-brand-text span {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 400;
            display: block;
        }

        .sigem-nav {
            padding: 16px 12px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-section-title {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 12px 14px 6px;
        }

        .sigem-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 3px;
        }

        .sigem-nav-link i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
        }

        .sigem-nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.06);
        }

        .sigem-nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, var(--sigem-primary-light), var(--sigem-primary));
            box-shadow: 0 4px 12px rgba(42,90,140,0.3);
        }

        .sigem-nav-link.active i {
            color: var(--sigem-accent);
        }

        /* PWA Install Button in Sidebar */
        .pwa-install-container {
            padding: 12px 16px;
            margin: 8px 12px;
            background: rgba(196, 154, 42, 0.12);
            border: 1px dashed rgba(196, 154, 42, 0.4);
            border-radius: 10px;
            display: none;
        }

        .btn-pwa-install {
            background: linear-gradient(135deg, var(--sigem-accent), var(--sigem-accent-light));
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-pwa-install:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        .sigem-user-card {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(0,0,0,0.15);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--sigem-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .user-info {
            overflow: hidden;
            flex-grow: 1;
        }

        .user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.72rem;
            color: var(--sigem-accent);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Main Content */
        .sigem-main {
            margin-left: 270px;
            flex-grow: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - 270px);
            transition: all 0.3s ease;
        }

        .sigem-topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid var(--sigem-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .btn-mobile-toggle {
            display: none;
            background: transparent;
            border: 1px solid var(--sigem-border);
            border-radius: 8px;
            padding: 6px 10px;
            color: var(--sigem-text);
            font-size: 1.2rem;
            cursor: pointer;
            margin-right: 12px;
        }

        .page-header-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--sigem-text);
            margin: 0;
        }

        .sigem-body {
            padding: 24px 28px;
            flex-grow: 1;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .sigem-sidebar {
                transform: translateX(-100%);
            }
            .sigem-sidebar.show {
                transform: translateX(0);
            }
            .sigem-sidebar-overlay.show {
                display: block;
            }
            .sigem-main {
                margin-left: 0;
                width: 100%;
            }
            .btn-mobile-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .sigem-topbar {
                padding: 0 16px;
            }
            .sigem-body {
                padding: 16px;
            }
        }

        /* Cards */
        .card {
            border: 1px solid var(--sigem-border);
            border-radius: var(--sigem-radius);
            box-shadow: var(--sigem-shadow);
            background: var(--sigem-card-bg);
            margin-bottom: 24px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--sigem-border);
            padding: 16px 20px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* KPI Card */
        .kpi-card {
            border-radius: var(--sigem-radius);
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: var(--sigem-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--sigem-shadow-lg);
        }

        .kpi-card .kpi-icon {
            position: absolute;
            right: 16px;
            top: 16px;
            font-size: 2.2rem;
            opacity: 0.25;
        }

        .kpi-card .kpi-value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .kpi-card .kpi-label {
            font-size: 0.78rem;
            font-weight: 500;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-card .kpi-trend {
            margin-top: 10px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0.95;
        }

        .kpi-primary { background: linear-gradient(135deg, #1a3a5c, #2a5a8c); }
        .kpi-success { background: linear-gradient(135deg, #146c43, #198754); }
        .kpi-info    { background: linear-gradient(135deg, #0b5380, #0f6faa); }
        .kpi-accent  { background: linear-gradient(135deg, #99741e, #c49a2a); }

        /* Buttons & Badges */
        .btn-sigem {
            background: var(--sigem-primary);
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-sigem:hover {
            background: var(--sigem-primary-light);
            color: #fff;
        }

        .btn-sigem-outline {
            background: transparent;
            color: var(--sigem-primary);
            border: 1px solid var(--sigem-primary);
            font-weight: 600;
            padding: 7px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-sigem-outline:hover {
            background: var(--sigem-primary);
            color: #fff;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-activo    { background: #dcfce7; color: #166534; }
        .badge-inactivo  { background: #fee2e2; color: #991b1b; }
        .badge-viable    { background: #dcfce7; color: #166534; }
        .badge-no_viable { background: #fef3c7; color: #92400e; }

        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--sigem-text-muted);
            font-weight: 600;
            border-bottom: 2px solid var(--sigem-border);
            padding: 12px 16px;
        }

        .table td {
            font-size: 0.85rem;
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--sigem-border);
        }

        .pred-metric {
            background: #fff;
            border: 1px solid var(--sigem-border);
            border-radius: 10px;
            padding: 14px 18px;
            text-align: center;
        }

        .pred-metric-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--sigem-primary);
        }

        .pred-metric-label {
            font-size: 0.72rem;
            color: var(--sigem-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* Pagination Styling & Safety SVG size cap */
        .pagination {
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .page-link {
            color: var(--sigem-primary);
            border-radius: 6px !important;
            border-color: var(--sigem-border);
            font-size: 0.82rem;
            padding: 5px 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .page-item.active .page-link {
            background-color: var(--sigem-primary);
            border-color: var(--sigem-primary);
            color: #fff;
        }
        .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: #f8fafc;
        }
        /* Strict SVG size cap across all nav and pagination components */
        nav svg,
        .pagination svg,
        .page-link svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            display: inline-block !important;
            vertical-align: middle;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="sigem-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sigem-sidebar" id="sigemSidebar">
        <div class="sigem-brand">
            <div class="sigem-brand-icon" style="background:#ffffff; padding:4px; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                <img src="/images/logo_iemb_transparent.png" alt="Logo IEMB" style="height:34px; width:auto; object-fit:contain;">
            </div>
            <div class="sigem-brand-text">
                <h1>SIGEM</h1>
                <span>Distrito Kollasuyo &bull; PWA</span>
            </div>
        </div>

        <nav class="sigem-nav">
            <div class="nav-section-title">Principal</div>
            <a href="{{ route('dashboard') }}" class="sigem-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Panel Principal</span>
            </a>

            <div class="nav-section-title">
                @if(Auth::user()?->isLocal())
                    Mi Congregación
                @elseif(Auth::user()?->isCircuito())
                    Gestión del Circuito
                @else
                    Gestión Eclesial
                @endif
            </div>

            <a href="{{ route('membresia.index') }}" class="sigem-nav-link {{ request()->routeIs('membresia.*') && !request()->routeIs('conteos.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>
                    @if(Auth::user()?->isLocal())
                        Membresía Local
                    @elseif(Auth::user()?->isCircuito())
                        Membresía Circuito
                    @else
                        Membresía Distrital
                    @endif
                </span>
            </a>

            <a href="{{ route('iglesias.index') }}" class="sigem-nav-link {{ request()->routeIs('iglesias.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span>
                    @if(Auth::user()?->isLocal())
                        Mi Iglesia
                    @elseif(Auth::user()?->isCircuito())
                        Iglesias del Circuito
                    @else
                        Iglesias y Circuitos
                    @endif
                </span>
            </a>

            <a href="{{ route('conteos.index') }}" class="sigem-nav-link {{ request()->routeIs('conteos.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data-fill"></i>
                <span>
                    @if(Auth::user()?->isLocal())
                        Mis Conteos Mensuales
                    @elseif(Auth::user()?->isCircuito())
                        Conteos del Circuito
                    @else
                        Conteos y Serie Histórica
                    @endif
                </span>
            </a>

            @if(Auth::user()?->hasAccesoDistrito())
            <div class="nav-section-title">Análisis Estratégico</div>
            <a href="{{ route('prediccion.index') }}" class="sigem-nav-link {{ request()->routeIs('prediccion.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Pronóstico Holt</span>
            </a>
            <a href="{{ route('conteos.importar') }}" class="sigem-nav-link {{ request()->routeIs('conteos.importar') ? 'active' : '' }}">
                <i class="bi bi-cloud-arrow-up"></i>
                <span>Carga Datos Históricos</span>
            </a>
            @endif

            @if(Auth::user()?->hasAccesoDistrito())
            <div class="nav-section-title">Administración y Roles</div>
            <a href="{{ route('usuarios.index') }}" class="sigem-nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i>
                <span>Usuarios y Pastores</span>
            </a>
            @endif

            <!-- PWA Install Card inside Sidebar -->
            <div class="pwa-install-container" id="pwaSidebarBox">
                <div style="font-size:0.75rem; color:#f8fafc; margin-bottom:8px; font-weight:500;">
                    Acceso directo PWA disponible
                </div>
                <button type="button" class="btn-pwa-install" id="btnPwaInstall">
                    <i class="bi bi-download"></i> Instalar Aplicacion
                </button>
            </div>
        </nav>

        <div class="sigem-user-card">
            <div class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name ?? 'Usuario SIGEM' }}</div>
                <div class="user-role">{{ Auth::user()->rol_display ?? 'Administrador' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link text-white-50 p-0 border-0" title="Cerrar sesion">
                    <i class="bi bi-box-arrow-right" style="font-size:1.15rem;"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="sigem-main">
        <header class="sigem-topbar">
            <div class="d-flex align-items-center">
                <button class="btn-mobile-toggle" id="btnSidebarToggle" aria-label="Abrir menu">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="page-header-title">@yield('page_title', 'SIGEM')</h2>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- PWA Topbar Badge / Button -->
                <button class="btn btn-sm btn-outline-warning d-none" id="btnTopbarInstall" style="font-size:0.75rem; font-weight:600;">
                    <i class="bi bi-download me-1"></i> Instalar App
                </button>
                <span class="badge d-none d-md-inline-block" style="background:#e2e8f0; color:#475569; font-weight:500; font-size:0.75rem;">
                    <i class="bi bi-calendar3 me-1"></i> {{ date('d/m/Y') }}
                </span>
                <span class="badge" style="background:#dbeafe; color:#1e40af; font-weight:500; font-size:0.75rem;">
                    <i class="bi bi-wifi me-1" id="onlineIndicatorIcon"></i>
                    <span id="onlineIndicatorText">En linea</span>
                </span>
            </div>
        </header>

        <main class="sigem-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:1.2rem;"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PWA & Mobile Navigation Scripts -->
    <script>
        // 1. Mobile Sidebar Toggle
        const toggleBtn = document.getElementById('btnSidebarToggle');
        const sidebar = document.getElementById('sigemSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (toggleBtn && sidebar && overlay) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        // 2. Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SIGEM ServiceWorker registrado con exito:', registration.scope);
                    })
                    .catch((err) => {
                        console.warn('Fallo en registro de ServiceWorker:', err);
                    });
            });
        }

        // 3. Online/Offline Network Status Detection
        function updateNetworkStatus() {
            const icon = document.getElementById('onlineIndicatorIcon');
            const text = document.getElementById('onlineIndicatorText');
            if (navigator.onLine) {
                if (icon) icon.className = 'bi bi-wifi me-1';
                if (text) text.textContent = 'En linea';
            } else {
                if (icon) icon.className = 'bi bi-wifi-off me-1';
                if (text) text.textContent = 'Sin conexion';
            }
        }
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        updateNetworkStatus();

        // 4. PWA Installation Handler
        let deferredPrompt;
        const pwaSidebarBox = document.getElementById('pwaSidebarBox');
        const btnPwaInstall = document.getElementById('btnPwaInstall');
        const btnTopbarInstall = document.getElementById('btnTopbarInstall');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (pwaSidebarBox) pwaSidebarBox.style.display = 'block';
            if (btnTopbarInstall) btnTopbarInstall.classList.remove('d-none');
        });

        function triggerInstall() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('El usuario acepto instalar SIGEM como PWA');
                }
                deferredPrompt = null;
                if (pwaSidebarBox) pwaSidebarBox.style.display = 'none';
                if (btnTopbarInstall) btnTopbarInstall.classList.add('d-none');
            });
        }

        if (btnPwaInstall) btnPwaInstall.addEventListener('click', triggerInstall);
        if (btnTopbarInstall) btnTopbarInstall.addEventListener('click', triggerInstall);

        window.addEventListener('appinstalled', () => {
            console.log('SIGEM PWA instalada exitosamente');
            if (pwaSidebarBox) pwaSidebarBox.style.display = 'none';
            if (btnTopbarInstall) btnTopbarInstall.classList.add('d-none');
        });
    </script>
    @stack('scripts')
</body>
</html>
