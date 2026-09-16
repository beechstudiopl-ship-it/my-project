var LEAD_EMAIL = 'kontakt@dzialajmy.online'; // TODO: podmień na swój realny adres, jeśli inny

var EXAMPLES = [
  'Jestem trenerem personalnym (Studio Form, Kraków, ul. Przykładowa 12). Pomagam zabieganym 30- i 40-latkom wrócić do formy bez katowania się na siłowni. Oferta: trening 1:1 (120 zł), pakiet 10 wejść (1000 zł), plan dietetyczny (200 zł). Treningi stacjonarnie i online. Kontakt: 123 456 789, kontakt@twojafirma.pl.',
  'Prowadzę mały warsztat samochodowy w Lęborku. Naprawy bieżące, wymiana opon, diagnostyka komputerowa. Pracuję sam, klienci głównie z polecenia. Chciałbym, żeby ludzie mogli mnie znaleźć w Google i zobaczyć cennik bez dzwonienia.',
  'Mam salon kosmetyczny w Gdyni — manicure, pedicure, zabiegi na twarz. Teraz zapisy tylko przez telefon i Instagram, gubię się w wiadomościach. Chcę stronę z grafikiem i cennikiem, żeby klientki zapisywały się same.'
];

var STORAGE_KEY = 'dzialajmy_lead_draft';

var exampleIndex = 0;

var opisEl = document.getElementById('opis');
var exampleBanner = document.getElementById('example-banner');
var exampleFillBtn = document.getElementById('example-fill');
var exampleInnyBtn = document.getElementById('example-inny');
var exampleWyczyscBtn = document.getElementById('example-wyczysc');

function fillExample() {
  if (!opisEl) return;
  opisEl.value = EXAMPLES[exampleIndex % EXAMPLES.length];
  exampleIndex++;
  if (exampleBanner) exampleBanner.hidden = false;
}

if (exampleFillBtn) exampleFillBtn.addEventListener('click', fillExample);
if (exampleInnyBtn) exampleInnyBtn.addEventListener('click', fillExample);
if (exampleWyczyscBtn) {
  exampleWyczyscBtn.addEventListener('click', function () {
    if (opisEl) opisEl.value = '';
    if (exampleBanner) exampleBanner.hidden = true;
  });
}
// Typing over the example dismisses the "to tylko przykład" banner.
if (opisEl) {
  opisEl.addEventListener('input', function () {
    if (exampleBanner && !exampleBanner.hidden) exampleBanner.hidden = true;
  });
}

// Photo/logo previews — client-side only, files are not uploaded anywhere yet.
var zdjeciaInput = document.getElementById('zdjecia');
var filePreviews = document.getElementById('file-previews');
if (zdjeciaInput && filePreviews) {
  zdjeciaInput.addEventListener('change', function () {
    filePreviews.innerHTML = '';
    Array.prototype.forEach.call(zdjeciaInput.files, function (file) {
      var img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.alt = file.name;
      filePreviews.appendChild(img);
    });
  });
}

// Pakiet selection cards (START / FULL / Pogadamy)
var selectCards = document.querySelectorAll('.select-card');
var keywordsRow = document.getElementById('keywords-row');
var frazyEl = document.getElementById('frazy');

function refreshSelectCards() {
  selectCards.forEach(function (card) {
    var input = card.querySelector('input[type="radio"]');
    card.classList.toggle('is-selected', input.checked);
  });
  var checked = document.querySelector('input[name="interesuje"]:checked');
  var isFull = checked && checked.value === 'FULL';
  if (keywordsRow) keywordsRow.hidden = !isFull;
  if (!isFull && frazyEl) frazyEl.value = '';
}

selectCards.forEach(function (card) {
  var input = card.querySelector('input[type="radio"]');
  input.addEventListener('change', refreshSelectCards);
});

// Pre-select a pakiet card when someone clicks a CTA on an oferta card, and scroll+refresh state.
document.querySelectorAll('[data-oferta]').forEach(function (link) {
  link.addEventListener('click', function () {
    var value = link.getAttribute('data-oferta');
    var radio = document.querySelector('input[name="interesuje"][value="' + value + '"]');
    if (radio) {
      radio.checked = true;
      refreshSelectCards();
    }
  });
});

// Hidden context captured for whoever eventually reads the leads (referrer, UTM, timestamp).
function getHiddenContext() {
  var params = new URLSearchParams(window.location.search);
  var utm = {};
  ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(function (key) {
    if (params.get(key)) utm[key] = params.get(key);
  });
  return {
    referrer: document.referrer || null,
    utm: utm,
    submitted_at: new Date().toISOString()
  };
}

