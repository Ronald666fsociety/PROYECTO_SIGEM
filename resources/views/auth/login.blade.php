<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Iniciar Sesión - SIGEM | IEMB Distrito Kollasuyo</title>

    <!-- PWA Manifest & Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1a3a5c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIGEM">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --sigem-navy: #0d1f33;
            --sigem-primary: #1a3a5c;
            --sigem-accent: #c49a2a;
            --sigem-accent-light: #e5b945;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            background: #0d1f33;
            overflow-x: hidden;
        }

        /* Split Layout Container */
        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left Hero Section with Puerto Carabuco Enhanced Background */
        .login-hero {
            flex: 1.25;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px 60px;
            color: #fff;
            background:
                linear-gradient(135deg, rgba(13, 31, 51, 0.90) 0%, rgba(26, 58, 92, 0.82) 45%, rgba(15, 111, 170, 0.70) 100%),
                url('/images/bg_carabuco.jpg') center center / cover no-repeat;
            overflow: hidden;
        }

        /* Decorative blur lights */
        .login-hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(196, 154, 42, 0.22) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-top {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 7px 18px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #f1f5f9;
        }

        .hero-badge i {
            color: var(--sigem-accent-light);
            font-size: 0.9rem;
        }

        .hero-center {
            position: relative;
            z-index: 2;
            max-width: 620px;
            margin: 40px 0;
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.5);
            margin-bottom: 28px;
        }

        .logo-container img {
            height: 105px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.12));
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.15;
            color: #ffffff;
            margin-bottom: 12px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .hero-title span {
            color: var(--sigem-accent-light);
        }

        .hero-desc {
            font-size: 1.05rem;
            color: #e2e8f0;
            line-height: 1.6;
            margin-bottom: 32px;
            text-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }

        .hero-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .hero-feature-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.09);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .hero-feature-card:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.14);
        }

        .hero-feature-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(196, 154, 42, 0.25);
            border: 1px solid rgba(196, 154, 42, 0.4);
            color: var(--sigem-accent-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .hero-feature-text {
            font-size: 0.82rem;
            font-weight: 600;
            color: #f8fafc;
            line-height: 1.3;
        }

        .hero-footer {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.78rem;
            color: #cbd5e1;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        /* Right Form Section */
        .login-form-panel {
            width: 490px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 45px;
            position: relative;
            z-index: 10;
            box-shadow: -15px 0 35px rgba(0, 0, 0, 0.15);
        }

        .form-header {
            margin-bottom: 26px;
        }

        .form-mini-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--sigem-primary);
            margin-bottom: 12px;
        }

        .form-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--sigem-navy);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .form-subtitle {
            font-size: 0.85rem;
            color: #64748b;
        }

        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #64748b;
            padding: 0 14px;
        }

        .form-control {
            border-color: #cbd5e1;
            padding: 11px 14px;
            font-size: 0.88rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--sigem-primary);
            box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.12);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--sigem-primary), #2a5a8c);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.92rem;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(26, 58, 92, 0.25);
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #15304d, #1a3a5c);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(26, 58, 92, 0.32);
        }

        /* Quick Account Switcher for Demo */
        .demo-roles-container {
            margin-top: 26px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px dashed #cbd5e1;
        }

        .demo-roles-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .demo-pills {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .demo-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 0.74rem;
            color: #334155;
            cursor: pointer;
            text-align: left;
            transition: all 0.15s ease;
            display: flex;
            flex-direction: column;
        }

        .demo-pill:hover {
            border-color: var(--sigem-primary);
            background: #f0f7ff;
            color: var(--sigem-primary);
        }

        .demo-pill strong {
            font-weight: 600;
            font-size: 0.78rem;
        }

        .demo-pill span {
            font-size: 0.68rem;
            color: #64748b;
        }

        @media (max-width: 1024px) {
            .login-wrapper {
                flex-direction: column;
            }
            .login-hero {
                padding: 40px 24px;
                min-height: auto;
            }
            .hero-title {
                font-size: 2.2rem;
            }
            .hero-features {
                grid-template-columns: 1fr;
            }
            .login-form-panel {
                width: 100%;
                padding: 36px 24px;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Hero Section with Puerto Carabuco Landscape and IEMB Official Logo -->
        <section class="login-hero">
            <div class="hero-top">
                <div class="hero-badge">
                    <i class="bi bi-geo-alt-fill"></i>
                    Distrito Kollasuyo &bull; La Paz, Bolivia
                </div>
            </div>

            <div class="hero-center">
                <!-- Official Church Logo Badge -->
                <div class="logo-container">
                    <img src="/images/logo_iemb_transparent.png" alt="Logo Oficial Iglesia Evangélica Metodista en Bolivia">
                </div>

                <h1 class="hero-title">
                    SIGEM <span>&bull; IEMB</span>
                </h1>
                <p class="hero-desc">
                    Sistema de Información y Gestión Eclesial con Modelo Predictivo Exploratorio basado en Holt para la Gestión Distrital y Estimación del Crecimiento de Membresía.
                </p>

                <div class="hero-features">
                    <div class="hero-feature-card">
                        <div class="hero-feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="hero-feature-text">
                            Modelo Predictivo Holt<br>
                            <span style="font-size:0.72rem; font-weight:normal; opacity:0.85;">Pronóstico a 6 meses vista</span>
                        </div>
                    </div>

                    <div class="hero-feature-card">
                        <div class="hero-feature-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="hero-feature-text">
                            24 Iglesias en 4 Circuitos<br>
                            <span style="font-size:0.72rem; font-weight:normal; opacity:0.85;">Cobertura Distrital Kollasuyo</span>
                        </div>
                    </div>

                    <div class="hero-feature-card">
                        <div class="hero-feature-icon">
                            <i class="bi bi-phone-fill"></i>
                        </div>
                        <div class="hero-feature-text">
                            Aplicación Web Progresiva<br>
                            <span style="font-size:0.72rem; font-weight:normal; opacity:0.85;">Instalable y soporte offline</span>
                        </div>
                    </div>

                    <div class="hero-feature-card">
                        <div class="hero-feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="hero-feature-text">
                            Gobierno por Roles<br>
                            <span style="font-size:0.72rem; font-weight:normal; opacity:0.85;">Aislamiento territorial conexional</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-footer">
                <div>
                    <strong>Iglesia Evangélica Metodista en Bolivia</strong> &bull; Conexionalidad y Misión
                </div>
                <div>
                    Puerto Carabuco &bull; Altiplano Paceño
                </div>
            </div>
        </section>

        <!-- Right Login Form Panel -->
        <section class="login-form-panel">
            <div class="form-header">
                <div class="form-mini-badge">
                    <i class="bi bi-lock-fill"></i> Portal Institucional Autorizado
                </div>
                <h2 class="form-title">Acceso al Sistema</h2>
                <p class="form-subtitle">Ingrese sus credenciales registradas para iniciar sesión</p>
            </div>

            @if($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-4" style="font-size:0.82rem; border-radius:8px;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico Institucional</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', 'admin@sigem.bo') }}" required autofocus placeholder="usuario@sigem.bo">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña de Acceso</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                        <input type="password" class="form-control" id="password" name="password" value="password" required placeholder="Contraseña">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4" style="font-size:0.82rem;">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                        <label class="form-check-label text-muted" for="remember">Recordar sesión</label>
                    </div>
                    <button type="button" class="btn btn-link text-decoration-none p-0 d-none" id="btnInstallLogin" style="font-size:0.8rem; color:var(--sigem-accent); font-weight:600;">
                        <i class="bi bi-download me-1"></i> Instalar PWA
                    </button>
                </div>

                <button type="submit" class="btn btn-submit">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión en SIGEM
                </button>
            </form>

            <!-- Quick Account Switcher for Demo / Defense -->
            <div class="demo-roles-container">
                <div class="demo-roles-title">
                    <span><i class="bi bi-person-badge me-1"></i> Acceso Rápido por Rol:</span>
                    <span class="badge bg-light text-muted border">Modo Demostración</span>
                </div>
                <div class="demo-pills">
                    <button type="button" class="demo-pill" onclick="seleccionarCuenta('admin@sigem.bo')">
                        <strong>Administrador</strong>
                        <span>Control total y usuarios</span>
                    </button>
                    <button type="button" class="demo-pill" onclick="seleccionarCuenta('distrito@sigem.bo')">
                        <strong>Superintendente</strong>
                        <span>Distrito y Modelo Holt</span>
                    </button>
                    <button type="button" class="demo-pill" onclick="seleccionarCuenta('circuito1@sigem.bo')">
                        <strong>Resp. Circuito</strong>
                        <span>Circuito Carabuco (6 igl.)</span>
                    </button>
                    <button type="button" class="demo-pill" onclick="seleccionarCuenta('iglesia1@sigem.bo')">
                        <strong>Pastor Local</strong>
                        <span>Carabuco Central</span>
                    </button>
                </div>
            </div>
        </section>
    </div>

    <!-- PWA Service Worker Registration & Quick Selector Script -->
    <script>
        function seleccionarCuenta(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
            document.getElementById('email').focus();
        }

        // Service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        // PWA install trigger
        let deferredPrompt;
        const btnInstallLogin = document.getElementById('btnInstallLogin');
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (btnInstallLogin) btnInstallLogin.classList.remove('d-none');
        });
        if (btnInstallLogin) {
            btnInstallLogin.addEventListener('click', () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt = null;
                    btnInstallLogin.classList.add('d-none');
                }
            });
        }
    </script>
</body>
</html>
