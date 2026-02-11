<?php
// Archivo de configuración - NO commitear a repos públicos
// En producción, usar variables de entorno en lugar de valores hardcodeados

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'c2731928_sapro',
        'user' => getenv('DB_USER') ?: 'c2731928_sapro',
        'pass' => getenv('DB_PASS') ?: 'basovi95RU'
    ],
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'allowed_ext' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'dir' => 'imagenes'
    ]
];
?>
