<?php
/**
 * Ashabesuffa Foundation - Main Configuration
 */

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Karachi');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL (auto-detect)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$basePath = preg_replace('#/(admin|api)$#', '', $scriptDir);
define('BASE_URL', $protocol . '://' . $host . $basePath . '/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . $basePath . '/');

// Upload directory
define('UPLOAD_DIR', BASE_PATH . 'uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Site defaults
define('SITE_NAME', 'Ashabesuffa Foundation');
define('ADMIN_EMAIL', 'admin@ashabesuffa.org');

// Load database
require_once __DIR__ . '/database.php';

// Load functions
require_once BASE_PATH . 'includes/functions.php';
require_once BASE_PATH . 'includes/language.php';
require_once BASE_PATH . 'includes/security.php';
