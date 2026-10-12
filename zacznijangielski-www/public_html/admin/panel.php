<?php
require __DIR__ . '/../app/lib.php';
require __DIR__ . '/../app/admin_ui.php';

$admin = require_admin();
$schema = require __DIR__ . '/../app/schema.php';
$tab = (string) ($_GET['tab'] ?? 'tresc');
$action = (string) ($_POST['action'] ?? '');

/* ---------- pomocnicze: ścieżki i nazwy pól ---------- */

function get_path(array $arr, string $path)
{
    foreach (explode('.', $path) as $k) {
        if (!is_array($arr) || !array_key_exists($k, $arr)) {
            return null;
        }
        $arr = $arr[$k];
    }
    return $arr;
}

function set_path(array &$arr, string $path, $value): void
{
    $ref = &$arr;
    foreach (explode('.', $path) as $k) {
        if (!isset($ref[$k]) || !is_array($ref[$k])) {
            $ref[$k] = [];
        }
        $ref = &$ref[$k];
    }
    $ref = $value;
}

function input_name(string $root, string $path): string
{
    return $root . '[' . implode('][', explode('.', $path)) . ']';
}

/* ---------- czyszczenie danych z formularza ---------- */

function clean_value(string $type, $v)
{
    if ($type === 'bool') {
        return !empty($v);
    }
    $s = is_string($v) ? str_replace("\r\n", "\n", trim($v)) : '';
    $s = mb_substr($s, 0, 5000);
    switch ($type) {
        case 'text':
        case 'select':
            return one_line($s);
        case 'url':
            return ($s === '' || preg_match('#^https?://#i', $s)) ? one_line($s) : '';
        case 'image':
            return preg_match('#^(assets|uploads)/[A-Za-z0-9._-]+$#', $s) ? $s : '';
        default:
            return $s;
    }
}

function clean_field(array $f, $posted)
{
    [, , $type] = $f;
    $opt = $f[3] ?? [];
    if ($type === 'strlist') {
        $out = [];
        foreach ((array) $posted as $v) {
            $v = clean_value('text', $v);
            if ($v !== '') {
                $out[] = $v;
            }
        }
        return $out;
    }
    if ($type === 'objlist') {
        $out = [];
        foreach ((array) $posted as $item) {
            $row = [];
            $filled = false;
            foreach ($opt['sub'] as $sf) {
                $val = clean_value($sf[2], $item[$sf[0]] ?? '');
                if ($sf[2] === 'select' && !array_key_exists($val, $sf[3]['options'])) {
                    $val = array_key_first($sf[3]['options']);
                }
                $row[$sf[0]] = $val;
                if ($sf[2] !== 'select' && $val !== '') {
                    $filled = true;
                }
            }
            if ($filled) {
                $out[] = $row;
            }
        }
        return $out;
    }
    if ($type === 'select' && !array_key_exists((string) $posted, $opt['options'] ?? [])) {
        return array_key_first($opt['options']);
    }
    return clean_value($type, $posted);
}

/* ---------- upload zdjęć ---------- */

