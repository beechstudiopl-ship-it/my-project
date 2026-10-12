<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Warsaw');

const APP_DIR = __DIR__;
const ROOT_DIR = __DIR__ . '/..';
const DATA_DIR = ROOT_DIR . '/data';
const UPLOAD_DIR = ROOT_DIR . '/uploads';

function cfg(string $key, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require APP_DIR . '/config.php';
    }
    return $cfg[$key] ?? $default;
}

function site_url(string $path = ''): string
{
    return rtrim((string) cfg('site_url', ''), '/') . $path;
}

function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Escape + prosta składnia: **pogrubienie** i [tekst](https://link).
function fmt($s): string
{
    $h = e($s);
    $h = preg_replace('/\*\*(.+?)\*\*/u', '<b>$1</b>', $h);
    $h = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/u', function ($m) {
        return '<a href="' . $m[2] . '" target="_blank" rel="noopener">' . $m[1] . '</a>';
    }, $h);
    return $h;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/* ---------- pliki JSON ---------- */

function read_json(string $name, $default = [])
{
    $file = DATA_DIR . '/' . $name;
    if (!is_file($file)) {
        return $default;
    }
    $fh = fopen($file, 'rb');
    if (!$fh) {
        return $default;
    }
    flock($fh, LOCK_SH);
    $raw = stream_get_contents($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    $data = json_decode((string) $raw, true);
    return is_array($data) ? $data : $default;
}

function write_json(string $name, $data): bool
{
    $file = DATA_DIR . '/' . $name;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }
    return rename($tmp, $file);
}

// Odczyt-modyfikacja-zapis pod wyłączną blokadą (np. lista zgłoszeń).
function update_json(string $name, callable $fn, $default = [])
{
    $lock = fopen(DATA_DIR . '/.' . $name . '.lock', 'c');
    flock($lock, LOCK_EX);
    $data = read_json($name, $default);
    $result = $fn($data);
    write_json($name, $data);
    flock($lock, LOCK_UN);
    fclose($lock);
    return $result;
}

function content(): array
{
    static $c = null;
    if ($c === null) {
        $c = read_json('content.json', []);
    }
    return $c;
}

function save_content(array $content): bool
{
    $current = DATA_DIR . '/content.json';
    if (is_file($current)) {
        $dir = DATA_DIR . '/backup';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        copy($current, $dir . '/content-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');
        $old = glob($dir . '/content-*.json') ?: [];
        rsort($old);
        foreach (array_slice($old, 20) as $f) {
            @unlink($f);
        }
    }
    return write_json('content.json', $content);
}

function backups(): array
{
    $files = glob(DATA_DIR . '/backup/content-*.json') ?: [];
    rsort($files);
    return $files;
}

/* ---------- sesja, CSRF, logowanie ---------- */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('za_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    start_session();
    $sent = (string) ($_POST['csrf'] ?? $_GET['csrf'] ?? '');
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

function is_admin_email(string $email): bool
{
    $email = strtolower(trim($email));
    foreach ((array) cfg('admins', []) as $a) {
        if ($email !== '' && strtolower(trim((string) $a)) === $email) {
            return true;
        }
    }
    return false;
}

function login_as(string $email): void
{
    start_session();
    session_regenerate_id(true);
    $_SESSION['admin'] = strtolower($email);
    $_SESSION['admin_at'] = time();
    unset($_SESSION['csrf']);
}

function current_admin(): ?string
{
    start_session();
    $email = $_SESSION['admin'] ?? null;
    if (!$email || time() - (int) ($_SESSION['admin_at'] ?? 0) > 8 * 3600 || !is_admin_email($email)) {
        unset($_SESSION['admin'], $_SESSION['admin_at']);
        return null;
    }
    return $email;
}

function require_admin(): string
{
    $email = current_admin();
    if (!$email) {
        header('Location: index.php');
        exit;
    }
    return $email;
}

/* ---------- limity, mail ---------- */

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

// true, jeśli limit nie został przekroczony (i zalicza próbę).
function rate_ok(string $bucket, int $max, int $windowSec): bool
{
    $key = $bucket . ':' . hash('sha256', client_ip());
    return update_json('ratelimit.json', function (&$data) use ($key, $max, $windowSec) {
        $now = time();
        foreach ($data as $k => $hits) {
            $data[$k] = array_values(array_filter($hits, fn($t) => $t > $now - 86400));
            if (!$data[$k]) {
                unset($data[$k]);
            }
        }
        $recent = array_filter($data[$key] ?? [], fn($t) => $t > $now - $windowSec);
        if (count($recent) >= $max) {
            return false;
        }
        $data[$key][] = $now;
        return true;
    });
}

function one_line(string $s): string
{
    return trim(preg_replace('/[\r\n\t]+/', ' ', $s));
}

function send_mail($to, string $subject, string $body, ?string $replyTo = null): bool
{
    $from = (string) cfg('mail_from');
    $fromName = (string) cfg('mail_from_name', '');
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'From: ' . ($fromName !== '' ? mb_encode_mimeheader($fromName, 'UTF-8') . ' ' : '') . '<' . $from . '>',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    $ok = true;
    foreach ((array) $to as $addr) {
        if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) {
            continue;
        }
        $ok = mail(
            $addr,
            mb_encode_mimeheader(one_line($subject), 'UTF-8'),
            $body,
            implode("\r\n", $headers),
            filter_var($from, FILTER_VALIDATE_EMAIL) ? '-f' . $from : ''
        ) && $ok;
    }
    return $ok;
}
