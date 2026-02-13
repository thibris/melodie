<?php
// Katalog z PDF-ami
$dir = __DIR__ . '/przepisy';

// Pobranie nazwy pliku z GET
$file = $_GET['file'] ?? '';

// Dekodowanie ewentualnego urlencodingu
$file = urldecode($file);

// Usunięcie ewentualnych ścieżek (zostawiamy tylko nazwę pliku)
$file = basename($file);

// Walidacja – pozwalamy na:
/// - dowolne znaki oprócz / i \
/// - rozszerzenie .pdf
if (!preg_match('/^[^\/\\\\]+\.pdf$/iu', $file)) {
    die("Nieprawidłowa nazwa pliku.");
}

$path = $dir . '/' . $file;

// Sprawdzenie czy plik istnieje
if (!file_exists($path)) {
    die("Plik nie istnieje.");
}

// Ustawienie nagłówków do wyświetlania PDF w przeglądarce
header('Content-Type: application/pdf');

// Prosta wersja Content-Disposition – większość współczesnych przeglądarek
// radzi sobie z UTF‑8 w nazwie pliku
header('Content-Disposition: inline; filename="' . $file . '"');

header('Content-Length: ' . filesize($path));

// Wysłanie pliku
readfile($path);
exit;

