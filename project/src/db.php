<?php
// src/db.php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = (int)($_ENV['DB_PORT'] ?? 3306);
    $name = $_ENV['DB_NAME'] ?? 'dienstplan';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);

    try {
            $pdo->exec("SET time_zone = 'Europe/Berlin'");
        } catch (PDOException $e) {
            // Fallback: aktuellen Offset der PHP-Zone ermitteln (+02:00 / +01:00)
            $offset = (new DateTime('now', new DateTimeZone('Europe/Berlin')))->format('P');
            $pdo->exec("SET time_zone = '$offset'");
        }

    return $pdo;
}