// Przyjmuje JPG/PNG/WEBP do 3 MB, przepisuje obraz przez GD (usuwa EXIF
// i ewentualne doklejone dane), zmniejsza do 1600 px. Zwraca ścieżkę albo null.
function store_upload(string $tmp, int $size, ?string &$err): ?string
{
    if ($size > 3 * 1024 * 1024) {
        $err = 'Zdjęcie jest za duże (maks. 3 MB).';
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || !getimagesize($tmp)) {
        $err = 'Dozwolone formaty zdjęć: JPG, PNG, WEBP.';
        return null;
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $name;

    if (function_exists('imagecreatefromstring')) {
        $img = @imagecreatefromstring((string) file_get_contents($tmp));
        if (!$img) {
            $err = 'Nie udało się odczytać zdjęcia.';
            return null;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w > 1600) {
            $nh = (int) round($h * 1600 / $w);
            $scaled = imagecreatetruecolor(1600, $nh);
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
            imagecopyresampled($scaled, $img, 0, 0, 0, 0, 1600, $nh, $w, $h);
            imagedestroy($img);
            $img = $scaled;
        }
        imagesavealpha($img, true);
        $ok = match ($ext) {
            'jpg' => imagejpeg($img, $dest, 85),
            'png' => imagepng($img, $dest, 7),
            'webp' => function_exists('imagewebp') ? imagewebp($img, $dest, 85) : false,
        };
        imagedestroy($img);
        if (!$ok) {
            $err = 'Nie udało się zapisać zdjęcia.';
            return null;
        }
    } elseif (!move_uploaded_file($tmp, $dest)) {
        $err = 'Nie udało się zapisać zdjęcia.';
        return null;
    }
    return 'uploads/' . $name;
}

// $_FILES['img'] ma strukturę lustrzaną do $_POST['c']; wstawia ścieżki
// nowych zdjęć w odpowiednie miejsca $post.
function apply_uploads(array &$post, array $names, array $tmps, array $sizes, array $errors, array &$msgs): void
{
    foreach ($names as $k => $n) {
        if (is_array($n)) {
            if (!isset($post[$k]) || !is_array($post[$k])) {
                $post[$k] = [];
            }
            apply_uploads($post[$k], $n, $tmps[$k], $sizes[$k], $errors[$k], $msgs);
        } elseif ($errors[$k] === UPLOAD_ERR_OK && is_uploaded_file($tmps[$k])) {
            $err = null;
            $path = store_upload($tmps[$k], (int) $sizes[$k], $err);
            if ($path) {
                $post[$k] = $path;
            } else {
                $msgs[] = $n . ': ' . $err;
            }
        } elseif ($errors[$k] !== UPLOAD_ERR_NO_FILE) {
            $msgs[] = $n . ': nie udało się przesłać pliku (może jest za duży).';
        }
    }
}

/* ---------- akcje ---------- */

$flash = ['ok' => [], 'err' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $flash['err'][] = 'Sesja wygasła. Odśwież stronę i spróbuj ponownie (zmiany nie zostały zapisane).';
    } elseif ($action === 'save') {
        $post = (array) ($_POST['c'] ?? []);
        $msgs = [];
        if (!empty($_FILES['img']['name']) && is_array($_FILES['img']['name'])) {
            $f = $_FILES['img'];
            apply_uploads($post, $f['name'], $f['tmp_name'], $f['size'], $f['error'], $msgs);
        }
        $content = content();
        foreach ($schema as $section) {
            foreach ($section['fields'] as $field) {
                set_path($content, $field[0], clean_field($field, get_path($post, $field[0])));
            }
        }
        if (save_content($content)) {
            $flash['ok'][] = 'Zapisano. Zmiany są już widoczne na stronie.';
        } else {
            $flash['err'][] = 'Nie udało się zapisać (sprawdź uprawnienia katalogu data/).';
        }
        $flash['err'] = array_merge($flash['err'], $msgs);
    } elseif ($action === 'restore') {
        $file = basename((string) ($_POST['file'] ?? ''));
        $path = DATA_DIR . '/backup/' . $file;
        $data = (preg_match('/^content-[0-9]{8}-[0-9]{6}-[a-f0-9]{4}\.json$/', $file) && is_file($path))
            ? json_decode((string) file_get_contents($path), true) : null;
        if (is_array($data) && save_content($data)) {
            $flash['ok'][] = 'Przywrócono wersję z ' . date('d.m.Y H:i', filemtime($path)) . '.';
        } else {
            $flash['err'][] = 'Nie udało się przywrócić tej wersji.';
        }
    } elseif ($action === 'delete_lead') {
        $id = (string) ($_POST['id'] ?? '');
        update_json('leads.json', function (&$leads) use ($id) {
            $leads = array_values(array_filter($leads, fn($l) => ($l['id'] ?? '') !== $id));
        });
        $flash['ok'][] = 'Usunięto zgłoszenie.';
    }
    $_SESSION['flash'] = $flash;
    header('Location: panel.php' . ($tab !== 'tresc' ? '?tab=' . rawurlencode($tab) : '') . ($action === 'save' ? '#top' : ''), true, 303);
    exit;
}

