<?php
// public/init_admin_web.php
declare(strict_types=1);
require_once __DIR__ . '/../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($email && $name && $pass) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO users (email, name, password_hash, role, is_active) VALUES (?,?,?,?,1)");
        $stmt->execute([$email, $name, $hash, 'admin']);
        echo "<p style='color:green'>Admin-Benutzer erstellt: ".htmlspecialchars($email)."</p>";
    } else {
        echo "<p style='color:red'>Bitte alle Felder ausfüllen!</p>";
    }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Admin anlegen</title>
</head>
<body>
  <h1>Ersten Admin anlegen</h1>
  <form method="post">
    <label>E-Mail: <input type="email" name="email" required></label><br><br>
    <label>Name: <input type="text" name="name" required></label><br><br>
    <label>Passwort: <input type="password" name="password" required></label><br><br>
    <button type="submit">Admin erstellen</button>
  </form>
</body>
</html>