(function () {
  // Mobile menu
  var btn = document.querySelector('.vtg-nav__menu');
  var menu = document.getElementById('mobile-menu');
  if (btn && menu) {
    btn.setAttribute('aria-controls', 'mobile-menu');
    btn.setAttribute('aria-expanded', 'false');
    btn.addEventListener('click', function () {
      var open = menu.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      document.documentElement.classList.toggle('menu-open', open);
      var hdr = menu.closest('.vtg-nav'); if (hdr) hdr.classList.toggle('menu-on', open);
      var use = btn.querySelector('use'); if (use) use.setAttribute('href', open ? '#i-close' : '#i-menu');
    });
    menu.addEventListener('click', function (e) {
      if (e.target.closest('a')) { menu.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); document.documentElement.classList.remove('menu-open'); var hdr = menu.closest('.vtg-nav'); if (hdr) hdr.classList.remove('menu-on'); var use = btn.querySelector('use'); if (use) use.setAttribute('href', '#i-menu'); }
    });
  }

  // Photo placeholders: show the expected file name while photos are missing
  document.querySelectorAll('.ph img').forEach(function (img) {
    function miss() { img.style.visibility = 'hidden'; img.closest('.ph').classList.add('is-missing'); }
    if (img.complete && img.naturalWidth === 0) miss();
    img.addEventListener('error', miss);
  });
})();

// Collapsibles: always open on desktop, closed on mobile until tapped
(function () {
  var mq = window.matchMedia('(max-width: 720px)');
  var items = document.querySelectorAll('details.mx');
  function sync() {
    items.forEach(function (d) {
      if (!mq.matches) { d.open = true; }
      else if (!d.dataset.touched) { d.open = false; }
    });
  }
  items.forEach(function (d) {
    var sum = d.querySelector('summary');
    if (sum) sum.addEventListener('click', function (e) {
      if (!mq.matches) { e.preventDefault(); return; }
      d.dataset.touched = '1';
    });
  });
  sync();
  if (mq.addEventListener) mq.addEventListener('change', sync); else mq.addListener(sync);
})();

// Step form: one question at a time; without JS all steps show and the form posts as is
(function () {
  var form = document.querySelector('form.wiz');
  if (!form) return;
  form.classList.add('is-js');
  var panels = form.querySelectorAll('.wiz__panel');
  var steps = form.querySelectorAll('.wiz__step');
  var back = form.querySelector('.wiz__back'), next = form.querySelector('.wiz__next'), send = form.querySelector('.wiz__send');
  var sum = form.querySelector('.wiz__sum');
  var cur = 1, last = panels.length;

  function label(name) {
    var c = form.querySelector('input[name="' + name + '"]:checked');
    return c ? c.parentNode.querySelector('.opt__t').textContent : '';
  }
  function show(n) {
    cur = n;
    panels.forEach(function (p) { p.hidden = +p.dataset.step !== n; });
    steps.forEach(function (s) {
      var k = +s.dataset.for;
      s.classList.toggle('is-current', k === n);
      s.classList.toggle('is-done', k < n);
      if (k === n) s.setAttribute('aria-current', 'step'); else s.removeAttribute('aria-current');
    });
    back.hidden = n === 1;
    next.hidden = n === last;
    send.hidden = n !== last;
    if (n === last && sum) sum.textContent = [label('type'), label('area')].filter(Boolean).join(' · ');
  }
  function valid(n) {
    var p = form.querySelector('.wiz__panel[data-step="' + n + '"]');
    var ok = Array.prototype.every.call(p.querySelectorAll('[required]'), function (el) {
      if (el.type === 'radio') return !!p.querySelector('input[name="' + el.name + '"]:checked');
      return el.value.trim() !== '';
    });
    p.querySelector('.wiz__err').hidden = ok;
    return ok;
  }
  next.addEventListener('click', function () { if (valid(cur)) { show(cur + 1); panels[cur - 1].querySelector('legend').focus(); } });
  back.addEventListener('click', function () { show(cur - 1); });
  form.addEventListener('submit', function (e) { if (!valid(cur)) e.preventDefault(); });
  // A tile choice on a tiles-only step moves on by itself
  form.querySelectorAll('.wiz__panel[data-step="1"] input[type=radio]').forEach(function (r) {
    r.addEventListener('change', function () { setTimeout(function () { if (cur === 1) { valid(1); show(2); } }, 180); });
  });
  form.querySelectorAll('legend').forEach(function (l) { l.tabIndex = -1; });
  show(1);
})();

// Version D service cards: the hovered or focused card turns blue
(function () {
  var cards = document.querySelectorAll('.dcard');
  if (!cards.length) return;
  function on(c) { cards.forEach(function (x) { x.classList.toggle('is-on', x === c); }); }
  cards.forEach(function (c) { c.addEventListener('mouseenter', function () { on(c); }); c.addEventListener('focus', function () { on(c); }); });
})();

// Sticky menu: black bar after the page leaves the top
(function () {
  var nav = document.querySelector('.vtg-nav--over');
  if (!nav) return;
  function sync() { nav.classList.toggle('is-scrolled', window.scrollY > 8); }
  sync();
  window.addEventListener('scroll', sync, { passive: true });
})();

// Contact page: area tabs and map zones
(function () {
  var tabs = document.querySelectorAll('.mp__tab');
  if (!tabs.length) return;
  function pick(r) {
    tabs.forEach(function (t) { var on = t.dataset.region === r; t.classList.toggle('is-on', on); t.setAttribute('aria-pressed', on ? 'true' : 'false'); });
    document.querySelectorAll('.mp__zone').forEach(function (z) { z.classList.toggle('is-on', z.dataset.region === r); });
    document.querySelectorAll('.mp__list').forEach(function (l) { l.hidden = l.dataset.region !== r; });
  }
  tabs.forEach(function (t) { t.addEventListener('click', function () { pick(t.dataset.region); }); });
  document.querySelectorAll('.mp__zone').forEach(function (z) { z.addEventListener('click', function () { pick(z.dataset.region); }); });
  pick('central');
})();

// Projects page: filter by type of work
(function () {
  var btns = document.querySelectorAll('.pjf');
  if (!btns.length) return;
  var items = document.querySelectorAll('.pj'), empty = document.querySelector('.pjs__empty');
  btns.forEach(function (b) {
    b.addEventListener('click', function () {
      var f = b.dataset.filter, n = 0;
      btns.forEach(function (x) { var on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
      items.forEach(function (it) { var show = f === 'all' || it.dataset.type === f; it.hidden = !show; if (show) n++; });
      if (empty) empty.hidden = n > 0;
    });
  });
})();

// Parallax: photos drift up/down inside their frames while scrolling
(function () {
  var imgs = [].slice.call(document.querySelectorAll('img[data-parallax]'));
  if (!imgs.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var ticking = false;
  function update() {
    var vh = window.innerHeight;
    imgs.forEach(function (img) {
      var r = img.parentNode.getBoundingClientRect();
      if (r.bottom < 0 || r.top > vh) return;
      var p = (r.top + r.height / 2 - vh / 2) / (vh / 2 + r.height / 2); // -1 .. 1
      img.style.transform = 'translate3d(0,' + (p * r.height * 0.22).toFixed(1) + 'px,0)';
    });
    ticking = false;
  }
  document.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true, capture: true });
  window.addEventListener('resize', update);
  update();
})();