if ($tab === 'zgloszenia' && ($_GET['export'] ?? '') === 'csv' && csrf_ok()) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="zgloszenia-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Data', 'Imię i nazwisko', 'Kontakt', 'Poziom', 'Wiadomość'], ';', '"', '');
    foreach (read_json('leads.json', []) as $l) {
        $row = [$l['date'] ?? '', $l['name'] ?? '', $l['contact'] ?? '', $l['level'] ?? '', $l['message'] ?? ''];
        // Ochrona przed formułami w Excelu.
        $row = array_map(fn($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : $v, $row);
        fputcsv($out, $row, ';', '"', '');
    }
    exit;
}

$flash = $_SESSION['flash'] ?? $flash;
unset($_SESSION['flash']);

/* ---------- widok ---------- */

function render_scalar(string $type, string $name, $value, array $opt = [], string $imgName = ''): void
{
    switch ($type) {
        case 'textarea':
            echo '<textarea name="' . e($name) . '" rows="3">' . e($value) . '</textarea>';
            break;
        case 'url':
            echo '<input type="url" name="' . e($name) . '" value="' . e($value) . '" placeholder="https://">';
            break;
        case 'bool':
            echo '<input type="hidden" name="' . e($name) . '" value="">';
            echo '<label class="check"><input type="checkbox" name="' . e($name) . '" value="1"' . (!empty($value) ? ' checked' : '') . '> Tak</label>';
            break;
        case 'select':
            echo '<select name="' . e($name) . '">';
            foreach ($opt['options'] as $k => $label) {
                echo '<option value="' . e($k) . '"' . ((string) $value === (string) $k ? ' selected' : '') . '>' . e($label) . '</option>';
            }
            echo '</select>';
            break;
        case 'image':
            echo '<div class="img-field">';
            echo '<img src="' . e($value !== '' ? '../' . $value : 'data:,') . '" alt="">';
            echo '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
            echo '<div><input type="file" name="' . e($imgName) . '" accept="image/jpeg,image/png,image/webp">';
            echo '<div class="muted">JPG, PNG lub WEBP, do 3 MB. Wybierz plik, żeby podmienić.</div></div>';
            echo '</div>';
            break;
        default:
            echo '<input type="text" name="' . e($name) . '" value="' . e($value) . '">';
    }
}

function render_field(array $f, array $content): void
{
    [$path, $label, $type] = $f;
    $opt = $f[3] ?? [];
    $name = input_name('c', $path);
    $value = get_path($content, $path);
    echo '<div class="field">';
    echo '<span class="lbl">' . e($label) . '</span>';

    if ($type === 'strlist') {
        echo '<div class="list" data-kind="str">';
        foreach ((array) $value as $v) {
            echo '<div class="str-row"><input type="text" name="' . e($name) . '[]" value="' . e($v) . '">'
                . row_tools() . '</div>';
        }
        echo '</div>';
        echo '<template><div class="str-row"><input type="text" name="' . e($name) . '[]" value="">' . row_tools() . '</div></template>';
        echo '<button type="button" class="btn small ghost add" data-add>+ ' . e($opt['add'] ?? 'Dodaj') . '</button>';
    } elseif ($type === 'objlist') {
        echo '<div class="list" data-kind="obj">';
        foreach (array_values((array) $value) as $i => $item) {
            render_obj_item($path, (string) $i, $opt['sub'], (array) $item);
        }
        echo '</div>';
        echo '<template>';
        render_obj_item($path, '__IDX__', $opt['sub'], []);
        echo '</template>';
        echo '<button type="button" class="btn small ghost add" data-add>+ ' . e($opt['add'] ?? 'Dodaj') . '</button>';
    } else {
        render_scalar($type, $name, $value ?? '', $opt, input_name('img', $path));
    }
    echo '</div>';
}

