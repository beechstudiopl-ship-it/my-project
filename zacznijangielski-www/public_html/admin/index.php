<?php
require __DIR__ . '/../app/lib.php';
require __DIR__ . '/../app/admin_ui.php';

start_session();
$action = (string) ($_POST['action'] ?? '');
$info = '';
$error = '';

if ($action === 'logout' && csrf_ok()) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

if (current_admin()) {
    header('Location: panel.php');
    exit;
}

// Krok 1: prośba o link logowania.
if ($action === 'link') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (!csrf_ok()) {
        $error = 'Sesja wygasła. Odśwież stronę i spróbuj ponownie.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Podaj poprawny adres e-mail.';
    } elseif (!rate_ok('login', 5, 900)) {
        $error = 'Za dużo prób. Spróbuj ponownie za kwadrans.';
    } else {
        if (is_admin_email($email)) {
            $token = bin2hex(random_bytes(32));
            update_json('tokens.json', function (&$tokens) use ($token, $email) {
                $now = time();
                foreach ($tokens as $h => $t) {
                    if (($t['exp'] ?? 0) < $now) {
                        unset($tokens[$h]);
                    }
                }
                $tokens[hash('sha256', $token)] = ['email' => $email, 'exp' => $now + 900];
            });
            $link = site_url('/admin/index.php?token=' . $token);
            send_mail(
                $email,
                'Link do panelu zacznijangielski',
                "Cześć!\n\nKliknij, żeby zalogować się do panelu strony:\n{$link}\n\n"
                . "Link działa 15 minut i tylko raz.\n"
                . "Jeśli to nie Ty prosiłeś o logowanie, zignoruj tę wiadomość.\n"
            );
        }
        // Ten sam komunikat niezależnie od tego, czy adres jest na liście.
        $info = 'Jeśli ten adres ma dostęp do panelu, za chwilę dostaniesz e-mail z linkiem do logowania. Sprawdź też folder spam.';
    }
}

// Krok 2: link z maila. GET pokazuje tylko przycisk, a logowanie dzieje się
// po POST, bo skanery poczty potrafią same „kliknąć” link i zużyć token.
$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
if ($token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    if ($action === 'use_token') {
        if (!csrf_ok()) {
            $error = 'Sesja wygasła. Kliknij link z maila jeszcze raz.';
        } else {
            $email = update_json('tokens.json', function (&$tokens) use ($token) {
                $h = hash('sha256', $token);
                $t = $tokens[$h] ?? null;
                unset($tokens[$h]);
                return ($t && ($t['exp'] ?? 0) >= time()) ? $t['email'] : null;
            });
            if ($email && is_admin_email($email)) {
                login_as($email);
                header('Location: panel.php');
                exit;
            }
            $error = 'Link wygasł albo został już użyty. Poproś o nowy.';
        }
    }
    if ($error === '') {
        admin_head('Logowanie'); ?>
        <div class="login"><div class="card">
          <h1>Zaloguj się</h1>
          <p class="muted" style="margin-bottom:18px">Potwierdź logowanie do panelu strony.</p>
          <form method="post" action="index.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="use_token">
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <button class="btn" type="submit" style="width:100%;justify-content:center">Wejdź do panelu →</button>
          </form>
        </div></div>
        <?php admin_foot();
        exit;
    }
}

if (isset($_GET['google_error'])) {
    $error = (string) $_GET['google_error'] === 'denied'
        ? 'To konto Google nie ma dostępu do panelu.'
        : 'Logowanie przez Google się nie udało. Spróbuj ponownie.';
}

admin_head('Logowanie'); ?>
<div class="login">
  <div class="card">
    <h1>Panel strony</h1>
    <p class="muted" style="margin-bottom:20px">zacznijangielski · edycja treści i zgłoszenia</p>
    <?php if ($info): ?><div class="msg ok"><?= e($info) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>

    <?php if (cfg('google_client_id')): ?>
    <a class="btn google" href="google.php?start=1">
      <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
      Zaloguj przez Google
    </a>
    <div class="or">albo linkiem na e-mail</div>
    <?php endif; ?>

    <form method="post" action="index.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="link">
      <div class="field" style="margin-top:0">
        <label for="email">Twój e-mail</label>
        <input id="email" name="email" type="email" required autocomplete="email" placeholder="np. dorota@gmail.com">
      </div>
      <button class="btn" type="submit" style="margin-top:14px;width:100%;justify-content:center">Wyślij link do logowania</button>
    </form>
  </div>
  <p class="muted" style="text-align:center"><a href="../">← wróć na stronę</a></p>
</div>
<?php admin_foot();
