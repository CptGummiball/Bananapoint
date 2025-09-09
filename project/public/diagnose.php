<?php
header('Content-Type: text/plain; charset=utf-8');
$tests = [
  'assets/icon-192.png',
  'assets/icon-512.png',
];
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo "SCRIPT_NAME:   " . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo "CWD:           " . getcwd() . "\n\n";

foreach ($tests as $rel) {
  $exists = file_exists($rel) ? 'YES' : 'NO';
  echo "[exists] $rel => $exists\n";
  if ($exists === 'YES') {
    echo "  realpath: " . realpath($rel) . "\n";
    echo "  size:     " . filesize($rel) . " bytes\n";
  }
}
echo "\nList icons/: \n";
foreach (@scandir('icons') ?: [] as $f) {
  if ($f === '.' || $f === '..') continue;
  echo " - $f\n";
}
