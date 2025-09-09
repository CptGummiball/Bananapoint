<?php
// src/helpers.php
declare(strict_types=1);

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function parse_date(string $s): DateTime {
    return new DateTime($s);
}

function dates_between(DateTime $start, DateTime $end): array {
    $out = [];
    $cur = clone $start;
    while ($cur <= $end) {
        $out[] = clone $cur;
        $cur->modify('+1 day');
    }
    return $out;
}

function month_calendar(int $y, int $m): array {
    $c = [];
    $first = new DateTimeImmutable("$y-$m-01");
    $start = $first->modify('last monday');
    if ($start->format('n') == $m && $start->format('j') != '1') {
        // already in month but not first Monday -> adjust
        $start = $start->modify('-7 days');
    } elseif ($start->format('n') != $m) {
        // previous month monday, ok
    }
    $cur = $start;
    for ($week=0;$week<6;$week++) {
        $row = [];
        for ($d=0;$d<7;$d++) {
            $row[] = $cur;
            $cur = $cur->modify('+1 day');
        }
        $c[] = $row;
        if ($cur->format('n') != $m && $cur->format('N') == 1) break;
    }
    return $c;
}
