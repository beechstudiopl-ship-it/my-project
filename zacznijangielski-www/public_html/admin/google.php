<?php
// Logowanie przez Google (OAuth 2.0, authorization code flow).
require __DIR__ . '/../app/lib.php';

start_session();
$clientId = (string) cfg('google_client_id', '');
$secret = (string) cfg('google_client_secret', '');
$redirect = site_url('/admin/google.php');

function back(string $err): void
{
    header('Location: index.php?google_error=' . rawurlencode($err));
    exit;
}

if ($clientId === '' || $secret === '') {
    back('config');
}

if (isset($_GET['start'])) {
    $_SESSION['g_state'] = bin2hex(random_bytes(16));
    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirect,
        'response_type' => 'code',
        'scope' => 'openid email',
        'state' => $_SESSION['g_state'],
        'prompt' => 'select_account',
    ]));
    exit;
}

$state = (string) ($_GET['state'] ?? '');
$expected = (string) ($_SESSION['g_state'] ?? '');
unset($_SESSION['g_state']);
if ($expected === '' || !hash_equals($expected, $state) || empty($_GET['code'])) {
    back('state');
}

function http_post_json(string $url, array $fields): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = is_string($res) ? json_decode($res, true) : null;
    return ($code === 200 && is_array($data)) ? $data : null;
}

function http_get_json(string $url, string $bearer): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $bearer],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = is_string($res) ? json_decode($res, true) : null;
    return ($code === 200 && is_array($data)) ? $data : null;
}

$tok = http_post_json('https://oauth2.googleapis.com/token', [
    'code' => (string) $_GET['code'],
    'client_id' => $clientId,
    'client_secret' => $secret,
    'redirect_uri' => $redirect,
    'grant_type' => 'authorization_code',
]);
if (!$tok || empty($tok['access_token'])) {
    back('token');
}

$user = http_get_json('https://openidconnect.googleapis.com/v1/userinfo', (string) $tok['access_token']);
$email = strtolower((string) ($user['email'] ?? ''));
if (!$user || empty($user['email_verified']) || !is_admin_email($email)) {
    back('denied');
}

login_as($email);
header('Location: panel.php');
