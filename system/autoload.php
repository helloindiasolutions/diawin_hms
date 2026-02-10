<?php
/**
 * Fallback Autoloader (when Composer not installed)
 */

declare(strict_types=1);

spl_autoload_register(function (string $class) {
    // PSR-4 namespace mappings
    $namespaces = [
        'App\\' => SRC_PATH . '/',
        'System\\' => SYSTEM_PATH . '/',
    ];

    foreach ($namespaces as $namespace => $directory) {
        if (strpos($class, $namespace) === 0) {
            $relativeClass = substr($class, strlen($namespace));
            $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';

            // Check formatted PSR-4 first
            if (file_exists($file)) {
                require_once $file;
                return true;
            }

            // Check with lowercase directories (for Linux compatibility)
            // e.g. App\Controllers\Auth -> src/controllers/Auth.php
            $parts = explode('\\', $relativeClass);
            $className = array_pop($parts);
            $folders = array_map('strtolower', $parts);
            $fileLower = $directory . implode('/', $folders) . '/' . $className . '.php';

            if (file_exists($fileLower)) {
                require_once $fileLower;
                return true;
            }
        }
    }

    return false;
});

// Load helper functions
require_once SYSTEM_PATH . '/helpers.php';
