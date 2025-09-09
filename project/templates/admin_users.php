<?php
// templates/admin_users.php
$title = 'Mitarbeiterverwaltung';
ob_start();
?>
<section class="grid cols-1 md-cols-2 gap">
  <div class="card">
    <h2>Neuen Mitarbeiter anlegen</h2>
    <form method="post" class="grid gap">
      <?= csrf_field() ?>
      <label>Name <input name="name" required></label>
      <label>E-Mail <input type="email" name="email" required></label>
      <label>Passwort
              <div class="row">
                <input type="password" name="password" id="pw-new" required>
                <button type="button" class="btn" onclick="togglePw('pw-new')">👁</button>
                <button type="button" class="btn" onclick="fillGenerated('pw-new')">🔒 Generieren</button>
              </div>
            </label>
      <label>Rolle
        <select name="role">
          <option value="employee">Mitarbeiter</option>
          <option value="manager">Berechtigt</option>
          <option value="admin">Admin</option>
        </select>
      </label>
      <button class="btn primary" name="create_user" value="1">Anlegen</button>
    </form>
  </div>
  <div class="card">
    <h2>Bestehende Mitarbeiter</h2>
    <ul class="list">
      <?php foreach ($users as $u): ?>
        <li>
          <div class="row between">
            <div>
              <strong><?= h($u['name']) ?></strong> – <?= h($u['email']) ?> <span class="chip"><?= h($u['role']) ?></span> <?= ((int)$u['is_active']===1?'':'<span class="chip warn">inaktiv</span>') ?>
            </div>
            <details>
              <summary>Aktionen</summary>
              <form method="post" class="row gap">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <label>Rolle
                  <select name="role">
                    <?php foreach (['employee'=>'Mitarbeiter','manager'=>'Berechtigt','admin'=>'Admin'] as $k=>$v): ?>
                      <option value="<?= $k ?>" <?= $u['role']===$k?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
                <label><input type="checkbox" name="is_active" <?= (int)$u['is_active']===1?'checked':'' ?>> aktiv</label>
                <button class="btn" name="update_role" value="1">Speichern</button>
              </form>
              <form method="post" class="row gap" style="flex-wrap:wrap">
                              <?= csrf_field() ?>
                              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                              <label>Neues Passwort
                                <div class="row">
                                  <?php $pwId = 'pw-reset-'.(int)$u['id']; ?>
                                  <input type="password" name="password" id="<?= $pwId ?>" required>
                                  <button type="button" class="btn" onclick="togglePw('<?= $pwId ?>')">👁</button>
                                  <button type="button" class="btn" onclick="fillGenerated('<?= $pwId ?>')">🔒 Generieren</button>
                                </div>
                              </label>
                              <button class="btn" name="reset_pw" value="1">Passwort setzen</button>
                            </form>
              <form method="post" onsubmit="return confirm('Mitarbeiter wirklich löschen?')">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button class="btn danger" name="delete_user" value="1">Löschen</button>
              </form>
            </details>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<script>
// Passwort sichtbar/unsichtbar
function togglePw(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.type = (el.type === 'password') ? 'text' : 'password';
  try { el.focus(); el.select(); } catch(e){}
}

// Kryptographisch starkes Passwort generieren
function generateStrongPassword(length = 16) {
  const lowers = 'abcdefghijklmnopqrstuvwxyz';
  const uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const digits = '0123456789';
  const symbols = '!@#$%^&*()-_=+[]{}.,:?';
  const all = lowers + uppers + digits + symbols;

  // Hilfsfunktion: sichere Zufallszahl
  function rand(max) {
    if (window.crypto && window.crypto.getRandomValues) {
      const a = new Uint32Array(1);
      window.crypto.getRandomValues(a);
      return a[0] % max;
    }
    return Math.floor(Math.random() * max); // Fallback
  }

  // Mind. je 1 aus jeder Klasse
  const pick = (set) => set[rand(set.length)];
  let pwd = [
    pick(lowers), pick(uppers), pick(digits), pick(symbols)
  ];

  // Rest auffüllen
  for (let i = pwd.length; i < length; i++) {
    pwd.push(all[rand(all.length)]);
  }

  // Mischen (Fisher–Yates)
  for (let i = pwd.length - 1; i > 0; i--) {
    const j = rand(i + 1);
    [pwd[i], pwd[j]] = [pwd[j], pwd[i]];
  }
  return pwd.join('');
}

function fillGenerated(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.value = generateStrongPassword(16);
  // kurz sichtbar machen, wenn es versteckt ist – optional
  if (el.type === 'password') {
    el.type = 'text';
    setTimeout(() => { el.type = 'password'; }, 2500);
  }
  try { el.focus(); el.select(); } catch(e){}
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
