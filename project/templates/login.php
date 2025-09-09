<?php
// templates/login.php
$title = 'Login';
ob_start();
?>
<section class="card center">
  <h1>Willkommen</h1>
  <form method="post" action="/index.php?route=login" class="grid gap">
    <?= csrf_field() ?>
    <label>E-Mail
      <input type="email" name="email" required>
    </label>
    <label>Passwort
      <input type="password" name="password" required>
    </label>
    <button class="btn primary">Einloggen</button>
  </form>
</section>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