// Draft autosave to localStorage, so a long opis survives an accidental reload.
var form = document.getElementById('contact-form');
var DRAFT_FIELDS = ['opis', 'imie', 'email', 'telefon', 'frazy'];

function saveDraft() {
  if (!form) return;
  try {
    var draft = {};
    DRAFT_FIELDS.forEach(function (name) {
      if (form[name]) draft[name] = form[name].value;
    });
    var interesuje = form.querySelector('input[name="interesuje"]:checked');
    var raty = form.querySelector('input[name="raty"]:checked');
    draft.interesuje = interesuje ? interesuje.value : '';
    draft.raty = raty ? raty.value : '';
    localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
  } catch (err) {
    // localStorage unavailable (private mode, blocked) — draft autosave just skipped.
  }
}

function restoreDraft() {
  if (!form) return;
  try {
    var raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;
    var draft = JSON.parse(raw);
    DRAFT_FIELDS.forEach(function (name) {
      if (form[name] && draft[name]) form[name].value = draft[name];
    });
    if (draft.interesuje) {
      var interesujeRadio = form.querySelector('input[name="interesuje"][value="' + draft.interesuje + '"]');
      if (interesujeRadio) interesujeRadio.checked = true;
    }
    if (draft.raty) {
      var ratyRadio = form.querySelector('input[name="raty"][value="' + draft.raty + '"]');
      if (ratyRadio) ratyRadio.checked = true;
    }
    refreshSelectCards();
  } catch (err) {
    // corrupted or blocked draft — ignore and start fresh.
  }
}

if (form) {
  restoreDraft();
  form.addEventListener('input', saveDraft);
  form.addEventListener('change', saveDraft);
}

function buildPrompt(data) {
  var lines = [
    'Nowe zgłoszenie z działajmy.online',
    '',
    'Pakiet: ' + data.interesuje,
    'Raty: ' + data.raty,
    '',
    'Opis biznesu klienta:',
    data.opis,
    ''
  ];
  if (data.frazy) {
    lines.push('Frazy, których mogą szukać jego klienci w Google:');
    lines.push(data.frazy);
    lines.push('');
  }
  lines.push('Dopasować: styl, typografię, kolory, UI/UX, wersję mobile.');
  lines.push('');
  if (data.zdjecia && data.zdjecia.length) {
    lines.push('Zdjęcia/logo (' + data.zdjecia.length + '): ' + data.zdjecia.join(', ') + ' — dosłane w odpowiedzi na maila.');
    lines.push('');
  }
  lines.push('Kontakt: ' + data.imie + ', ' + data.email + ', ' + data.telefon);
  return lines.join('\n');
}

// Contact form submit.
// TODO: to dziś tylko mailto: + log/localStorage. Docelowo podłącz prawdziwy backend
// (np. Formspree/EmailJS/własne API), żeby zdjęcia faktycznie trafiały do Ciebie jako załączniki.
if (form) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Honeypot: bots fill every field, real visitors never see this one.
    if (form.firma && form.firma.value) {
      return;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var data = {
      opis: form.opis.value,
      zdjecia: zdjeciaInput ? Array.prototype.map.call(zdjeciaInput.files, function (f) { return f.name; }) : [],
      interesuje: form.interesuje.value,
      frazy: form.frazy ? form.frazy.value : '',
      imie: form.imie.value,
      email: form.email.value,
      telefon: form.telefon.value,
      raty: form.raty.value,
      context: getHiddenContext()
    };

    var prompt = buildPrompt(data);

    console.log('Nowe zgłoszenie działajmy.online:', data);
    console.log(prompt);

    try {
      localStorage.setItem('dzialajmy_last_submission', JSON.stringify({ data: data, prompt: prompt }));
      localStorage.removeItem(STORAGE_KEY);
    } catch (err) {
      // storage unavailable — submission still proceeds via mailto below.
    }

    var subject = 'Nowe zgłoszenie: ' + data.interesuje + ' — ' + data.imie;
    var mailtoUrl = 'mailto:' + LEAD_EMAIL + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(prompt);
    window.location.href = mailtoUrl;

    var successBlock = document.getElementById('form-success-block');
    form.querySelectorAll('input, textarea, button').forEach(function (el) {
      el.disabled = true;
    });
    if (successBlock) successBlock.hidden = false;

    var copyBtn = document.getElementById('copy-submission');
    if (copyBtn) {
      copyBtn.disabled = false;
      copyBtn.addEventListener('click', function () {
        navigator.clipboard.writeText(prompt).then(function () {
          copyBtn.textContent = 'Skopiowano!';
        }).catch(function () {
          copyBtn.textContent = 'Nie udało się skopiować';
        });
      });
    }
  });
}
