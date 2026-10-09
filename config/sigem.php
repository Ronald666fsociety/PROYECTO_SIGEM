<?php

return [
    'seed_admin_email' => env('SIGEM_ADMIN_EMAIL', 'admin@sigem.local'),
    'seed_admin_password' => env('SIGEM_ADMIN_PASSWORD', 'Cambiar123!'),
    'python_executable' => env('SIGEM_PYTHON_EXECUTABLE', 'auto'),
    'allow_synthetic_data' => env('SIGEM_ALLOW_SYNTHETIC_DATA', false),
];
