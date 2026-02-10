<?php
/**
 * Application Configuration
 */

return [
    'name' => Env::get('APP_NAME', 'My Application'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::get('APP_DEBUG', false),
    'url' => Env::get('APP_URL', 'http://localhost'),
    'version' => Env::get('APP_VERSION', '1.0.0'),
    'timezone' => 'Asia/Kolkata',
    'locale' => 'en',
    
    'security' => [
        'csrf_enabled' => true,
        'csrf_single_use' => Env::get('CSRF_SINGLE_USE', true),
        'rate_limit_enabled' => Env::get('RATE_LIMIT_ENABLED', true),
        'rate_limit' => Env::get('RATE_LIMIT', 100),
        'rate_limit_window' => Env::get('RATE_LIMIT_WINDOW', 60),
        'max_input_length' => Env::get('MAX_INPUT_LENGTH', 10000),
        'blocked_ips' => array_filter(explode(',', Env::get('BLOCKED_IPS', ''))),
    ],
    
    'session' => [
        'name' => Env::get('SESSION_NAME', 'SECURE_SESSION'),
        'lifetime' => Env::get('SESSION_LIFETIME', 7200),
        'path' => Env::get('SESSION_PATH', '/'),
        'domain' => Env::get('SESSION_DOMAIN', ''),
        'samesite' => Env::get('SESSION_SAMESITE', 'Lax'),
        'regenerate_interval' => Env::get('SESSION_REGENERATE_INTERVAL', 300),
        'timeout' => Env::get('SESSION_TIMEOUT', 1800),
    ],
    
    'cors' => [
        'allowed_origins' => explode(',', Env::get('CORS_ALLOWED_ORIGINS', '*')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token'],
        'max_age' => 86400,
    ],
    
    'logging' => [
        'level' => Env::get('LOG_LEVEL', 'debug'),
        'path' => STORAGE_PATH . '/logs',
        'days_to_keep' => 30,
    ],
];
