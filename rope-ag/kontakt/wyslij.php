<?php
/**
 * Formularz wyceny – Rope Access Group
 * Wysyła zapytanie ze zdjęciami na office@rope-ag.pl przez funkcję mail() serwera.
 * Działa z fetch() (odpowiedź JSON) i bez JavaScriptu (przekierowanie z komunikatem).
 */

// ---- Konfiguracja ----------------------------------------------------------
const MAIL_TO      = 'office@rope-ag.pl';
// Nadawca musi być w domenie serwera (SPF/DKIM), inaczej maile trafią do spamu.
const MAIL_FROM    = 'formularz@rope-ag.pl';
const MAIL_NAME    = 'Formularz rope-ag.pl';
const MAX_FILES    = 8;
const MAX_FILE_MB  = 10;
const MAX_TOTAL_MB = 25;
const ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/heic' => 'heic',
    'image/heif' => 'heif',
    'application/pdf' => 'pdf',
];
const THANKS_URL   = './?wyslano=1#formularz';
const ERROR_URL    = './?blad=1#formularz';
// ---------------------------------------------------------------------------

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function finish(bool $ok, string $msg, int $code = 200): never
{
    global $wantsJson;
    if ($wantsJson) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ' . ($ok ? THANKS_URL : ERROR_URL), true, 303);
    }
    exit;
}

function field(string $name, int $max = 500): string
{
    $v = trim((string)($_POST[$name] ?? ''));
    $v = str_replace("\0", '', $v);
    return mb_substr($v, 0, $max);
}

function oneLine(string $v): string
{
    // Chroni nagłówki maila przed wstrzyknięciem nowych linii.
    return trim(preg_replace('/[\r\n]+/', ' ', $v));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    finish(false, 'Nieprawidłowe żądanie.', 405);
}

// Pułapka na boty: pole ukryte przed ludźmi musi zostać puste.
if (field('website') !== '') {
    finish(true, 'Dziękujemy!');
}

$imie    = oneLine(field('imie', 120));
$telefon = oneLine(field('telefon', 40));
$email   = oneLine(field('email', 160));
$rodzaj  = oneLine(field('rodzaj', 120));
$adres   = oneLine(field('adres', 200));
$opis    = field('opis', 5000);
$zgoda   = isset($_POST['zgoda']);

if ($imie === '' || $telefon === '' || $opis === '') {
    finish(false, 'Uzupełnij imię, telefon i opis prac.', 422);
}
if (!preg_match('/^[0-9 +()\-]{6,}$/', $telefon)) {
    finish(false, 'Podaj poprawny numer telefonu.', 422);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    finish(false, 'Podaj poprawny adres e-mail albo zostaw to pole puste.', 422);
}
if (!$zgoda) {
    finish(false, 'Zaznacz zgodę na przetwarzanie danych.', 422);
}

// ---- Załączniki ------------------------------------------------------------
$attachments = [];
$files = $_FILES['zdjecia'] ?? null;
if ($files && is_array($files['name'])) {
    $count = 0;
    $total = 0;
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($files['name'] as $i => $origName) {
        $err = $files['error'][$i];
        if ($err === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            finish(false, 'Jedno ze zdjęć jest za duże (limit ' . MAX_FILE_MB . ' MB na plik).', 413);
        }
        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($files['tmp_name'][$i])) {
            finish(false, 'Nie udało się przesłać zdjęć. Spróbuj ponownie.', 400);
        }
        if (++$count > MAX_FILES) {
            finish(false, 'Możesz dodać maksymalnie ' . MAX_FILES . ' plików.', 413);
        }
        $size = (int)$files['size'][$i];
        $total += $size;
        if ($size > MAX_FILE_MB * 1024 * 1024 || $total > MAX_TOTAL_MB * 1024 * 1024) {
            finish(false, 'Zdjęcia są za duże (limit ' . MAX_FILE_MB . ' MB na plik, ' . MAX_TOTAL_MB . ' MB łącznie).', 413);
        }
        $mime = $finfo->file($files['tmp_name'][$i]) ?: '';
        if (!isset(ALLOWED_MIME[$mime])) {
            finish(false, 'Dozwolone są zdjęcia JPG, PNG, WEBP, HEIC oraz pliki PDF.', 415);
        }
        $base = pathinfo($origName, PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9_-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: 'zdjecie');
        $attachments[] = [
            'name' => trim($base, '-') . '-' . ($i + 1) . '.' . ALLOWED_MIME[$mime],
            'mime' => $mime,
            'data' => file_get_contents($files['tmp_name'][$i]),
        ];
    }
}

// ---- Treść maila -----------------------------------------------------------
$lines = [
    'Nowe zapytanie o wycenę ze strony rope-ag.pl',
    '',
    'Imię / firma:  ' . $imie,
    'Telefon:       ' . $telefon,
    'E-mail:        ' . ($email ?: '-'),
    'Rodzaj prac:   ' . ($rodzaj ?: '-'),
    'Adres obiektu: ' . ($adres ?: '-'),
    'Załączniki:    ' . count($attachments),
    '',
    'Opis prac:',
    $opis,
    '',
    '---',
    'Wysłano: ' . date('Y-m-d H:i') . ' · IP: ' . ($_SERVER['REMOTE_ADDR'] ?? '-'),
];
$text = implode("\r\n", $lines);

$subject = 'Wycena: ' . ($rodzaj ?: 'prace wysokościowe') . ' – ' . $imie;
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$headers = [
    'From: =?UTF-8?B?' . base64_encode(MAIL_NAME) . '?= <' . MAIL_FROM . '>',
    'MIME-Version: 1.0',
    'X-Mailer: rope-ag.pl',
];
if ($email !== '') {
    $headers[] = 'Reply-To: =?UTF-8?B?' . base64_encode($imie) . '?= <' . $email . '>';
}

if ($attachments) {
    $boundary = 'b_' . bin2hex(random_bytes(12));
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($text)) . "\r\n";
    foreach ($attachments as $a) {
        $body .= "--$boundary\r\n";
        $body .= 'Content-Type: ' . $a['mime'] . '; name="' . $a['name'] . "\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= 'Content-Disposition: attachment; filename="' . $a['name'] . "\"\r\n\r\n";
        $body .= chunk_split(base64_encode($a['data'])) . "\r\n";
    }
    $body .= "--$boundary--\r\n";
} else {
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: base64';
    $body = chunk_split(base64_encode($text));
}

$sent = mail(MAIL_TO, $encodedSubject, $body, implode("\r\n", $headers), '-f' . MAIL_FROM);

if (!$sent) {
    error_log('rope-ag formularz: mail() zwróciło false');
    finish(false, 'Nie udało się wysłać zapytania. Zadzwoń: +48 501 282 983 lub napisz na ' . MAIL_TO . '.', 500);
}

finish(true, 'Dziękujemy! Zapytanie dotarło. Odezwiemy się zwykle tego samego dnia roboczego.');
