<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/shifts.php';

$route = $_GET['route'] ?? 'dashboard';
verify_csrf();

// Auth routes
if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    include __DIR__ . '/../templates/login.php';
    exit;
}
if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($email, $password)) {
        redirect('/index.php?route=dashboard');
    } else {
        $error = 'Login fehlgeschlagen.';
        include __DIR__ . '/../templates/login.php';
    }
    exit;
}
if ($route === 'logout') {
    logout();
    redirect('/index.php?route=login');
}

// Protected routes
require_login();
$user = current_user();

if ($route === 'dashboard') {
    $now = new DateTime();
    $year = (int)($_GET['year'] ?? $now->format('Y'));
    $month = (int)($_GET['month'] ?? $now->format('n'));
    $view = $_GET['view'] ?? 'simple';
    $shifts = list_shifts_for_user_month((int)$user['id'], $year, $month);
    $first = sprintf('%04d-%02d-01', $year, $month);
    $lastDay = (new DateTime($first))->format('t');
    $absences = list_absences_for_user_between((int)$user['id'], $first, sprintf('%04d-%02d-%02d', $year, $month, $lastDay));
    include __DIR__ . '/../templates/dashboard.php';
    exit;
}

if ($route === 'shifts' && is_manager()) {
    $isManager = is_manager(); // true für manager/admin
    // Nutzer-Auswahl: Manager/Admin dürfen wählen; Mitarbeiter nur sich selbst
    $selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$user['id'];
    if (!$isManager) {
        $selected_user_id = (int)$user['id'];
    }

    $users = list_users(); // alle Nutzer (Manager/Admin sehen alle, UI filtert)
    $activities = list_activities();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create_shift'])) {
            $uid = (int)$_POST['user_id'];
            if (!$isManager) { $uid = (int)$user['id']; } // Safety
            $activity_id = !empty($_POST['activity_id']) ? (int)$_POST['activity_id'] : null;
            $start_dt = $_POST['start_datetime'];
            $end_dt = $_POST['end_datetime'];
            $notes = $_POST['notes'] ?? null;
            $copy_start = $_POST['copy_start'] ?? null;
            $copy_end = $_POST['copy_end'] ?? null;
            $dow = $_POST['dow'] ?? []; // array of 1..7 (Mon..Sun)

            if ($copy_start && $copy_end && !empty($dow)) {
                $ds = new DateTime($copy_start);
                $de = new DateTime($copy_end);
                $baseStart = new DateTime($start_dt);
                $baseEnd = new DateTime($end_dt);
                while ($ds <= $de) {
                    $weekday = (int)$ds->format('N'); // 1..7
                    if (in_array((string)$weekday, $dow, true)) {
                        $start = (clone $ds)->setTime((int)$baseStart->format('H'), (int)$baseStart->format('i'));
                        $end   = (clone $ds)->setTime((int)$baseEnd->format('H'), (int)$baseEnd->format('i'));
                        create_shift($uid, $activity_id, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), $notes, (int)$user['id']);
                    }
                    $ds->modify('+1 day');
                }
            } else {
                create_shift($uid, $activity_id, $start_dt, $end_dt, $notes, (int)$user['id']);
            }
            $msg = 'Dienst(e) gespeichert.';
        } elseif (isset($_POST['create_absence'])) {
            $uid = (int)$_POST['user_id'];
            if (!$isManager) { $uid = (int)$user['id']; } // Safety
            $type = $_POST['type'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $notes = $_POST['notes'] ?? null;
            create_absence($uid, $type, $start_date, $end_date, $notes, (int)$user['id']);
            $msg = 'Fehlzeit eingetragen.';
        } elseif (isset($_POST['delete_shift'])) {
            delete_shift((int)$_POST['shift_id']);
            $msg = 'Dienst gelöscht.';
        } elseif (isset($_POST['delete_absence'])) {
            delete_absence((int)$_POST['absence_id']);
            $msg = 'Fehlzeit gelöscht.';
        }
    }

    // ----- Filter: Monat aus GET (YYYY-MM), Default = aktueller Monat
    $ym = isset($_GET['m']) ? $_GET['m'] : (new DateTime('first day of this month'))->format('Y-m');

    try {
        // Anzeigegrenzen (inklusive)
        $periodStartDate = new DateTime($ym . '-01 00:00:00');
        $periodEndDate   = (clone $periodStartDate)->modify('last day of this month 23:59:59');

        // Query-Grenzen (halb-offen) – WICHTIG: clone vor modify!
        $from = $periodStartDate->format('Y-m-d H:i:s');
        $to   = (clone $periodStartDate)->modify('first day of next month')->format('Y-m-d H:i:s');
    } catch (Throwable $e) {
        $periodStartDate = new DateTime('first day of this month 00:00:00');
        $periodEndDate   = (clone $periodStartDate)->modify('last day of this month 23:59:59');
        $from = $periodStartDate->format('Y-m-d H:i:s');
        $to   = (clone $periodStartDate)->modify('first day of next month')->format('Y-m-d H:i:s');
        $ym = $periodStartDate->format('Y-m');
    }

    // Daten laden
    $sel_user = get_user($selected_user_id);

    // ✅ HIER wichtig: jetzt die Zeitstempel ($from/$to) verwenden
    $shifts = list_shifts_for_user_between($selected_user_id, $from, $to);

    // Absences bleiben Datum-inklusive
    $absences = list_absences_for_user_between(
        $selected_user_id,
        $periodStartDate->format('Y-m-d'),
        $periodEndDate->format('Y-m-d')
    );

    // Navigation
    $prevYm = (clone $periodStartDate)->modify('-1 month')->format('Y-m');
    $nextYm = (clone $periodStartDate)->modify('+1 month')->format('Y-m');

    // Fürs Template (Anzeige)
    $periodStart = $periodStartDate->format('Y-m-d');
    $periodEnd   = $periodEndDate->format('Y-m-d');

    include __DIR__ . '/../templates/shifts.php';
    exit;
}

if ($route === 'admin_users' && is_manager()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create_user'])) {
            create_user($_POST['name'], $_POST['email'], $_POST['password'], $_POST['role']);
            $msg = 'Mitarbeiter erstellt.';
        } elseif (isset($_POST['reset_pw'])) {
            reset_user_password((int)$_POST['user_id'], $_POST['password']);
            $msg = 'Passwort zurückgesetzt.';
        } elseif (isset($_POST['update_role'])) {
            update_user_role((int)$_POST['user_id'], $_POST['role'], isset($_POST['is_active']) ? 1 : 0);
            $msg = 'Rolle/Aktivität aktualisiert.';
        } elseif (isset($_POST['delete_user'])) {
            delete_user((int)$_POST['user_id']);
            $msg = 'Mitarbeiter gelöscht.';
        }
    }
    $users = list_users();
    include __DIR__ . '/../templates/admin_users.php';
    exit;
}

if ($route === 'admin_activities' && is_manager()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create_activity'])) {
            $name = trim($_POST['name']);
            $icon = $_POST['icon'] ?: null;
            create_activity($name, $icon);
            $msg = 'Tätigkeit hinzugefügt.';
        } elseif (isset($_POST['delete_activity'])) {
            delete_activity((int)$_POST['activity_id']);
            $msg = 'Tätigkeit gelöscht.';
        }
    }
    $activities = list_activities();
    $available_icons = array_values(array_filter(scandir(__DIR__ . '/assets/activities'), fn($f) => str_ends_with($f, '.png')));
    include __DIR__ . '/../templates/admin_activities.php';
    exit;
}

// fallback
http_response_code(404);
echo "Seite nicht gefunden.";
