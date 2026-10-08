# SIGEM — gestión distrital y pronóstico de membresía

SIGEM es una aplicación web adaptable con acceso móvil mediante PWA para organizar la información de las 24 iglesias del Distrito Kollasuyo. Integra un modelo predictivo exploratorio basado en Holt para estimar el crecimiento mensual de la membresía sin sustituir la decisión de las autoridades institucionales.

## Funciones principales

- Administración de usuarios, roles, circuitos e iglesias.
- Registro, validación y consolidación de cortes mensuales de membresía.
- Consulta de miembros, indicadores e información distrital.
- Acceso desde computadora y teléfono mediante una misma PWA.
- Importación de datos históricos con control de procedencia.
- Evaluación temporal y pronóstico de membresía con Holt.

## Protocolo predictivo

Holt lineal sin estacionalidad es el aporte predictivo integrado. Su desempeño no se presupone: se compara con último valor observado, suavizamiento exponencial simple y regresión lineal temporal.

| Elemento | Decisión metodológica |
|---|---|
| Unidad de análisis | Un total mensual distrital agregado de las 24 iglesias |
| Serie mínima | 36 meses continuos y completos |
| Desarrollo con 36 meses | Primeros 30 meses |
| Comprobación final | Últimos 6 meses, reservados |
| Evaluación interna | Siete orígenes expansivos desde 18 hasta 24 meses |
| Horizontes | De 1 a 6 meses |
| Métrica principal | MAE |
| Métrica complementaria | RMSE |

Los datos sintéticos sirven únicamente para probar el funcionamiento. La validación de precisión requiere registros históricos reales. SIGEM no rellena meses faltantes, no convierte totales anuales en observaciones mensuales y no oculta estimaciones negativas.

## Instalación rápida

Requisitos:

- PHP 8.3 o superior y Composer.
- Node.js y npm.
- Python con `numpy`, `pandas` y `statsmodels`.
- SQLite para el entorno local.

En PowerShell:

```powershell
git clone https://github.com/Ronald666fsociety/PROYECTO_SIGEM.git
Set-Location PROYECTO_SIGEM
Copy-Item .env.example .env
composer install
npm install
php artisan key:generate
New-Item -ItemType File -Force database/database.sqlite
php artisan migrate --seed
python -m pip install numpy pandas statsmodels
npm run build
php artisan serve
```

Abra `http://127.0.0.1:8000`. El usuario inicial se configura mediante `SIGEM_ADMIN_EMAIL` y `SIGEM_ADMIN_PASSWORD` en `.env`. Cambie la contraseña predeterminada antes de usar el sistema fuera de un entorno local.

## Datos y privacidad

El repositorio no contiene:

- El archivo `.env`.
- La base SQLite local.
- Datos personales de miembros o usuarios.
- Conteos sintéticos o históricos institucionales.
- Resultados temporales del modelo.

La estructura de cuatro circuitos y 24 iglesias se crea con el seeder. Los conteos históricos deben importarse desde una fuente institucional autorizada.

## Verificación

```powershell
php artisan test --compact
python -m unittest discover -s tests/python -v
```

Las pruebas predictivas verifican el protocolo temporal, la comparación de cuatro métodos, el rechazo de cortes incompletos, la continuidad mensual y la identificación de datos sintéticos.

## Tecnologías

- Laravel 13, PHP y arquitectura MVC.
- Blade, AdminLTE, Bootstrap y PWA.
- SQLite o MySQL como base centralizada.
- Python, pandas, NumPy y statsmodels para el componente predictivo.
