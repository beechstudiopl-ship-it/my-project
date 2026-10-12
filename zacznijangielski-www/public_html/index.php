<?php
require __DIR__ . '/app/lib.php';

$c = content();
$hero = $c['hero'] ?? [];
$topics = $c['topics'] ?? [];
$biz = $c['business'] ?? [];
$price = $c['price'] ?? [];
$teacher = $c['teacher'] ?? [];
$partners = $c['partners'] ?? [];
$loc = $c['location'] ?? [];
$test = $c['test'] ?? [];
$su = $c['signup'] ?? [];
$levels = $su['levels'] ?? [];

$lat = (float) ($loc['map_lat'] ?? 54.262);
$lng = (float) ($loc['map_lng'] ?? 18.636);
$mapSrc = 'https://www.openstreetmap.org/export/embed.html?bbox='
    . rawurlencode(sprintf('%.4f,%.4f,%.4f,%.4f', $lng - 0.02, $lat - 0.01, $lng + 0.02, $lat + 0.01))
    . '&layer=mapnik&marker=' . rawurlencode(sprintf('%.4f,%.4f', $lat, $lng));

$sent = isset($_GET['wyslano']);
$formError = (string) ($_GET['blad'] ?? '');
$telHref = preg_replace('/[^0-9+]/', '', (string) ($loc['phone'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%231F3A8A'/%3E%3Ctext x='16' y='23' font-family='Arial,sans-serif' font-weight='900' font-size='20' text-anchor='middle' fill='%23FF8A3D'%3EA%3C/text%3E%3C/svg%3E">
<title><?= e($c['meta']['title'] ?? '') ?></title>
<meta name="description" content="<?= e($c['meta']['description'] ?? '') ?>">
<link rel="canonical" href="<?= e(site_url('/')) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="pl_PL">
<meta property="og:title" content="<?= e($c['meta']['title'] ?? '') ?>">
<meta property="og:description" content="<?= e($c['meta']['description'] ?? '') ?>">
<meta property="og:url" content="<?= e(site_url('/')) ?>">
<meta property="og:image" content="<?= e(site_url('/assets/og-image.jpg')) ?>">
<meta property="og:image:width" content="1600">
<meta property="og:image:height" content="1000">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800;900&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=<?= (int) @filemtime(__DIR__ . '/assets/style.css') ?>">
</head>
<body>

<nav>
  <div class="logo"><?= e($c['brand']['part1'] ?? '') ?><span><?= e($c['brand']['part2'] ?? '') ?></span></div>
  <div class="nav-links">
    <a href="#tematy">Tematy</a>
    <a href="#twoj-biznes">Dla firm</a>
    <a href="#cena">Cena</a>
    <a href="#prowadzaca">Prowadząca</a>
    <a href="#dojazd">Dojazd</a>
  </div>
  <a href="#zapis" class="btn btn-accent" style="padding:11px 20px;font-size:13px"><?= e($c['brand']['nav_cta'] ?? 'Zapisz się') ?></a>
</nav>

<div class="hero">
  <div class="hero-grid">
    <div>
      <span class="kicker"><?= e($hero['kicker'] ?? '') ?></span>
      <h1><?= e($hero['title'] ?? '') ?></h1>
      <p class="hero-desc"><?= fmt($hero['lead'] ?? '') ?></p>
      <div class="hero-actions">
        <a href="#zapis" class="btn btn-accent"><?= e($hero['cta_primary'] ?? '') ?><span>→</span></a>
        <a href="#tematy" class="btn btn-ghost"><?= e($hero['cta_secondary'] ?? '') ?></a>
      </div>
    </div>
    <div class="hero-art" aria-hidden="true">
      <div class="bubbles">
        <?php foreach ($hero['bubbles'] ?? [] as $b): ?>
        <div class="bubble <?= ($b['side'] ?? '') === 'you' ? 'you' : 'them' ?>"><small><?= e($b['who'] ?? '') ?></small><?= e($b['text'] ?? '') ?></div>
        <?php endforeach; ?>
      </div>
      <div class="hero-badges">
        <?php foreach ($hero['badges'] ?? [] as $b): ?>
        <div class="badge"><b><?= e($b['value'] ?? '') ?></b><span><?= e($b['label'] ?? '') ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<section id="tematy">
  <div class="section-inner">
    <div class="section-label"><?= e($topics['label'] ?? '') ?></div>
    <h2><?= e($topics['title'] ?? '') ?></h2>
    <p class="section-sub"><?= fmt($topics['sub'] ?? '') ?></p>

    <div class="offer-grid">
      <?php foreach ($topics['items'] ?? [] as $i => $t): ?>
      <div class="offer-card">
        <span class="offer-n"><?= sprintf('%02d', $i + 1) ?></span>
        <h3><?= e($t['title'] ?? '') ?></h3>
        <p><?= fmt($t['text'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="twoj-biznes">
  <div class="section-inner">
    <div class="biz-grid">
      <div>
        <div class="section-label"><?= e($biz['label'] ?? '') ?></div>
        <h2><?= e($biz['title'] ?? '') ?></h2>
        <ul class="biz-qs">
          <?php foreach ($biz['questions'] ?? [] as $q): ?>
          <li><?= e($q) ?></li>
          <?php endforeach; ?>
        </ul>
        <p class="section-sub"><?= fmt($biz['note'] ?? '') ?></p>
      </div>
      <div style="align-self:center">
        <div class="pull"><?= e($biz['quote'] ?? '') ?></div>
        <p class="pull-note"><?= fmt($biz['quote_note'] ?? '') ?></p>
      </div>
    </div>
  </div>
</section>

<section id="cena">
  <div class="section-inner">
    <div class="section-label"><?= e($price['label'] ?? '') ?></div>
    <h2><?= e($price['title'] ?? '') ?></h2>

    <div class="price-card">
      <div>
        <div class="price-amount"><?= e($price['amount'] ?? '') ?><span><?= e($price['period'] ?? '') ?></span></div>
        <div class="price-per"><?= e($price['per'] ?? '') ?></div>
        <a href="#zapis" class="btn"><?= e($price['cta'] ?? '') ?><span>→</span></a>
      </div>
      <ul class="price-list">
        <?php foreach ($price['points'] ?? [] as $p): ?>
        <li><?= e($p) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section id="prowadzaca">
  <div class="section-inner">
    <div class="about-grid">
      <div class="about-photo">
        <?php if (!empty($teacher['photo'])): ?>
        <img src="<?= e($teacher['photo']) ?>" alt="<?= e($teacher['name'] ?? '') ?>" loading="lazy">
        <?php endif; ?>
      </div>
      <div>
        <div class="section-label"><?= e($teacher['label'] ?? '') ?></div>
        <h2><?= e($teacher['name'] ?? '') ?></h2>
        <p class="section-sub" style="max-width:600px"><?= nl2br(fmt($teacher['bio'] ?? '')) ?></p>
      </div>
    </div>

    <?php if (!empty($partners['enabled'])): ?>
    <div class="partners">
      <div class="partners-text">
        <div class="section-label"><?= e($partners['label'] ?? '') ?></div>
        <h3><?= e($partners['title'] ?? '') ?></h3>
        <p><?= fmt($partners['text'] ?? '') ?></p>
      </div>
      <div class="partners-logos">
        <?php foreach ($partners['items'] ?? [] as $i => $p): ?>
        <?php if ($i > 0): ?><span class="partners-plus" aria-hidden="true">+</span><?php endif; ?>
        <a class="partner-logo" href="<?= e($p['url'] ?? '#') ?>" target="_blank" rel="noopener"><img src="<?= e($p['logo'] ?? '') ?>" alt="<?= e($p['name'] ?? '') ?>"></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<section id="dojazd">
  <div class="section-inner">
    <div class="section-label"><?= e($loc['label'] ?? '') ?></div>
    <h2><?= e($loc['title'] ?? '') ?></h2>

    <div class="loc-grid">
      <div class="map-frame">
        <iframe src="<?= e($mapSrc) ?>" loading="lazy" title="Mapa: <?= e($loc['place'] ?? '') ?>"></iframe>
      </div>
      <div class="loc-info">
        <div class="loc-row"><span class="loc-row-icon">📍</span><span><b>Miejsce</b><span><?= e($loc['place'] ?? '') ?><?php if (!empty($loc['address'])): ?><br><?= e($loc['address']) ?><?php endif; ?></span></span></div>
        <?php if (!empty($loc['schedule'])): ?>
        <div class="loc-row"><span class="loc-row-icon">🗓️</span><span><b>Rytm</b><span><?= e($loc['schedule']) ?></span></span></div>
        <?php endif; ?>
        <?php if (!empty($loc['contact_name'])): ?>
        <div class="contact-person">
          <?php if (!empty($loc['contact_photo'])): ?>
          <img src="<?= e($loc['contact_photo']) ?>" alt="<?= e($loc['contact_name']) ?>" width="72" height="72" loading="lazy">
          <?php endif; ?>
          <div><b><?= e($loc['contact_name']) ?></b><span><?= e($loc['contact_role'] ?? '') ?></span></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($loc['phone'])): ?>
        <div class="loc-row"><span class="loc-row-icon">📞</span><span><b>Telefon</b><span><a href="tel:<?= e($telHref) ?>"><?= e($loc['phone']) ?></a></span></span></div>
        <?php endif; ?>
        <?php if (!empty($loc['email'])): ?>
        <div class="loc-row"><span class="loc-row-icon">✉️</span><span><b>E-mail</b><span><a href="mailto:<?= e($loc['email']) ?>"><?= e($loc['email']) ?></a></span></span></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($test['enabled'])): ?>
<section id="test" class="test-section">
  <div class="section-inner">
    <div class="test-card">
      <div class="test-text">
        <span class="soon-badge"><?= e($test['badge'] ?? '') ?></span>
        <h2><?= e($test['title'] ?? '') ?></h2>
        <p class="section-sub"><?= fmt($test['sub'] ?? '') ?></p>
        <p class="test-meanwhile"><?= fmt($test['meanwhile'] ?? '') ?></p>
        <a href="#zapis" class="btn btn-accent" id="testToForm"><?= e($test['cta'] ?? '') ?><span>→</span></a>
      </div>
      <div class="quiz-mock" aria-hidden="true">
        <div class="quiz-top"><span>Pytanie 3 / 20</span><span class="quiz-lock">🔒 wkrótce</span></div>
        <div class="quiz-bar"><span></span></div>
        <p class="quiz-q">Could you ______ me the report by Friday?</p>
        <div class="quiz-opts">
          <span class="quiz-opt">sending</span>
          <span class="quiz-opt is-right">send</span>
          <span class="quiz-opt">sent</span>
          <span class="quiz-opt">to send</span>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section id="zapis">
  <div class="section-inner">
    <div class="section-label"><?= e($su['label'] ?? '') ?></div>
    <h2><?= e($su['title'] ?? '') ?></h2>
    <p class="section-sub"><?= fmt($su['sub'] ?? '') ?></p>

    <div class="book-box">
      <form id="bookMain" action="wyslij.php" method="post"<?= $sent ? ' style="display:none"' : '' ?>>
        <div class="book-grid">
          <fieldset class="level-field">
            <legend><?= e($su['levels_label'] ?? '') ?></legend>
            <div class="level-pick">
              <?php foreach ($levels as $l): ?>
              <label class="lvl-opt">
                <input type="radio" name="level" value="<?= e($l['name'] ?? '') ?>">
                <span><?= e($l['name'] ?? '') ?></span><span class="lvl-opt-hint"><?= e($l['hint'] ?? '') ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </fieldset>
          <div class="book-form">
            <div>
              <label for="f-name">Imię i nazwisko</label>
              <input id="f-name" name="name" type="text" placeholder="np. Anna Kowalska" maxlength="100" autocomplete="name" required>
            </div>
            <div>
              <label for="f-contact">Telefon lub e-mail</label>
              <input id="f-contact" name="contact" type="text" placeholder="np. 500 000 000" maxlength="120" required>
            </div>
            <div>
              <label for="f-msg">Wiadomość (opcjonalnie)</label>
              <textarea id="f-msg" name="message" rows="3" maxlength="2000" placeholder="<?= e($su['message_placeholder'] ?? '') ?>"></textarea>
            </div>
            <div class="hp" aria-hidden="true">
              <label for="f-website">Strona www</label>
              <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>
            <input type="hidden" name="ts" value="<?= time() ?>">
            <label class="consent" for="f-consent">
              <input id="f-consent" name="consent" value="1" type="checkbox" required>
              <span><?= fmt($su['consent'] ?? '') ?></span>
            </label>
            <p class="book-error" id="bookError" role="alert"<?= $formError === '' ? ' hidden' : '' ?>><?= e($formError) ?></p>
            <button type="submit" class="btn btn-accent book-submit"><?= e($su['button'] ?? 'Wyślij') ?><span>→</span></button>
            <p class="book-note"><?= fmt($su['note'] ?? '') ?></p>
          </div>
        </div>
      </form>
      <div class="book-done<?= $sent ? ' show' : '' ?>" id="bookDone" role="status">
        <h3><?= e($su['done_title'] ?? '') ?></h3>
        <p><?= fmt($su['done_text'] ?? '') ?></p>
      </div>
    </div>
  </div>
</section>

<footer>
  <span><?= fmt($c['footer']['left'] ?? '') ?></span>
  <span><?= fmt($c['footer']['right'] ?? '') ?></span>
</footer>

<script>
  var testBtn = document.getElementById('testToForm');
  if (testBtn) testBtn.addEventListener('click', function(){
    var opts = document.querySelectorAll('.lvl-opt input');
    if (opts.length) opts[opts.length - 1].checked = true;
  });

  var form = document.getElementById('bookMain');
  var errBox = document.getElementById('bookError');
  form.addEventListener('submit', function(e){
    if (!window.fetch || !window.FormData) return;
    e.preventDefault();
    var btn = form.querySelector('.book-submit');
    btn.disabled = true;
    errBox.hidden = true;
    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } })
      .then(function(r){ return r.json(); })
      .then(function(res){
        if (res.ok) {
          form.style.display = 'none';
          document.getElementById('bookDone').classList.add('show');
        } else {
          errBox.textContent = res.error || 'Nie udało się wysłać zgłoszenia. Spróbuj ponownie.';
          errBox.hidden = false;
        }
      })
      .catch(function(){
        errBox.textContent = 'Brak połączenia. Spróbuj ponownie za chwilę.';
        errBox.hidden = false;
      })
      .then(function(){ btn.disabled = false; });
  });
</script>

</body>
</html>