function render_obj_item(string $path, string $idx, array $sub, array $item): void
{
    echo '<div class="list-item"><div class="row-tools">' . row_tools() . '</div>';
    foreach ($sub as $sf) {
        $p = $path . '.' . $idx . '.' . $sf[0];
        echo '<div class="field"><span class="lbl">' . e($sf[1]) . '</span>';
        render_scalar($sf[2], input_name('c', $p), $item[$sf[0]] ?? '', $sf[3] ?? [], input_name('img', $p));
        echo '</div>';
    }
    echo '</div>';
}

function row_tools(): string
{
    return '<button type="button" class="btn small ghost" data-up title="W górę">↑</button>'
        . '<button type="button" class="btn small ghost" data-down title="W dół">↓</button>'
        . '<button type="button" class="btn small danger" data-del title="Usuń">Usuń</button>';
}

admin_head($tab === 'zgloszenia' ? 'Zgłoszenia' : 'Treść strony', $admin);
?>
<div class="wrap" id="top">
  <?php foreach ($flash['ok'] as $m): ?><div class="msg ok"><?= e($m) ?></div><?php endforeach; ?>
  <?php foreach ($flash['err'] as $m): ?><div class="msg err"><?= e($m) ?></div><?php endforeach; ?>

<?php if ($tab === 'zgloszenia'):
    $leads = read_json('leads.json', []); ?>
  <div class="card">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
      <div><h2>Zgłoszenia z formularza</h2><p class="muted"><?= count($leads) ?> łącznie, najnowsze na górze.</p></div>
      <?php if ($leads): ?><a class="btn ghost" href="panel.php?tab=zgloszenia&amp;export=csv&amp;csrf=<?= e(csrf_token()) ?>">Pobierz CSV (Excel)</a><?php endif; ?>
    </div>
    <?php if (!$leads): ?>
      <p class="muted" style="margin-top:16px">Na razie brak zgłoszeń.</p>
    <?php else: ?>
    <div class="table-wrap" style="margin-top:12px"><table>
      <tr><th>Data</th><th>Osoba</th><th>Kontakt</th><th>Poziom</th><th>Wiadomość</th><th></th></tr>
      <?php foreach ($leads as $l): ?>
      <tr>
        <td><?= e($l['date'] ?? '') ?><?php if (empty($l['mail_sent'])): ?><br><span class="tag warn" title="Serwer nie wysłał maila – zgłoszenie jest tylko tutaj">bez maila</span><?php endif; ?></td>
        <td><b><?= e($l['name'] ?? '') ?></b></td>
        <td><?php $ct = (string) ($l['contact'] ?? '');
            if (filter_var($ct, FILTER_VALIDATE_EMAIL)): ?><a href="mailto:<?= e($ct) ?>"><?= e($ct) ?></a>
            <?php else: ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $ct)) ?>"><?= e($ct) ?></a><?php endif; ?></td>
        <td><?php if (!empty($l['level'])): ?><span class="tag"><?= e($l['level']) ?></span><?php endif; ?></td>
        <td class="msgcol"><?= e($l['message'] ?? '') ?></td>
        <td><form method="post" action="panel.php?tab=zgloszenia" onsubmit="return confirm('Usunąć to zgłoszenie?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete_lead"><input type="hidden" name="id" value="<?= e($l['id'] ?? '') ?>">
          <button class="btn small danger" type="submit">Usuń</button></form></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
    <?php endif; ?>
  </div>

