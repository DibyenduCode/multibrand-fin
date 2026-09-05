<?php
// Global Configuration Settings

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Multi-Brand Finance System');
define('APP_ENV', 'development');

// Determine base URL dynamically for XAMPP Apache and PHP CLI
if (php_sapi_name() === 'cli') {
    define('BASE_URL', 'http://localhost/fin');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = dirname($scriptName);
    
    // Normalize base directory by stripping /public suffix if present
    $baseDir = preg_replace('#/public$#', '', $scriptDir);
    if ($baseDir === '/' || $baseDir === '\\') {
        $baseDir = '';
    }
    
    $baseUrl = rtrim($protocol . $host . $baseDir, '/');
    define('BASE_URL', $baseUrl);
}

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'multibrand_fin');
define('DB_USER', 'root');
define('DB_PASS', '');

// Set default timezone
date_default_timezone_set('Asia/Kolkata');
