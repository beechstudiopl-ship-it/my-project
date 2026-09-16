// Pre-select "interesuje mnie" radio when someone clicks a CTA on an oferta card
document.querySelectorAll('[data-oferta]').forEach(function (link) {
  link.addEventListener('click', function () {
    var value = link.getAttribute('data-oferta');
    var radio = document.querySelector('input[name="interesuje"][value="' + value + '"]');
    if (radio) radio.checked = true;
  });
});

// Contact form: client-side only for now.
// TODO: podłącz do prawdziwego backendu (np. Formspree, EmailJS albo własne API).
// Na razie dane trafiają tylko do konsoli i użytkownik widzi potwierdzenie.
var form = document.getElementById('contact-form');
if (form) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    var data = {
      imie: form.imie.value,
      branza: form.branza.value,
      telefon: form.telefon.value,
      interesuje: form.interesuje.value,
      raty: form.raty.value
    };

    console.log('Nowe zgłoszenie działajmy.online:', data);

    var success = document.getElementById('form-success');
    form.querySelectorAll('input, button').forEach(function (el) {
      el.disabled = true;
    });
    if (success) success.hidden = false;
  });
}
