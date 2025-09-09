<?php
// templates/layout.php
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? 'Bananapoint') ?></title>
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="icon" href="/assets/icon-192.png">
  <link rel="apple-touch-icon" href="/assets/icon-192.png">
  <meta name="theme-color" content="#0ea5e9">
  <link rel="stylesheet" href="/styles.css">
  <script defer src="/app.js"></script>
</head>
<body>
<header class="topbar">
  <div class="brand"><img src="/assets/icon-192.png" alt="Logo" class="logo"></div>
  <nav class="nav">
    <?php if (!empty($user)): ?>
      <a href="/index.php?route=dashboard">Mein Plan</a>
      <?php if (is_manager()): ?>
        <a href="/index.php?route=shifts">Dienste</a>
        <a href="/index.php?route=admin_users">Mitarbeiter</a>
        <a href="/index.php?route=admin_activities">Tätigkeiten</a>
      <?php endif; ?>
      <button id="btn-install" class="btn secondary mobile-only">Als App installieren</button>
      <button id="btn-shortcut" class="btn secondary desktop-only">Als Verknüpfung speichern</button>
      <a href="/index.php?route=logout" class="btn danger ml-auto">Logout</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
  <?php if (!empty($msg)): ?>
    <div class="alert success"><?= h($msg) ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert danger"><?= h($error) ?></div>
  <?php endif; ?>
  <?= $content ?? '' ?>
</main>
<footer class="footer">
  <small>&copy; <?= date('Y') ?> Bananapoint</small>
</footer>
</body>
</html>
