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
