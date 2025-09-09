<?php
// scripts/init_admin.php
declare(strict_types=1);
require_once __DIR__ . '/../src/db.php';

$email = $argv[1] ?? null;
$name  = $argv[2] ?? 'Admin';
$pass  = $argv[3] ?? null;

if (!$email || !$pass) {
    echo "Nutzung: php scripts/init_admin.php EMAIL NAME PASSWORT\n";
    exit(1);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$pdo = db();
$pdo->exec("INSERT INTO users (email, name, password_hash, role, is_active) VALUES (".$pdo->quote($email).", ".$pdo->quote($name).", ".$pdo->quote($hash).", 'admin', 1)");
echo "Admin-Benutzer erstellt: $email\n";
