<?php
// templates/dashboard.php
$title = 'Mein Dienstplan';
ob_start();
$now = new DateTime();
$y = (int)($year ?? $now->format('Y'));
$m = (int)($month ?? $now->format('n'));
?>
<section class="card">
  <div class="flex between center">
    <h1>Hallo, <?= h($user['name']) ?></h1>
    <div class="view-switch">
      <a class="btn <?= $view==='simple'?'primary':'' ?>" href="/index.php?route=dashboard&view=simple">Einfache Ansicht</a>
      <a class="btn <?= $view==='calendar'?'primary':'' ?>" href="/index.php?route=dashboard&view=calendar">Kalender</a>
    </div>
  </div>

  <?php if ($view==='simple'): ?>
    <h2>Dienste</h2>
    <ul class="list">
      <?php
        // Nur Dienste zeigen, die heute sind oder in der Zukunft/gerade aktiv
        $todayStr = (new DateTime())->format('Y-m-d');
        $visibleShifts = array_filter($shifts, function($s) use ($todayStr, $now) {
            $start = new DateTime($s['start_datetime']);
            $end   = new DateTime($s['end_datetime']);
            // heute gestartet ODER noch nicht beendet (läuft oder liegt in der Zukunft)
            return $start->format('Y-m-d') === $todayStr || $end >= $now;
        });
      ?>

      <?php if (empty($visibleShifts)): ?>
        <li class="muted">Keine Einträge</li>
      <?php else: foreach ($visibleShifts as $s): ?>
        <li>
          <div class="row">
            <div>
              <strong><?= (new DateTime($s['start_datetime']))->format('d.m.Y H:i') ?>–<?= (new DateTime($s['end_datetime']))->format('H:i') ?></strong>
              <?php if (!empty($s['activity_name'])): ?>
                <span class="chip"><img class="icon" src="/assets/activities/<?= h($s['icon'] ?: 'sunflower.png') ?>" alt=""> <?= h($s['activity_name']) ?></span>
              <?php endif; ?>
              <?php if (!empty($s['notes'])): ?>
                <div class="muted"><?= h($s['notes']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </li>
      <?php endforeach; endif; ?>
    </ul>
    <h2>Fehlzeiten</h2>
    <ul class="list">
      <?php
        $today = new DateTime('today');
        $visibleAbsences = array_filter($absences, function($a) use ($today) {
            $end = new DateTime($a['end_date']);
            return $end >= $today; // endet heute oder später
        });
      ?>

      <?php if (empty($visibleAbsences)): ?>
        <li class="muted">Keine Einträge</li>
      <?php else: foreach ($visibleAbsences as $a): ?>
        <li>
          <strong><?= (new DateTime($a['start_date']))->format('d.m.Y') ?>–<?= (new DateTime($a['end_date']))->format('d.m.Y') ?></strong>
          <span class="chip"><?= h(ucfirst($a['type'])) ?></span>
          <?php if (!empty($a['notes'])): ?><div class="muted"><?= h($a['notes']) ?></div><?php endif; ?>
        </li>
      <?php endforeach; endif; ?>
    </ul>

  <?php else: ?>
    <?php
      // Aktuellen Monat/Jahr aus vorhandenen $y/$m ableiten
      $cur  = DateTime::createFromFormat('!Y-n', "$y-$m");
      $prev = (clone $cur)->modify('-1 month');
      $next = (clone $cur)->modify('+1 month');
    ?>
    <div class="flex between center">
      <a class="btn" href="/index.php?route=dashboard&view=calendar&year=<?= $prev->format('Y') ?>&month=<?= $prev->format('n') ?>">&#8592;</a>
      <h2 style="margin:0;">Kalender <?= sprintf('%02d.%04d', $m, $y) ?></h2>
      <a class="btn" href="/index.php?route=dashboard&view=calendar&year=<?= $next->format('Y') ?>&month=<?= $next->format('n') ?>">&#8594;</a>
    </div>

    <div class="calendar">
      <div class="cal-grid">
        <?php
        $days = ['Mo','Di','Mi','Do','Fr','Sa','So'];
        foreach ($days as $d) echo '<div class="cal-head">'.$d.'</div>';

        $grid = month_calendar($y, $m);

        // Map shifts by Y-m-d
        $byDay = [];
        foreach ($shifts as $s) {
            $dkey = (new DateTime($s['start_datetime']))->format('Y-m-d');
            $byDay[$dkey][] = $s;
        }

        // Map absences pro Tag
        $absByDay = [];
        foreach ($absences as $a) {
            $sd = new DateTime($a['start_date']);
            $ed = new DateTime($a['end_date']);
            for ($d = clone $sd; $d <= $ed; $d->modify('+1 day')) {
                $absByDay[$d->format('Y-m-d')][] = $a;
            }
        }

        foreach ($grid as $week) {
            foreach ($week as $day) {
                $dstr = $day->format('Y-m-d');
                $inMonth = ((int)$day->format('n') === $m);

                // Nur ausgewählten Monat zeigen: Zellen außerhalb leer/rendern, aber ohne Inhalt
                if (!$inMonth) {
                    echo '<div class="cal-cell empty"></div>';
                    continue;
                }

                echo '<div class="cal-cell">'; // keine "muted"-Klasse mehr
                echo '<div class="date">'.$day->format('j').'</div>';

                if (!empty($byDay[$dstr])) {
                    foreach ($byDay[$dstr] as $s) {
                        echo '<div class="tag"><img class="icon" src="/assets/activities/'.h($s['icon'] ?: 'sunflower.png').'" alt="">';
                        echo (new DateTime($s['start_datetime']))->format('H:i').'–'.(new DateTime($s['end_datetime']))->format('H:i');
                        if (!empty($s['activity_name'])) echo ' '.h($s['activity_name']);
                        echo '</div>';
                    }
                }
                if (!empty($absByDay[$dstr])) {
                    foreach ($absByDay[$dstr] as $a) {
                        echo '<div class="tag warn">'.h(ucfirst($a['type'])).'</div>';
                    }
                }
                echo '</div>';
            }
        }
        ?>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
