<?php
require __DIR__ . '/app/lib.php';

$wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

function respond(bool $ok, string $error = ''): void
{
    global $wantsJson;
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'error' => $error], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ./' . ($ok ? '?wyslano=1' : '?blad=' . rawurlencode($error)) . '#zapis', true, 303);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ./#zapis');
    exit;
}

$name = one_line((string) ($_POST['name'] ?? ''));
$contact = one_line((string) ($_POST['contact'] ?? ''));
$message = trim(str_replace("\r\n", "\n", (string) ($_POST['message'] ?? '')));
$level = one_line((string) ($_POST['level'] ?? ''));
$consent = ($_POST['consent'] ?? '') === '1';
$honeypot = trim((string) ($_POST['website'] ?? ''));
$ts = (int) ($_POST['ts'] ?? 0);

// Boty: wypełnione ukryte pole albo formularz wysłany szybciej niż w 3 s.
// Udajemy sukces, żeby nie podpowiadać, co zablokowało.
if ($honeypot !== '' || ($ts > 0 && time() - $ts < 3)) {
    respond(true);
}

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    respond(false, 'Podaj imię i nazwisko.');
}
if (mb_strlen($contact) < 5 || mb_strlen($contact) > 120) {
    respond(false, 'Podaj telefon albo adres e-mail, żebyśmy mogli się odezwać.');
}
if (mb_strlen($message) > 2000) {
    respond(false, 'Wiadomość jest za długa (maks. 2000 znaków).');
}
$allowedLevels = array_map(fn($l) => (string) ($l['name'] ?? ''), content()['signup']['levels'] ?? []);
if ($level !== '' && !in_array($level, $allowedLevels, true)) {
    $level = '';
}
if (!$consent) {
    respond(false, 'Zaznacz zgodę na przetwarzanie danych, żebyśmy mogli się z Tobą skontaktować.');
}
if (!rate_ok('lead', 10, 3600)) {
    respond(false, 'Wysłano już kilka zgłoszeń z tego adresu. Spróbuj ponownie za godzinę albo zadzwoń.');
}

$lead = [
    'id' => bin2hex(random_bytes(6)),
    'date' => date('Y-m-d H:i'),
    'name' => $name,
    'contact' => $contact,
    'level' => $level,
    'message' => $message,
    'mail_sent' => false,
];

$body = "Nowe zgłoszenie ze strony " . site_url('/') . "\n\n"
    . "Imię i nazwisko: {$name}\n"
    . "Kontakt: {$contact}\n"
    . "Poziom: " . ($level !== '' ? $level : '(nie wybrano)') . "\n"
    . "Data: {$lead['date']}\n\n"
    . "Wiadomość:\n" . ($message !== '' ? $message : '(brak)') . "\n\n"
    . "Wszystkie zgłoszenia: " . site_url('/admin/') . "\n";

$replyTo = filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : null;
$lead['mail_sent'] = send_mail(cfg('leads_to', []), 'Nowe zgłoszenie: ' . $name, $body, $replyTo);

update_json('leads.json', function (&$leads) use ($lead) {
    array_unshift($leads, $lead);
});

respond(true);
