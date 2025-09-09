<?php
// src/config.php
declare(strict_types=1);

// Pfad zur .env (eine Ebene über public/)
$rootDir = dirname(__DIR__);
$envFile = $rootDir . DIRECTORY_SEPARATOR . '.env';

function load_env(string $file): array {
    $vars = [];
    if (!file_exists($file)) {
        return $vars;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $vars[$key] = $value;
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    return $vars;
}

load_env($envFile);

define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_SECRET', $_ENV['APP_SECRET'] ?? bin2hex(random_bytes(16)));

// Session Settings (Secure/HttpOnly)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token initialisieren
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string {
    return $_SESSION['csrf_token'] ?? '';
}
function csrf_field(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="'.$t.'">';
}
function verify_csrf(): void {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
  if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
      http_response_code(403);
      exit('Ungültiger CSRF-Token.');
  }
}