<?php else:
    $content = content(); ?>
  <div class="toc">
    <?php foreach ($schema as $s): ?><a href="#s-<?= e($s['id']) ?>"><?= e($s['title']) ?></a><?php endforeach; ?>
    <a href="#s-wersje">Poprzednie wersje</a>
  </div>
  <p class="muted" style="margin:-6px 0 18px">W tekstach możesz użyć <b>**pogrubienia**</b> i linków: <code>[tekst](https://adres.pl)</code>.</p>

  <form method="post" action="panel.php" enctype="multipart/form-data" id="contentForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php foreach ($schema as $s): ?>
    <div class="card" id="s-<?= e($s['id']) ?>">
      <h2><?= e($s['title']) ?></h2>
      <?php if (!empty($s['hint'])): ?><p class="hint"><?= e($s['hint']) ?></p><?php endif; ?>
      <?php foreach ($s['fields'] as $f) { render_field($f, $content); } ?>
    </div>
    <?php endforeach; ?>
    <div class="savebar">
      <button class="btn" type="submit">Zapisz zmiany</button>
      <a class="btn ghost" href="../" target="_blank" rel="noopener">Zobacz stronę ↗</a>
      <span class="muted" id="dirty" hidden>Masz niezapisane zmiany</span>
    </div>
  </form>

  <div class="card" id="s-wersje" style="margin-top:18px">
    <h2>Poprzednie wersje</h2>
    <p class="hint">Przed każdym zapisem panel zachowuje kopię treści (ostatnie 20). Możesz wrócić do dowolnej.</p>
    <?php $bk = backups(); if (!$bk): ?><p class="muted">Brak zapisanych wersji.</p><?php endif; ?>
    <?php foreach (array_slice($bk, 0, 20) as $f): ?>
    <form method="post" action="panel.php" style="display:flex;gap:12px;align-items:center;margin-top:8px" onsubmit="return confirm('Przywrócić tę wersję? Obecna treść też zostanie zachowana jako kopia.')">
      <?= csrf_field() ?><input type="hidden" name="action" value="restore"><input type="hidden" name="file" value="<?= e(basename($f)) ?>">
      <span><?= e(date('d.m.Y H:i:s', filemtime($f))) ?></span>
      <button class="btn small ghost" type="submit">Przywróć</button>
    </form>
    <?php endforeach; ?>
  </div>

<script>
(function(){
  var form = document.getElementById('contentForm');
  var dirty = document.getElementById('dirty');
  var changed = false;
  function mark(){ changed = true; dirty.hidden = false; }
  form.addEventListener('input', mark);
  form.addEventListener('change', function(e){
    mark();
    // podgląd wybranego zdjęcia
    if (e.target.type === 'file' && e.target.files && e.target.files[0]) {
      var img = e.target.closest('.img-field').querySelector('img');
      img.src = URL.createObjectURL(e.target.files[0]);
    }
  });
  form.addEventListener('submit', function(){ changed = false; });
  window.addEventListener('beforeunload', function(e){ if (changed) { e.preventDefault(); e.returnValue = ''; } });

  var counter = 0;
  form.addEventListener('click', function(e){
    var b = e.target.closest('button');
    if (!b) return;
    if (b.hasAttribute('data-add')) {
      var tpl = b.previousElementSibling, list = tpl.previousElementSibling;
      var html = tpl.innerHTML.replace(/__IDX__/g, 'n' + Date.now() + (counter++));
      list.insertAdjacentHTML('beforeend', html);
      var first = list.lastElementChild.querySelector('input,textarea');
      if (first) first.focus();
      mark();
      return;
    }
    var row = b.closest('.list-item, .str-row');
    if (!row) return;
    if (b.hasAttribute('data-del')) { row.remove(); mark(); }
    if (b.hasAttribute('data-up') && row.previousElementSibling) { row.parentNode.insertBefore(row, row.previousElementSibling); mark(); }
    if (b.hasAttribute('data-down') && row.nextElementSibling) { row.parentNode.insertBefore(row.nextElementSibling, row); mark(); }
  });
})();
</script>
<?php endif; ?>
</div>
<?php admin_foot();
