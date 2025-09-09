<?php
// src/shifts.php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

function list_users(): array {
    $stmt = db()->query("SELECT id, name, email, role, is_active FROM users ORDER BY name");
    return $stmt->fetchAll();
}
function get_user(int $id): ?array {
    $stmt = db()->prepare("SELECT id, name, email, role, is_active FROM users WHERE id=?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    return $r ?: null;
}
function create_user(string $name, string $email, string $password, string $role='employee'): int {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)");
    $stmt->execute([$name, $email, $hash, $role]);
    return (int)db()->lastInsertId();
}
function update_user_role(int $id, string $role, int $active=1): void {
    $stmt = db()->prepare("UPDATE users SET role=?, is_active=? WHERE id=?");
    $stmt->execute([$role, $active, $id]);
}
function reset_user_password(int $id, string $password): void {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare("UPDATE users SET password_hash=? WHERE id=?");
    $stmt->execute([$hash, $id]);
}
function delete_user(int $id): void {
    $stmt = db()->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
}

function list_activities(): array {
    $stmt = db()->query("SELECT id, name, icon FROM activities ORDER BY name");
    return $stmt->fetchAll();
}
function create_activity(string $name, ?string $icon): int {
    $stmt = db()->prepare("INSERT INTO activities (name, icon) VALUES (?,?)");
    $stmt->execute([$name, $icon]);
    return (int)db()->lastInsertId();
}
function delete_activity(int $id): void {
    $stmt = db()->prepare("DELETE FROM activities WHERE id=?");
    $stmt->execute([$id]);
}

function list_shifts_for_user_between(int $user_id, string $start, string $end): array {
    // Normalisieren: Datum-only -> 00:00:00, Enddatum -> +1 Tag (exklusiv)
    $fromDt = (strlen($start) === 10)
        ? new DateTimeImmutable("$start 00:00:00")
        : new DateTimeImmutable($start);

    $toDt = (strlen($end) === 10)
        ? (new DateTimeImmutable("$end 00:00:00"))->modify('+1 day')
        : new DateTimeImmutable($end);

    if ($toDt <= $fromDt) { [$fromDt, $toDt] = [$toDt, $fromDt]; }

    $stmt = db()->prepare("
        SELECT s.*, a.name AS activity_name, a.icon
        FROM shifts s
        LEFT JOIN activities a ON a.id = s.activity_id
        WHERE s.user_id = :uid
          AND s.start_datetime < :to
          AND s.end_datetime >= :from
        ORDER BY s.start_datetime
    ");
    $stmt->execute([
        ':uid'  => $user_id,
        ':to'   => $toDt->format('Y-m-d H:i:s'),
        ':from' => $fromDt->format('Y-m-d H:i:s'),
    ]);
    return $stmt->fetchAll();
}

function list_shifts_for_user_month(int $user_id, int $year, int $month): array {
    $from = new DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $year, $month));
    $to   = $from->modify('first day of next month'); // exklusiv
    return list_shifts_for_user_between(
        $user_id,
        $from->format('Y-m-d H:i:s'),
        $to->format('Y-m-d H:i:s')
    );
}

function create_shift(int $user_id, ?int $activity_id, string $start_dt, string $end_dt, ?string $notes, int $creator_id): int {
    $stmt = db()->prepare("INSERT INTO shifts (user_id, activity_id, start_datetime, end_datetime, notes, created_by)
                           VALUES (?,?,?,?,?,?)");
    $stmt->execute([$user_id, $activity_id, $start_dt, $end_dt, $notes, $creator_id]);
    return (int)db()->lastInsertId();
}
function delete_shift(int $id): void {
    $stmt = db()->prepare("DELETE FROM shifts WHERE id=?");
    $stmt->execute([$id]);
}

function list_absences_for_user_between(int $user_id, string $start, string $end): array {
    $stmt = db()->prepare("SELECT * FROM absences WHERE user_id=? AND start_date <= ? AND end_date >= ? ORDER BY start_date");
    $stmt->execute([$user_id, $end, $start]);
    return $stmt->fetchAll();
}
function create_absence(int $user_id, string $type, string $start_date, string $end_date, ?string $notes, int $creator_id): int {
    $stmt = db()->prepare("INSERT INTO absences (user_id, type, start_date, end_date, notes, created_by) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$user_id, $type, $start_date, $end_date, $notes, $creator_id]);
    return (int)db()->lastInsertId();
}
function delete_absence(int $id): void {
    $stmt = db()->prepare("DELETE FROM absences WHERE id=?");
    $stmt->execute([$id]);
}
