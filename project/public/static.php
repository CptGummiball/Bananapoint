<?php
// public/static.php – TEMPORÄRER WORKAROUND
// Liefert Dateien aus public/icons & public/icons/activities sicher aus.

declare(strict_types=1);

$allowDirs = ['assets', 'assets/activities']; // Whitelist
$req = $_GET['f'] ?? '';
$req = ltrim($req, '/');

// Keine Pfadsprünge
if ($req === '' || str_contains($req, '..')) {
  http_response_code(400); exit('Bad request');
}

// Nur erlaubte Ordner
$ok = false;
foreach ($allowDirs as $d) {
  if (str_starts_with($req, $d . '/')) { $ok = true; break; }
}
if (!$ok) { http_response_code(403); exit('Forbidden'); }

$path = __DIR__ . '/' . $req;
if (!is_file($path)) { http_response_code(404); exit('Not found'); }

// Content-Type rudimentär bestimmen
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$types = [
  'png' => 'image/png',
  'jpg' => 'image/jpeg',
  'jpeg'=> 'image/jpeg',
  'gif' => 'image/gif',
  'svg' => 'image/svg+xml',
  'ico' => 'image/x-icon',
];
$ct = $types[$ext] ?? 'application/octet-stream';

// Caching-Header
$mtime = filemtime($path);
$etag  = '"' . md5($req . $mtime . filesize($path)) . '"';
header('ETag: ' . $etag);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
header('Cache-Control: public, max-age=604800'); // 7 Tage

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
  http_response_code(304); exit;
}

header('Content-Type: ' . $ct);
header('Content-Length: ' . filesize($path));
readfile($path);
