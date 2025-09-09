<?php
// templates/admin_activities.php
$title = 'Tätigkeiten';
ob_start();
?>
<section class="grid cols-1 md-cols-2 gap">
  <div class="card">
    <h2>Neue Tätigkeit</h2>
    <form method="post" class="grid gap">
      <?= csrf_field() ?>
      <label>Name <input name="name" required></label>

      <fieldset>
        <legend>Icon</legend>
        <div class="icon-grid">
          <label class="icon-option">
            <input type="radio" name="icon" value="">
            <img src="/assets/activities/placeholder.png" alt="Ohne Icon" onerror="this.style.visibility='hidden'">
            <span>ohne Icon</span>
          </label>

          <?php foreach ($available_icons as $file):
              $name = pathinfo($file, PATHINFO_FILENAME);
          ?>
            <label class="icon-option">
              <input type="radio" name="icon" value="<?= h($file) ?>">
              <img src="/assets/activities/<?= h($file) ?>" alt="<?= h($name) ?>">
              <span><?= h($name) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <button class="btn primary" name="create_activity" value="1">Hinzufügen</button>
    </form>
  </div>

  <div class="card">
    <h2>Vorhandene Tätigkeiten</h2>
    <ul class="list">
      <?php foreach ($activities as $a): ?>
        <li class="row between">
          <div>
            <span class="chip">
              <img class="icon" src="/assets/activities/<?= h($a['icon'] ?: 'sunflower.png') ?>" alt="">
            </span>
            <strong><?= h($a['name']) ?></strong>
          </div>
          <form method="post" onsubmit="return confirm('Diese Tätigkeit löschen?')">
            <?= csrf_field() ?>
            <input type="hidden" name="activity_id" value="<?= (int)$a['id'] ?>">
            <button class="btn danger" name="delete_activity" value="1">Löschen</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<style>
/* Kleine, self-contained Styles für die Icon-Galerie */
.icon-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(96px,1fr));
  gap:12px;
}
.icon-option{
  display:flex;flex-direction:column;align-items:center;gap:6px;
  cursor:pointer; user-select:none;
}
.icon-option input{display:none}
.icon-option img{
  width:48px;height:48px;border:2px solid var(--border);
  border-radius:12px;padding:6px;background:#0f172a;object-fit:contain
}
.icon-option input:checked + img{
  border-color:var(--primary);
  background:rgba(14,165,233,.15)
}
.icon-option span{
  font-size:12px;color:var(--muted);text-align:center;max-width:90px;word-break:break-word
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
