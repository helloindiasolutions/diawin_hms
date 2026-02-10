<?php
/**
 * PHP Built-in Server Router
 * Run: php -S localhost:8000 server.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static assets directly
if (preg_match('/^\/assets\//', $uri)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath) && is_file($filePath)) {
        // Get MIME type
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'pdf' => 'application/pdf',
        ];
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$ext] ?? mime_content_type($filePath);
        
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000');
        readfile($filePath);
        return true;
    }
}

// Route everything else to public/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/public/index.php';
