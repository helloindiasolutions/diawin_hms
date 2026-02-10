<?php
/**
 * Database Configuration
 */

return [
    'default' => Env::get('DB_DRIVER', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Env::get('DB_HOST', 'localhost'),
            'port' => Env::get('DB_PORT', '3306'),
            'database' => Env::get('DB_DATABASE', ''),
            'username' => Env::get('DB_USERNAME', ''),
            'password' => Env::get('DB_PASSWORD', ''),
            'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => Env::get('DB_PREFIX', ''),
            'strict' => true,
            'engine' => 'InnoDB',
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => STORAGE_PATH . '/database.sqlite',
            'prefix' => '',
        ],
    ],
];
