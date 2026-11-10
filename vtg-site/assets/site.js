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
    });
    menu.addEventListener('click', function (e) {
      if (e.target.closest('a')) { menu.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); }
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
