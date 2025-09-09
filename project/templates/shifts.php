<?php
// templates/shifts.php
$title = 'Dienste verwalten';
ob_start();
?>
<section class="grid cols-1 md-cols-2 gap">
  <div class="card">
    <h2>Dienst / Fehlzeit anlegen</h2>
    <form method="post" class="grid gap">
      <?= csrf_field() ?>
      <label>Mitarbeiter
        <select name="user_id" required <?= !is_manager() ? 'disabled' : '' ?>>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= $selected_user_id===(int)$u['id']?'selected':'' ?>>
              <?= h($u['name']) ?> (<?= h($u['email']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!is_manager()): ?>
          <input type="hidden" name="user_id" value="<?= (int)$selected_user_id ?>">
        <?php endif; ?>
      </label>

      <fieldset>
        <legend>Dienst</legend>
        <label>Tätigkeit
          <select name="activity_id">
            <option value="">–</option>
            <?php foreach ($activities as $a): ?>
              <option value="<?= (int)$a['id'] ?>"><?= h($a['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Start
          <input type="datetime-local" name="start_datetime" required>
        </label>
        <label>Ende
          <input type="datetime-local" name="end_datetime" required>
        </label>
        <label>Notiz
          <input type="text" name="notes" placeholder="optional">
        </label>
        <details>
          <summary>Dienst kopieren in Zeitraum</summary>
          <div class="grid cols-2 gap">
            <label>Von <input type="date" name="copy_start"></label>
            <label>Bis <input type="date" name="copy_end"></label>
          </div>
          <div>Wochentage:</div>
          <div class="weekdays">
            <?php foreach (['1'=>'Mo','2'=>'Di','3'=>'Mi','4'=>'Do','5'=>'Fr','6'=>'Sa','7'=>'So'] as $k=>$v): ?>
              <label><input type="checkbox" name="dow[]" value="<?= $k ?>"> <?= $v ?></label>
            <?php endforeach; ?>
          </div>
        </details>
        <button class="btn primary" name="create_shift" value="1">Dienst speichern</button>
      </fieldset>
    </form>
    <hr>
    <form method="post" class="grid gap">
      <?= csrf_field() ?>
      <input type="hidden" name="user_id" value="<?= (int)$selected_user_id ?>">
      <fieldset>
        <legend>Fehlzeit</legend>
        <label>Art
          <select name="type" required>
            <?php foreach (['urlaub'=>'Urlaub','krankheit'=>'Krankheit','sonderurlaub'=>'Sonderurlaub','sonstiges'=>'Sonstiges'] as $k=>$v): ?>
              <option value="<?= $k ?>"><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <div class="grid cols-2 gap">
          <label>Von <input type="date" name="start_date" required></label>
          <label>Bis <input type="date" name="end_date" required></label>
        </div>
        <label>Notiz
          <input type="text" name="notes">
        </label>
        <button class="btn" name="create_absence" value="1">Fehlzeit speichern</button>
      </fieldset>
    </form>
  </div>

  <div class="card">
    <div class="row between" style="align-items:flex-end">
      <div>
        <h2>Übersicht</h2>
        <div class="muted">
          Zeitraum: <?= (new DateTime($periodStart))->format('d.m.Y') ?> – <?= (new DateTime($periodEnd))->format('d.m.Y') ?>
        </div>
        <div class="muted">Ausgewählt: <?= h($sel_user['name'] ?? '') ?></div>
      </div>
      <form method="get" class="row gap" style="flex-wrap:wrap">
        <input type="hidden" name="route" value="shifts">
        <?php if (is_manager()): ?>
          <label style="display:flex;align-items:center;gap:6px">
            <span>Mitarbeiter</span>
            <select name="user_id">
              <?php foreach ($users as $u): ?>
                <option value="<?= (int)$u['id'] ?>" <?= $selected_user_id===(int)$u['id']?'selected':'' ?>><?= h($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        <?php endif; ?>
        <?php $ymValue = (new DateTime($periodStart))->format('Y-m'); ?>
        <label style="display:flex;align-items:center;gap:6px">
          <span>Monat</span>
          <input type="month" name="m" value="<?= h($ymValue) ?>">
        </label>
        <a class="btn" href="?route=shifts&user_id=<?= (int)$selected_user_id ?>&m=<?= h($prevYm) ?>">&laquo;</a>
        <a class="btn" href="?route=shifts&user_id=<?= (int)$selected_user_id ?>&m=<?= h($nextYm) ?>">&raquo;</a>
        <button class="btn">Anzeigen</button>
      </form>
    </div>

    <h3>Dienste</h3>
    <ul class="list">
      <?php if (empty($shifts)): ?><li class="muted">Keine Einträge</li><?php endif; ?>
      <?php foreach ($shifts as $s): ?>
        <li class="row between">
          <div>
            <strong><?= (new DateTime($s['start_datetime']))->format('d.m.Y H:i') ?>–<?= (new DateTime($s['end_datetime']))->format('H:i') ?></strong>
            <?php if (!empty($s['activity_name'])): ?>
              <span class="chip">
                <img class="icon" src="assets/activities/<?= h($s['icon'] ?: 'sunflower.png') ?>" alt="">
                <?= h($s['activity_name']) ?>
              </span>
            <?php endif; ?>
            <?php if (!empty($s['notes'])): ?><div class="muted"><?= h($s['notes']) ?></div><?php endif; ?>
          </div>
          <form method="post" onsubmit="return confirm('Diesen Dienst löschen?')">
            <?= csrf_field() ?>
            <input type="hidden" name="shift_id" value="<?= (int)$s['id'] ?>">
            <button class="btn danger" name="delete_shift" value="1">Löschen</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>

    <h3>Fehlzeiten</h3>
    <ul class="list">
      <?php if (empty($absences)): ?><li class="muted">Keine Einträge</li><?php endif; ?>
      <?php foreach ($absences as $a): ?>
        <li class="row between">
          <div>
            <strong><?= (new DateTime($a['start_date']))->format('d.m.Y') ?>–<?= (new DateTime($a['end_date']))->format('d.m.Y') ?></strong>
            <span class="chip"><?= h(ucfirst($a['type'])) ?></span>
            <?php if (!empty($a['notes'])): ?><div class="muted"><?= h($a['notes']) ?></div><?php endif; ?>
          </div>
          <form method="post" onsubmit="return confirm('Diese Fehlzeit löschen?')">
            <?= csrf_field() ?>
            <input type="hidden" name="absence_id" value="<?= (int)$a['id'] ?>">
            <button class="btn danger" name="delete_absence" value="1">Löschen</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';

