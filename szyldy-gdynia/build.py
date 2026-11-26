#!/usr/bin/env python3
"""Generator strony „Szyldy reklamowe Gdynia”.

Dane firmy wpisujesz RAZ w sekcji FIRMA poniżej, a potem uruchamiasz:
    python3 build.py
Skrypt nadpisze pliki w folderze ./strona (to ten folder wgrywasz na serwer).
Dzięki temu nazwa, adres, telefon i e-mail są identyczne na każdej podstronie,
w danych strukturalnych (JSON-LD) i w llms.txt – tak jak w Profilu Firmy w Google.
"""
import html
import json
import re
from pathlib import Path

# ---------------------------------------------------------------------------
# 1. DANE FIRMY – wpisz tu prawdziwe dane (te same co w Profilu Firmy w Google)
# ---------------------------------------------------------------------------
DOMENA = "https://www.TWOJA-DOMENA.pl"  # [UZUPEŁNIJ] bez ukośnika na końcu
DATA_AKTUALIZACJI = "2026-11-23"

FIRMA = {
    "nazwa": "[UZUPEŁNIJ: pełna nazwa firmy]",
    "logo": "Szyldy Gdynia",  # [UZUPEŁNIJ] krótka nazwa marki w nagłówku
    "ulica": "[UZUPEŁNIJ: ulica i numer]",
    "kod": "[UZUPEŁNIJ: kod pocztowy]",
    "miasto": "Gdynia",
    "telefon": "[UZUPEŁNIJ: numer telefonu]",
    "telefon_href": "+48000000000",  # [UZUPEŁNIJ] numer w formacie +48XXXXXXXXX
    "whatsapp": "48000000000",  # [UZUPEŁNIJ] numer do WhatsApp: 48 + 9 cyfr, bez plusa i spacji
    "czas_odpowiedzi": "[UZUPEŁNIJ: czas odpowiedzi, np. 1 dnia roboczego]",
    "email": "[UZUPEŁNIJ: adres e-mail]",
    "nip": "[UZUPEŁNIJ: NIP, opcjonalnie]",
    "godziny_pn_pt": "[UZUPEŁNIJ: godziny]",
    "godziny_sob": "[UZUPEŁNIJ: godziny lub „nieczynne”]",
    # do JSON-LD (format 24h, np. "08:00"):
    "otwarcie_pn_pt": "[UZUPEŁNIJ: np. 08:00]",
    "zamkniecie_pn_pt": "[UZUPEŁNIJ: np. 16:00]",
    "obszar": ["Gdynia", "[UZUPEŁNIJ: inne miejscowości, np. Sopot, Gdańsk, Rumia, Reda]"],
    "mapa_link": "[UZUPEŁNIJ: link do wizytówki w Mapach Google]",
    # adres obsługi formularza (np. Formspree, Getform, własny skrypt PHP):
    "formularz_action": "https://formspree.io/f/UZUPELNIJ",
    "polityka_prywatnosci": "[UZUPEŁNIJ: adres polityki prywatności]",
}
ADRES = f"{FIRMA['ulica']}, {FIRMA['kod']} {FIRMA['miasto']}"
GODZINY = f"pn–pt: {FIRMA['godziny_pn_pt']}, sobota: {FIRMA['godziny_sob']}, niedziela: nieczynne"

import sys
# python3 build.py            -> pełna wersja w ./strona
# python3 build.py --makieta  -> makieta bez stylu (szkielet) w ./makieta
MAKIETA = "--makieta" in sys.argv
OUT = Path(__file__).parent / ("makieta" if MAKIETA else "strona")

# ---------------------------------------------------------------------------
# 2. Pomocnicze
# ---------------------------------------------------------------------------
FILL_RE = re.compile(r"\[UZUPEŁNIJ[^\]]*\]")


def fill(text):
    """Zaznacza miejsca [UZUPEŁNIJ…] na żółto, żeby właściciel je widział."""
    return FILL_RE.sub(lambda m: f'<mark class="fill">{m.group(0)}</mark>', text)


def plain(text):
    """Tekst bez znaczników – do meta, JSON-LD."""
    text = re.sub(r"\[([^\[\]]+)\]\(([^)]+)\)", r"\1", text)
    text = text.replace("**", "")
    for k, v in TOKENS_PLAIN.items():
        text = text.replace(k, v)
    return re.sub(r"\s+", " ", text).strip()


TOKENS_PLAIN = {
    "{NAZWA}": FIRMA["nazwa"],
    "{ADRES}": ADRES,
    "{TEL}": FIRMA["telefon"],
    "{TEL_LINK}": FIRMA["telefon"],
    "{EMAIL}": FIRMA["email"],
    "{EMAIL_LINK}": FIRMA["email"],
    "{NIP}": FIRMA["nip"],
    "{GODZINY}": GODZINY,
    "{GODZ_PNPT}": FIRMA["godziny_pn_pt"],
    "{GODZ_SOB}": FIRMA["godziny_sob"],
}


def tokens_html():
    return {
        "{NAZWA}": fill(html.escape(FIRMA["nazwa"], quote=False)),
        "{ADRES}": fill(html.escape(ADRES, quote=False)),
        "{TEL_LINK}": f'<a href="tel:{FIRMA["telefon_href"]}">{fill(FIRMA["telefon"])}</a>',
        "{TEL}": fill(FIRMA["telefon"]),
        "{EMAIL_LINK}": f'<a href="mailto:{html.escape(FIRMA["email"])}">{fill(FIRMA["email"])}</a>',
        "{EMAIL}": fill(FIRMA["email"]),
        "{NIP}": fill(FIRMA["nip"]),
        "{GODZINY}": fill(html.escape(GODZINY, quote=False)),
        "{GODZ_PNPT}": fill(FIRMA["godziny_pn_pt"]),
        "{GODZ_SOB}": fill(html.escape(FIRMA["godziny_sob"], quote=False)),
    }


def inline(text):
    text = html.escape(text, quote=False)
    text = re.sub(r"\*\*(.+?)\*\*", r"<strong>\1</strong>", text)
    text = re.sub(r"\[([^\[\]]+)\]\(([^)\s]+)\)", r'<a href="\2">\1</a>', text)
    text = fill(text)
    for k, v in tokens_html().items():
        text = text.replace(k, v)
    return text


def item_parts(text):
    m = re.match(r"\*\*(.+?)\*\*\s*[–:]?\s*(.*)", text)
    if not m:
        return ("", text)
    d = m.group(2)
    return (m.group(1).rstrip(":"), d[:1].upper() + d[1:] if not d.startswith("[") else d)


def md(src):
    """Mini-markdown: akapity, listy „- ”, „1. ”, warianty list „::cards”, „::price”, „::info”, dyrektywy {{…}}."""
    out = []
    for block in [b.strip() for b in src.strip().split("\n\n") if b.strip()]:
        lines = [l.strip() for l in block.split("\n")]
        variant = None
        if lines[0].startswith("::"):
            variant, lines = lines[0][2:], lines[1:]
        if block.startswith("{{") and block.endswith("}}"):
            name, _, arg = block[2:-2].partition(":")
            out.append(DIRECTIVES[name](arg))
        elif all(l.startswith("- ") for l in lines):
            items = [l[2:] for l in lines]
            if variant == "cards":
                cells = []
                for n, it in enumerate(items, 1):
                    t, d = item_parts(it)
                    cells.append(f'<li class="tile"><span class="tile-n">{n:02d}</span><h3>{inline(t)}</h3><p>{inline(d)}</p></li>')
                out.append(f'<ul class="tiles tiles-{len(cells)}">' + "".join(cells) + "</ul>")
            elif variant == "services":
                cells = []
                for it in items:
                    t, d = item_parts(it)
                    key = "led" if "LED" in t else ("litery" if "Litery" in t else "szyld")
                    cells.append(f'<li class="svc"><span class="svc-ico">{ICONS[key]}</span><h3>{inline(t)}</h3><p>{inline(d)}</p></li>')
                out.append('<ul class="svcs">' + "".join(cells) + "</ul>")
            elif variant in ("price", "info"):
                rows = []
                for it in items:
                    t, d = item_parts(it)
                    rows.append(f'<li><span class="k">{inline(t)}</span><span class="v">{inline(d)}</span></li>' if t else f'<li><span class="v">{inline(d)}</span></li>')
                out.append(f'<ul class="{variant}-list">' + "".join(rows) + "</ul>")
            else:
                out.append("<ul>" + "".join(f"<li>{inline(i)}</li>" for i in items) + "</ul>")
        elif all(re.match(r"\d+\. ", l) for l in lines):
            out.append('<ol class="steps">' + "".join(f"<li><div>{inline(re.sub(r'^\d+\. ', '', l))}</div></li>" for l in lines) + "</ol>")
        else:
            out.append(f"<p>{inline(' '.join(lines))}</p>")
    return "\n".join(out)


ICONS = {
    "szyld": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="10" rx="1.5"/><path d="M7 11h10M8 16v3M16 16v3"/></svg>',
    "litery": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19 9 5l5 14M6 14h6"/><path d="M11 5l2-1.5 5 14-2 1.5"/></svg>',
    "led": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"/></svg>',
}


# --- obrazy (placeholdery WebP – podmień na prawdziwe zdjęcia, zachowując nazwy) ---
IMAGES = {
    "szyld-1": ("Szyld reklamowy na elewacji lokalu usługowego w Gdyni", "Szyld płaski na elewację – [UZUPEŁNIJ: branża klienta, materiał, dzielnica]"),
    "szyld-2": ("Podświetlany kaseton reklamowy nad wejściem do sklepu w Gdyni", "Kaseton nad wejściem – [UZUPEŁNIJ: branża klienta, materiał, dzielnica]"),
    "litery-1": ("Litery przestrzenne 3D na fasadzie budynku w Gdyni", "Litery przestrzenne na fasadzie – [UZUPEŁNIJ: branża, materiał, rodzaj podświetlenia]"),
    "litery-2": ("Logo z liter przestrzennych na ścianie recepcji biura w Gdyni", "Logo 3D na recepcji – [UZUPEŁNIJ: branża, materiał, rodzaj podświetlenia]"),
    "led-1": ("Reklama LED – litery podświetlane po zmroku na elewacji w Gdyni", "Litery LED widoczne po zmroku – [UZUPEŁNIJ: branża, rodzaj podświetlenia, dzielnica]"),
    "led-2": ("Podświetlany kaseton LED na witrynie lokalu w Gdyni", "Kaseton LED – [UZUPEŁNIJ: branża, wymiary, dzielnica]"),
}


def figure(key, eager=False):
    alt, cap = IMAGES[key]
    loading = 'fetchpriority="high"' if eager else 'loading="lazy"'
    return (f'<figure><img src="img/{key}.webp" width="800" height="600" {loading} decoding="async" '
            f'alt="{html.escape(alt)}"><figcaption>{inline(cap)}</figcaption></figure>')


def gallery(arg):
    return '<div class="gallery">' + "".join(figure(k.strip()) for k in arg.split(",")) + "</div>"


RODZAJE = ["Nie wiem – doradźcie", "Kaseton reklamowy", "Litery przestrzenne 3D", "Logo do środka",
           "Litery metalowe", "Neon LED"]


def form(arg):
    """Krótki formularz wyceny: zdjęcie + telefon/e-mail + wyślij. Reszta opcjonalnie, zwinięta."""
    fid = "szybka" if arg == "okno" else "formularz"
    opts = "".join(f"<option>{o}</option>" for o in RODZAJE)
    return f'''<form class="form qform" id="{fid}" action="{html.escape(FIRMA['formularz_action'])}" method="post" enctype="multipart/form-data" data-quote>
  <input type="hidden" name="strona" value="{arg}">
  <input type="file" class="q-photos" id="{fid}-foto" name="zdjecia" multiple accept="image/*,.pdf,.ai,.eps,.svg">
  <label class="photo-pick" for="{fid}-foto"><span class="photo-pick-ico" aria-hidden="true">+</span><span><strong>Dodaj zdjęcie elewacji</strong><small>Z aparatu lub galerii. Możesz dodać kilka zdjęć i logo.</small></span></label>
  <ul class="thumbs" aria-live="polite"></ul>
  <label for="{fid}-kontakt">Telefon lub e-mail</label>
  <input type="text" id="{fid}-kontakt" name="kontakt" required minlength="6" autocomplete="tel" placeholder="np. 600 100 200">
  <details class="q-more">
    <summary>Dodaj wymiary i szczegóły <span>(opcjonalnie)</span></summary>
    <div class="q-more-body">
      <fieldset class="dims" data-dims>
        <legend>Wymiary szyldu</legend>
        <div class="form-row">
          <label>Szerokość (cm)<input type="number" class="d-w" id="{fid}-w" name="szerokosc_cm" min="1" step="any" inputmode="decimal" placeholder="np. 300"></label>
          <label>Wysokość (cm)<input type="number" class="d-h" id="{fid}-h" name="wysokosc_cm" min="1" step="any" inputmode="decimal" placeholder="np. 60"></label>
        </div>
        <p class="dims-area" aria-live="polite">Pole szyldu: wpisz szerokość i wysokość.</p>
        <input type="hidden" name="pole_m2">
      </fieldset>
      <fieldset class="measure" data-measure>
        <legend>Nie znasz wymiarów? Zmierz na zdjęciu</legend>
        <p class="measure-empty">Najpierw dodaj zdjęcie elewacji (na wprost) – zaznaczysz na nim drzwi i miejsce na szyld, a my policzymy wymiary.</p>
        <div class="measure-stage" hidden>
          <div class="measure-tools" role="group" aria-label="Co zaznaczasz">
            <button type="button" class="btn" data-mode="line" aria-pressed="true">1. Drzwi</button>
            <button type="button" class="btn" data-mode="rect" aria-pressed="false">2. Miejsce na szyld</button>
            <button type="button" class="btn btn-link" data-clear>Wyczyść</button>
          </div>
          <canvas class="measure-canvas" aria-label="Zdjęcie elewacji – przeciągnij, aby zaznaczyć"></canvas>
          <p class="measure-label">Szerokość zaznaczonego elementu</p>
          <div class="measure-tools" role="group" aria-label="Szerokość elementu">
            <button type="button" class="btn" data-ref="90" aria-pressed="false">Drzwi ok. 90 cm</button>
            <button type="button" class="btn" data-ref="150" aria-pressed="false">Dwuskrzydłowe ok. 150 cm</button>
          </div>
          <label>albo wpisz (cm)<input type="number" class="m-ref" id="{fid}-ref" name="szerokosc_odniesienia_cm" min="1" step="any" inputmode="decimal" placeholder="np. 90"></label>
          <p class="measure-result" aria-live="polite"></p>
          <p class="measure-note">Wynik jest orientacyjny – drzwi i szyld muszą być na tej samej ścianie. Dokładnie zmierzymy przed produkcją.</p>
        </div>
      </fieldset>
      <div class="form-row">
        <label>Rodzaj<select name="rodzaj" class="q-rodzaj">{opts}</select></label>
        <label>Podświetlenie<select name="podswietlenie"><option>Nie wiem – doradźcie</option><option>Podświetlane LED</option><option>Bez podświetlenia</option></select></label>
      </div>
      <label>Adres montażu<input type="text" name="adres" autocomplete="street-address" placeholder="np. ul. Świętojańska, Gdynia"></label>
      <label>Imię lub nazwa firmy<input type="text" name="imie" autocomplete="name"></label>
      <label>Wiadomość<textarea name="wiadomosc" rows="3" placeholder="Podświetlany czy nie? Termin, budżet…"></textarea></label>
    </div>
  </details>
  <button class="btn btn-primary btn-send" type="submit">Wyślij do wyceny</button>
  <p class="form-note">Odpowiemy z wyceną w ciągu {fill(FIRMA["czas_odpowiedzi"])}. Dane wykorzystamy tylko do przygotowania wyceny – <a href="{html.escape(FIRMA['polityka_prywatnosci'])}">polityka prywatności</a>.</p>
  <p class="form-status" role="status" hidden></p>
</form>'''


def mapa(_):
    return (f'<div class="map"><p>{fill("[UZUPEŁNIJ: mapa Google – wklej tu kod iframe z Map Google i dodaj loading=&quot;lazy&quot;]")}</p>'
            f'<p><a href="{html.escape(FIRMA["mapa_link"])}" rel="noopener">Otwórz w Mapach Google</a></p></div>')


def mosaic(arg):
    """Galeria bez podpisów, zdjęcia różnej wielkości (big = 2×2, wide = 2×1, tall = 1×2)."""
    cells = []
    for item in arg.split(","):
        key, _, size = item.strip().partition(":")
        alt = IMAGES[key][0]
        cls = f' class="m-{size}"' if size else ""
        cells.append(f'<figure{cls}><img src="img/{key}.webp" width="800" height="600" loading="lazy" decoding="async" alt="{html.escape(alt)}"></figure>')
    return '<div class="mosaic">' + "".join(cells) + "</div>"


WHO_ICONS = {
    "shop": '<path d="M4 10v10h16V10"/><path d="M3 10l2-6h14l2 6"/><path d="M3 10c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3"/><path d="M10 20v-5h4v5"/>',
    "food": '<path d="M5 3v8a2 2 0 0 0 2 2v8M9 3v8a2 2 0 0 1-2 2M7 3v6"/><path d="M16 21V3c2.5 1.5 3.5 4 3.5 7.5 0 2-1.5 3.5-3.5 3.5"/>',
    "office": '<rect x="3" y="7" width="18" height="13" rx="1"/><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M3 12h18M11 12v2h2v-2"/>',
    "tower": '<path d="M5 21V4h9v17M14 9h5v12M3 21h18"/><path d="M8 7h3M8 10h3M8 13h3M8 16h3M16.5 12h0M16.5 15h0M16.5 18h0"/>',
}
WHO = [
    ("shop", "Sklepy i punkty usługowe", "Otwierają nowy lokal lub zmieniają wizerunek.", "szyldy-reklamowe-gdynia"),
    ("food", "Restauracje, kawiarnie i bary", "Zależy im na widoczności także po zmroku.", "reklama-led-gdynia"),
    ("office", "Gabinety, salony i biura", "Potrzebują estetycznego, czytelnego szyldu lub tablicy.", "litery-przestrzenne-gdynia"),
    ("tower", "Firmy w biurowcach i pawilonach", "Muszą dopasować reklamę do zasad wspólnoty lub zarządcy budynku.", "szyldy-reklamowe-gdynia"),
]


def who(_):
    """Kto się zgłasza – siatka: ikona, grupa klientów, opis, strzałka (cały kafel to link)."""
    cells = "".join(
        f'<li><a href="{"#wycena" if LANDING_ONLY else href(link)}"><span class="who-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">{WHO_ICONS[ico]}</svg></span>'
        f'<span class="who-txt"><strong>{t}</strong><span>{d}</span></span><b aria-hidden="true">→</b></a></li>'
        for ico, t, d, link in WHO)
    return f'<ul class="who">{cells}</ul>'


LETTERS = [
    ("plexi", "Litera z plexi – światło w przód",
     [("Lico", "mleczna plexi"), ("Boki", "taśma aluminiowa"), ("Światło", "LED świeci przez lico, do przodu")],
     "Najlepsza czytelność po zmroku i z daleka – sklepy, gastronomia, ruchliwe ulice."),
    ("halo", "Litera metalowa – światło na elewację",
     [("Lico i boki", "metal, np. aluminium lub stal"), ("Światło", "LED świeci do tyłu, na ścianę – efekt halo")],
     "Dyskretna, elegancka poświata – salony, kliniki, biura, restauracje."),
    ("metal", "Litera metalowa bez podświetlenia",
     [("Lico i boki", "aluminium lub stal nierdzewna"), ("Światło", "brak – dystanse od ściany dają cień i głębię")],
     "Prostsza i tańsza – do wnętrz, na recepcję lub na dobrze oświetloną elewację."),
]


def letter_svg(kind):
    """Rysunek litery „A” w danej konstrukcji (inline SVG, bez obrazków)."""
    t = 'x="160" y="168" text-anchor="middle" font-family="Archivo, Arial, sans-serif" font-weight="700" font-size="150"'
    if kind == "plexi":
        wall = '<rect width="320" height="200" fill="#16232d"/>'
        side = "".join(f'<text {t} transform="translate({i * .9:.1f} {i * .9:.1f})" fill="#{"8f99a3" if i % 2 else "a7b0b8"}">A</text>' for i in range(9, 0, -1))
        glow = f'<text {t} fill="#fff4cc" filter="url(#g-plexi)" opacity=".95">A</text>'
        face = f'<text {t} fill="#fffdf4">A</text>'
        defs = '<filter id="g-plexi" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="9"/></filter>'
        body = wall + side + glow + face
    elif kind == "halo":
        wall = '<rect width="320" height="200" fill="#1b2630"/>'
        halo = f'<text {t} fill="#ffc04d" filter="url(#g-halo)">A</text>'
        side = "".join(f'<text {t} transform="translate({i * .9:.1f} {i * .9:.1f})" fill="#23272d">A</text>' for i in range(7, 0, -1))
        face = f'<text {t} fill="url(#m-halo)">A</text>'
        defs = ('<filter id="g-halo" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="11"/></filter>'
                '<linearGradient id="m-halo" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#5d646d"/><stop offset="1" stop-color="#2e333a"/></linearGradient>')
        body = wall + halo + halo + side + face
    else:
        wall = '<rect width="320" height="200" fill="#dcd8d0"/>'
        shadow = f'<text {t} transform="translate(10 12)" fill="#000" opacity=".28" filter="url(#g-metal)">A</text>'
        side = "".join(f'<text {t} transform="translate({i * .9:.1f} {i * .9:.1f})" fill="#7f858c">A</text>' for i in range(7, 0, -1))
        face = f'<text {t} fill="url(#m-metal)">A</text>'
        defs = ('<filter id="g-metal" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="4"/></filter>'
                '<linearGradient id="m-metal" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f1f2f3"/><stop offset=".5" stop-color="#b9bec4"/><stop offset="1" stop-color="#e2e4e6"/></linearGradient>')
        body = wall + shadow + side + face
    return f'<svg viewBox="0 0 320 200" role="img" aria-hidden="true"><defs>{defs}</defs>{body}</svg>'


def scene_svg(kind):
    """Rysunki do oferty i katalogu: kaseton, litery do wnętrz, neon LED (inline SVG)."""
    if kind == "kaseton":
        return ('<svg viewBox="0 0 320 200" role="img" aria-hidden="true"><defs>'
                '<filter id="g-kas" x="-30%" y="-60%" width="160%" height="220%"><feGaussianBlur stdDeviation="12"/></filter></defs>'
                '<rect width="320" height="200" fill="#16232d"/>'
                '<rect x="56" y="58" width="208" height="84" fill="#fff4cc" filter="url(#g-kas)" opacity=".75"/>'
                '<rect x="62" y="66" width="200" height="80" fill="#3c4650"/>'
                '<rect x="56" y="58" width="200" height="80" fill="#fffdf6" stroke="#4d5964" stroke-width="5"/>'
                '<rect x="76" y="80" width="40" height="36" rx="6" fill="#ffb800"/>'
                '<text x="128" y="110" font-family="Archivo, Arial, sans-serif" font-weight="700" font-size="30" fill="#0f1d27">LOGO</text></svg>')
    if kind == "wnetrze":
        return ('<svg viewBox="0 0 320 200" role="img" aria-hidden="true"><defs>'
                '<filter id="g-wn" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur stdDeviation="3"/></filter></defs>'
                '<rect width="320" height="200" fill="#ece8e1"/><rect y="150" width="320" height="50" fill="#b8875a"/>'
                '<rect y="146" width="320" height="6" fill="#9c7048"/>'
                '<text x="164" y="104" text-anchor="middle" font-family="Archivo, Arial, sans-serif" font-weight="700" font-size="54" fill="#000" opacity=".22" filter="url(#g-wn)">STUDIO</text>'
                '<text x="160" y="98" text-anchor="middle" font-family="Archivo, Arial, sans-serif" font-weight="700" font-size="54" fill="#0f1d27">STUDIO</text>'
                '<rect x="128" y="112" width="64" height="4" fill="#ffb800"/></svg>')
    # neon LED
    return ('<svg viewBox="0 0 320 200" role="img" aria-hidden="true"><defs>'
            '<filter id="g-neon" x="-30%" y="-60%" width="160%" height="220%"><feGaussianBlur stdDeviation="7"/></filter></defs>'
            '<rect width="320" height="200" fill="#1a1d24"/>'
            '<g font-family="Pacifico, Brush Script MT, Segoe Script, cursive" font-size="76" text-anchor="middle">'
            '<text x="160" y="124" fill="#ff4fa3" filter="url(#g-neon)">open</text>'
            '<text x="160" y="124" fill="none" stroke="#ff8cc4" stroke-width="5">open</text>'
            '<text x="160" y="124" fill="none" stroke="#ffe3f0" stroke-width="1.6">open</text></g></svg>')


def cards_grid(items):
    cells = []
    for pic, title, spec, when, rodzaj in items:
        rows = "".join(f'<li><span class="k">{k}</span><span class="v">{v}</span></li>' for k, v in spec)
        cells.append(f'<li class="ltype"><div class="ltype-pic">{pic}</div><h3>{title}</h3>'
                     f'<ul class="ltype-spec">{rows}</ul><p>{when}</p>'
                     f'<a class="btn btn-sm lt-btn" href="#wycena" data-rodzaj="{html.escape(rodzaj)}">Wyceń – {title}</a></li>')
    return '<ul class="ltypes">' + "".join(cells) + "</ul>"


def offer(_):
    return cards_grid([
        (scene_svg("kaseton"), "Kaseton reklamowy",
         [("Wykończenie", "frezowanie wklęsłe lub wypukłe"), ("Podświetlenie", "LED w środku – równe światło po zmroku"), ("Przykładowa cena", "250 × 50 cm, frezowanie wklęsłe – 4500 zł [UZUPEŁNIJ: netto/brutto]"), ("Montaż", "od 1000 zł – zależnie od miejsca montażu")],
         "Dużo miejsca na logo i nazwę, dobra widoczność z daleka – sklepy, punkty usługowe, lokale przy ulicy.", "Kaseton reklamowy"),
        (letter_svg("plexi"), "Litery przestrzenne 3D",
         [("Co to", "litery i logo 3D na fasadę, nad wejście lub nad witrynę"), ("Podświetlane", "światło w przód, na elewację (halo) lub z boków"), ("Bez podświetlenia", "aluminium, stal lub plexi na dystansach"), ("Przykładowa cena", "napis 250 × 30 cm, 6 liter – od 6800 zł [UZUPEŁNIJ: netto/brutto]"), ("Montaż", "od 1000 zł – zależnie od miejsca montażu")],
         "Elegancki napis, który nie zasłania architektury – restauracje, salony, gabinety, biura.", "Litery przestrzenne 3D"),
        (scene_svg("wnetrze"), "Logo do środka",
         [("Co to", "logo za ladą, na recepcji, w biurze i sali konferencyjnej"), ("Podświetlane", "subtelna poświata na ścianę (halo)"), ("Bez podświetlenia", "plexi, PCV lub metal, montaż na ścianie"), ("Cena", "od 4800 zł – zależnie od rodzaju i wykończenia [UZUPEŁNIJ: netto/brutto]")],
         "Pierwsze, co klient widzi po wejściu – recepcje, gabinety, biura, salony.", "Logo do środka"),
    ])


def katalog(_):
    return cards_grid([
        (letter_svg("halo"), "Litery metalowe",
         [("Materiał", "aluminium lub stal nierdzewna"), ("Wersje", "bez podświetlenia lub ze światłem na elewację")],
         "Prestiżowy, trwały napis na fasadę – dobrze znosi nadmorską pogodę.", "Litery metalowe"),
        (scene_svg("wnetrze"), "Litery do środka",
         [("Materiał", "plexi, PCV spienione, dibond lub metal"), ("Wersje", "z podświetleniem halo lub bez")],
         "Logo na recepcję, do biura i lokalu – lżejsze i tańsze niż litery na elewację.", "Logo do środka"),
        (scene_svg("neon"), "Neony LED",
         [("Materiał", "elastyczny neon LED na plexi"), ("Zastosowanie", "napisy, logo i hasła w lokalu i witrynie")],
         "Efekt klasycznego neonu przy niskim zużyciu prądu – kawiarnie, bary, salony.", "Neon LED"),
    ])


def letters(_):
    cells = []
    for kind, title, spec, when in LETTERS:
        rows = "".join(f'<li><span class="k">{k}</span><span class="v">{v}</span></li>' for k, v in spec)
        cells.append(f'<li class="ltype"><div class="ltype-pic">{letter_svg(kind)}</div><h3>{title}</h3>'
                     f'<ul class="ltype-spec">{rows}</ul><p>{when}</p></li>')
    return '<ul class="ltypes">' + "".join(cells) + "</ul>"


# schematyczna mapa Gdyni: (nazwa, x, y, kotwica etykiety) – położenia przybliżone
DZIELNICE = [
    ("Babie Doły", 452, 52, "end"), ("Oksywie", 424, 104, "end"), ("Obłuże", 362, 118, "end"), ("Pogórze", 300, 80, "middle"),
    ("Pustki Cisowskie-Demptowo", 182, 104, "middle"), ("Cisowa", 150, 168, "end"), ("Chylonia", 236, 168, "middle"),
    ("Leszczynki", 288, 200, "end"), ("Grabówek", 318, 236, "end"), ("Działki Leśne", 350, 262, "end"),
    ("Śródmieście", 400, 276, "start"), ("Kamienna Góra", 422, 312, "start"), ("Wzgórze Św. Maksymiliana", 360, 330, "end"),
    ("Witomino", 270, 292, "end"), ("Chwarzno-Wiczlino", 120, 296, "middle"), ("Dąbrowa", 178, 358, "middle"),
    ("Karwiny", 218, 410, "middle"), ("Wielki Kack", 286, 424, "middle"), ("Mały Kack", 352, 404, "start"),
    ("Redłowo", 418, 368, "start"), ("Orłowo", 432, 428, "start"),
]
SASIEDZI = [("Kosakowo", 352, 26), ("Rumia", 40, 204), ("Reda, Wejherowo ←", 14, 140), ("Sopot", 452, 488), ("Gdańsk ↓", 470, 532)]


def mapa(_):
    land = ("M470,30 L300,38 L210,60 L140,92 L92,170 L64,240 L60,320 L96,392 L150,440 L240,470 L330,462 L446,452 "
            "L436,400 L424,340 L412,300 L404,262 L372,236 L360,196 L384,168 L430,150 L448,110 L462,70 Z")
    dots, labels = [], []
    for n, x, y, a in DZIELNICE:
        dx = {"start": 9, "end": -9, "middle": 0}[a]
        dy = 4 if a != "middle" else -10
        dots.append(f'<circle class="m-dot" data-d="{html.escape(n)}" cx="{x}" cy="{y}" r="5"><title>{n}</title></circle>')
        labels.append(f'<text class="m-lab" x="{x + dx}" y="{y + dy}" text-anchor="{a}">{n}</text>')
    nb = "".join(f'<circle class="m-nb" cx="{x}" cy="{y}" r="4"/><text class="m-nbl" x="{x + 8}" y="{y + 4}">{n}</text>' for n, x, y in SASIEDZI)
    svg = (f'<svg class="gmap" viewBox="0 0 600 560" role="img" aria-label="Schematyczna mapa Gdyni z dzielnicami, w których działamy">'
           f'<rect width="600" height="560" class="m-sea"/>'
           f'<path class="m-land-out" d="M0,0 H470 L462,70 L448,110 L430,150 L384,168 L360,196 L372,236 L404,262 L412,300 L424,340 L436,400 L446,452 L460,560 H0 Z"/>'
           f'<path class="m-land" d="{land}"/>'
           f'<text class="m-sea-l" x="540" y="250" transform="rotate(90 540 250)">ZATOKA GDAŃSKA</text>'
           f'<text class="m-port" x="398" y="206">PORT</text>'
           f'{nb}{"".join(dots)}{"".join(labels)}</svg>')
    chips = "".join(f'<li><button type="button" class="m-chip" data-d="{html.escape(n)}">{n}</button></li>' for n, *_ in sorted(DZIELNICE, key=lambda d: d[0]))
    return (f'<div class="map-wrap"><div class="map-card"><div class="map-scroll">{svg}</div>'
            f'<ul class="map-legend"><li><span class="lg-dot"></span>Gdynia – wszystkie dzielnice</li>'
            f'<li><span class="lg-nb"></span>Okolice – {fill("[UZUPEŁNIJ: potwierdź, gdzie montujecie]")}</li></ul>'
            f'<p class="map-note">Mapa poglądowa – położenie dzielnic przybliżone.</p></div>'
            f'<div class="map-side"><p class="map-side-t">Dzielnice Gdyni, w których działamy</p>'
            f'<ul class="map-chips">{chips}</ul><p class="map-pick" aria-live="polite">Wybierz dzielnicę, żeby zobaczyć ją na mapie.</p></div></div>')


def bleed(arg):
    """Zdjęcie na całą szerokość ekranu z podpisem."""
    key, alt, cap = (x.strip() for x in arg.split("|"))
    return (f'<figure class="bleed"><img src="img/{key}.webp" width="1920" height="816" loading="lazy" decoding="async" '
            f'alt="{html.escape(alt)}"><figcaption>{inline(cap)}</figcaption></figure>')


DIRECTIVES = {"bleed": bleed, "offer": offer, "katalog": katalog, "mapa": mapa, "letters": letters, "who": who, "mosaic": mosaic, "gallery": gallery, "form": form, "map": mapa}

# ---------------------------------------------------------------------------
# 3. Nawigacja i linki „Zobacz też”
# ---------------------------------------------------------------------------
LANDING_ONLY = True  # na razie budujemy tylko landing (stronę główną); False = wszystkie podstrony
LANDING_NAV = [("#oferta", "Oferta"), ("#katalog", "Katalog"), ("#realizacje", "Realizacje"),
               ("#wycena", "Wycena"), ("#obszar", "Obszar działania"), ("#faq", "FAQ")]
SECTION_IDS = {
    "Szyldy reklamowe Gdynia – co robimy i dla kogo": "dla-kogo",
    "Kasetony, litery przestrzenne i litery do wnętrz": "oferta",
    "Katalog: litery metalowe, litery do środka i neony LED": "katalog",
    "Realizacje i opinie klientów": "realizacje",
    "Jak wygląda współpraca krok po kroku": "wycena",
    "Od czego zależy cena szyldu reklamowego": "cena",
    "Szyldy reklamowe w Gdyni i okolicy – obszar działania i kontakt": "obszar",
    "Uchwała krajobrazowa Gdyni – co sprawdzić przed montażem szyldu": "uchwala",
}
NAV = [
    ("szyldy-reklamowe-gdynia", "Szyldy reklamowe"),
    ("litery-przestrzenne-gdynia", "Litery przestrzenne"),
    ("reklama-led-gdynia", "Reklama LED"),
    ("realizacje", "Realizacje"),
    ("cennik", "Cennik"),
    ("kontakt", "Kontakt"),
]
CARDS = {
    "szyldy-reklamowe-gdynia": ("Szyldy reklamowe Gdynia", "Szyldy na elewację, kasetony, semafory i tablice."),
    "litery-przestrzenne-gdynia": ("Litery przestrzenne Gdynia", "Litery 3D na fasadę, recepcję i do biura."),
    "reklama-led-gdynia": ("Reklama LED Gdynia", "Podświetlane kasetony, szyldy i litery LED."),
    "realizacje": ("Realizacje", "Zdjęcia wykonanych szyldów z opisem materiałów."),
    "cennik": ("Cennik", "Od czego zależy cena i orientacyjne widełki."),
    "kontakt": ("Kontakt i wycena", "Wyślij zdjęcie elewacji i wymiary."),
}


def url(slug):
    return f"{DOMENA}/" if slug == "index" else f"{DOMENA}/{slug}.html"


def href(slug):
    return "./" if slug == "index" else f"{slug}.html"


# ---------------------------------------------------------------------------
# 4. TREŚCI PODSTRON
# ---------------------------------------------------------------------------
L_SZ = "szyldy-reklamowe-gdynia.html"
L_LI = "litery-przestrzenne-gdynia.html"
L_LED = "reklama-led-gdynia.html"
L_RE = "realizacje.html"
L_CE = "cennik.html"
L_KO = "kontakt.html"

PAGES = []

# ============================ INDEX (LANDING) ===============================
PAGES.append(dict(
    slug="index",
    title="Szyldy reklamowe Gdynia – kasetony i litery przestrzenne",
    desc="Szyldy reklamowe Gdynia: kasetony, litery przestrzenne i litery do wnętrz – podświetlane LED lub bez. Wyślij zdjęcie elewacji, przygotujemy wycenę.",
    h1="Szyldy reklamowe w Gdyni",
    lead="Szyldy reklamowe w Gdyni wykonujemy dla sklepów, lokali usługowych, gabinetów, restauracji i biur, które chcą być lepiej widoczne z ulicy. Robimy szyldy z kasetonów, litery przestrzenne na elewację i litery do wnętrz – podświetlane LED albo bez podświetlenia, od projektu po montaż. Wystarczy przesłać zdjęcie elewacji, a przygotujemy propozycję i wycenę.",
    crumb=None,
    schema="home",
    sections=[
        ("Szyldy reklamowe Gdynia – co robimy i dla kogo", """
Jesteśmy agencją reklamy zewnętrznej z Gdyni. Skupiamy się na trzech rzeczach: szyldach z kasetonów, literach przestrzennych na elewację i literach do wnętrz. Pracujemy z małymi i średnimi firmami, które potrzebują jednego szyldu nad wejściem, a także z tymi, które oznakowują kilka lokali.

Najczęściej zgłaszają się do nas:

{{who}}

**Doświadczenie:** [UZUPEŁNIJ: od kiedy działacie / ile szyldów wykonaliście – tylko prawdziwe dane].
"""),
        ("Kasetony, litery przestrzenne i litery do wnętrz", """
Kasetony reklamowe wykonujemy z podświetleniem LED. Litery przestrzenne 3D i logo do środka – w wersji podświetlanej, gdy firma ma być widoczna po zmroku, albo bez podświetlenia, gdy działa głównie w dzień lub budynek jest dobrze oświetlony.

{{offer}}

**Montaż:** [UZUPEŁNIJ: czy montujecie sami, czy przez partnera, i czy pomagacie przy zgodach od zarządcy budynku].
"""),
        ("Katalog: litery metalowe, litery do środka i neony LED", """
Nie wiesz, od czego zacząć? Wybierz kierunek z katalogu – dopasujemy wielkość, kolor i podświetlenie do Twojego logo i miejsca montażu.

{{katalog}}

[UZUPEŁNIJ: zdjęcia wzorów z katalogu – po kilka przykładów w każdej kategorii]
"""),
        ("Realizacje i opinie klientów", """
Najlepiej ocenić naszą pracę po efektach. Poniżej pokazujemy kasetony, litery przestrzenne i litery do wnętrz wykonane dla firm z Gdyni i okolic.

{{mosaic:szyld-1:big,litery-1:tall,led-1,szyld-2:wide,litery-2,led-2:wide}}

**Opinie klientów:** [UZUPEŁNIJ: prawdziwe opinie, np. z wizytówki Google, z imieniem lub nazwą firmy za zgodą klienta].
"""),
        ("Jak wygląda współpraca krok po kroku", """
Wyślij zdjęcie elewacji przez formularz obok – resztę bierzemy na siebie. Tak wygląda droga od zdjęcia do gotowego szyldu.

1. **Zapytanie** – wysyłasz zdjęcie elewacji lub miejsca na szyld, przybliżone wymiary i logo (jeśli je masz).
2. **Konsultacja i propozycja** – doradzamy: kaseton czy litery, jaki materiał, z podświetleniem czy bez – a potem przygotowujemy wycenę.
3. **Projekt i wizualizacja** – pokazujemy, jak szyld będzie wyglądał na Twoim budynku. Poprawki wprowadzamy przed produkcją.
4. **Produkcja** – po akceptacji projektu wykonujemy szyld. Czas realizacji: [UZUPEŁNIJ: typowy czas wykonania].
5. **Montaż i odbiór** – [UZUPEŁNIJ: jak wygląda montaż i odbiór u Was].

**Co przesyłasz:**

- zdjęcie elewacji zrobione na wprost – wystarczy z telefonu,
- telefon lub e-mail, żebyśmy mogli odpowiedzieć,
- opcjonalnie: przybliżone wymiary (jeśli ich nie znasz, zmierzysz je na zdjęciu w formularzu) i logo.

**Co dostajesz:**

- wycenę, w której wypisujemy, co obejmuje oferta – dodatkowe pozycje zaznaczamy osobno,
- propozycję 1–2 wariantów w różnych cenach, np. z podświetleniem i bez,
- wizualizację szyldu na zdjęciu Twojego budynku przed produkcją [UZUPEŁNIJ: czy wizualizacja jest bezpłatna],
- odpowiedź w ciągu [UZUPEŁNIJ: czas odpowiedzi, np. 1 dnia roboczego].

Jeśli nie masz jeszcze logo ani pomysłu na wygląd szyldu, napisz o tym w zapytaniu. [UZUPEŁNIJ: czy oferujecie projekt graficzny od zera].
"""),
        ("Uchwała krajobrazowa Gdyni – co sprawdzić przed montażem szyldu", """
W Gdyni obowiązuje uchwała krajobrazowa – uchwała nr LV/1678/23 Rady Miasta Gdyni, opublikowana w Dzienniku Urzędowym Województwa Pomorskiego z 27 września 2023 r., poz. 4324. Określa zasady umieszczania tablic i urządzeń reklamowych, w tym szyldów: ich wymiary, miejsce na budynku, materiały i standardy jakości.

- **Wymiary i miejsce** – szyld, kaseton czy litery przestrzenne trzeba dopasować do zasad uchwały, zanim trafią do produkcji.
- **Liczba szyldów** – ustawa krajobrazowa pozwala gminie określić także, ile szyldów może wisieć na jednym budynku.
- **Zgoda zarządcy** – niezależnie od uchwały na montaż na elewacji zwykle potrzebna jest zgoda właściciela, wspólnoty lub zarządcy budynku.
- **Aktualny tekst** – część zapisów uchwały była zaskarżana w sądzie, dlatego przed zamówieniem warto sprawdzić jej obecne brzmienie w Urzędzie Miasta Gdyni.

To ogólne wskazówki, nie porada prawna. [UZUPEŁNIJ: czy pomagacie dopasować projekt do uchwały i przygotować wizualizację do urzędu lub wspólnoty].
"""),
        ("Szyldy reklamowe w Gdyni i okolicy – obszar działania i kontakt", """
Działamy w Gdyni we wszystkich dzielnicach, od Śródmieścia, przez Redłowo i Orłowo, po Chylonię i Obłuże. [UZUPEŁNIJ: czy obsługujecie też Sopot, Gdańsk, Rumię, Redę, Wejherowo i resztę Pomorza].

{{bleed:gdynia-realizacja|Litery przestrzenne na elewacji budynku w Gdyni|Realizacja w Gdyni – [UZUPEŁNIJ: co wykonaliście, dzielnica]}}

::info
- **Telefon:** {TEL_LINK}
- **E-mail:** {EMAIL_LINK}
- **Adres:** {ADRES}
- **Godziny pracy:** {GODZINY}

Najszybciej przygotujemy wycenę, jeśli przez [formularz wyceny](#wycena) prześlesz zdjęcie miejsca montażu i przybliżone wymiary.
"""),
    ],
    faq=[
        ("Ile kosztuje szyld reklamowy w Gdyni?", "Cena zależy od rodzaju (kaseton, litery przestrzenne, litery do wnętrz), wymiarów, materiału, podświetlenia i sposobu montażu, dlatego każdy szyld wyceniamy indywidualnie. Przykładowo kaseton reklamowy 250 × 50 cm z frezowaniem wklęsłym kosztuje 4500 zł [UZUPEŁNIJ: netto/brutto], napis z liter przestrzennych 250 × 30 cm (6 liter) od 6800 zł, a logo do środka od 4800 zł – zależnie od rodzaju i wykończenia. Montaż kosztuje od 1000 zł – zależnie od miejsca montażu. Dokładną wycenę przygotujemy po otrzymaniu zdjęcia elewacji i wymiarów."),
        ("Jak długo trwa wykonanie szyldu?", "Czas realizacji zależy od rodzaju reklamy i obłożenia produkcji. Szyld bez podświetlenia zwykle powstaje szybciej niż kaseton czy litery przestrzenne z podświetleniem LED. U nas typowy czas to [UZUPEŁNIJ: liczba dni roboczych od akceptacji projektu]."),
        ("Czy zajmujecie się też montażem szyldu?", "[UZUPEŁNIJ: czy montaż wykonujecie sami, czy przez partnera, i na jakim obszarze]. Przed montażem warto sprawdzić, czy wspólnota, zarządca budynku lub urząd nie wymagają zgody na umieszczenie reklamy. Gdynia ma też uchwałę krajobrazową, która może określać zasady dla szyldów."),
        ("Podświetlany czy bez podświetlenia – co wybrać?", "Podświetlenie LED warto wybrać, jeśli lokal działa po zmroku, stoi przy ruchliwej ulicy albo konkuruje z innymi reklamami. Wersja bez podświetlenia wystarczy, gdy firma działa głównie w dzień lub budynek jest dobrze oświetlony – jest też tańsza. W wycenie możemy pokazać oba warianty."),
        ("Czy dajecie gwarancję na szyldy i podświetlenie LED?", "[UZUPEŁNIJ: okres i zakres gwarancji na szyldy oraz osobno na elementy LED i zasilacze]. W razie usterki wystarczy się z nami skontaktować, a ustalimy termin naprawy."),
        ("Nie mam logo ani projektu – czy możecie pomóc?", "Tak, wystarczy, że opiszesz swoją firmę i pokażesz zdjęcie miejsca na szyld. [UZUPEŁNIJ: czy przygotowujecie projekt graficzny od zera i czy jest wliczony w cenę]. Przed produkcją zawsze dostaniesz wizualizację do akceptacji."),
    ],
    cta="Wyślij nam zdjęcie elewacji i przybliżone wymiary, a przygotujemy propozycję i wycenę szyldu dla Twojej firmy w Gdyni.",
    links=[],
))

# ============================ SZYLDY =========================================
PAGES.append(dict(
    slug="szyldy-reklamowe-gdynia",
    mock="panel",
    title="Szyldy reklamowe Gdynia – projekt, wykonanie, montaż",
    desc="Szyldy reklamowe Gdynia – projekt, wykonanie i montaż szyldów na elewację, nad wejście i do lokalu. Podświetlane LED lub klasyczne. Zapytaj o wycenę.",
    h1="Szyldy reklamowe Gdynia",
    lead="Szyldy reklamowe w Gdyni projektujemy i wykonujemy dla sklepów, lokali usługowych, gabinetów, restauracji i biur, które chcą być lepiej widoczne z ulicy. Robimy szyldy na elewację, nad wejście, semafory oraz oznakowanie wewnątrz lokalu, z podświetleniem LED lub bez. Wycenę przygotowujemy na podstawie wymiarów, materiału i miejsca montażu.",
    crumb="Szyldy reklamowe Gdynia",
    schema=("service", "Szyldy reklamowe", "Projekt, wykonanie i montaż szyldów reklamowych w Gdyni: szyldy na elewację, kasetony, semafory, tablice i oznakowanie wnętrz, z podświetleniem LED lub bez."),
    sections=[
        ("Jakie szyldy reklamowe wykonujemy w Gdyni", f"""
Każdy szyld dobieramy do budynku, branży i tego, skąd klienci mają go zobaczyć: z chodnika, z jadącego samochodu czy z drugiej strony ulicy. Najczęściej wykonujemy:

::cards
- **Szyldy płaskie na elewację** – panel z dibondu, PCV lub plexi z nadrukiem albo oklejeniem folią, montowany nad wejściem lub na fasadzie.
- **Kasetony reklamowe** – skrzynki świetlne z podświetleniem od środka, dobrze widoczne także wieczorem.
- **Semafory (szyldy dwustronne)** – montowane prostopadle do ściany, widoczne dla osób idących wzdłuż ulicy.
- **Litery przestrzenne** – pojedyncze litery i logo 3D, podświetlane lub nie; szczegóły opisujemy na stronie o [literach przestrzennych]({L_LI}).
- **Tablice i szyldy informacyjne** – przy wejściu do biura, gabinetu czy kancelarii, często z plexi na dystansach.
- **Oznakowanie wewnątrz lokalu** – logo na ścianie za ladą, napisy na szybach, tabliczki.

Jeśli nie wiesz, który rodzaj będzie najlepszy, wystarczy zdjęcie elewacji i przybliżone wymiary – zaproponujemy rozwiązanie i pokażemy wizualizację.
"""),
        ("Materiały i wykończenia szyldów", """
Od materiału zależy wygląd, trwałość i cena szyldu. Na zewnątrz stosujemy materiały odporne na wilgoć, słońce i nadmorski wiatr, który w Gdyni potrafi dać się we znaki.

- **Dibond (kompozyt aluminiowy)** – sztywny, lekki, odporny na warunki atmosferyczne; dobry wybór na szyldy płaskie.
- **Plexi (pleksa)** – mleczna, przezroczysta lub kolorowa; sprawdza się w szyldach podświetlanych i eleganckich tablicach.
- **PCV spienione** – ekonomiczne rozwiązanie, szczególnie do wnętrz i szyldów tymczasowych.
- **Stal nierdzewna i aluminium** – do liter i elementów o bardziej prestiżowym wyglądzie.
- **Folie i nadruki** – folie wylewane i nadruk UV, które dłużej zachowują kolor.

Wykończenie dobieramy do identyfikacji wizualnej firmy: kolory z księgi znaku, mat lub połysk, frezowane logo, elementy wypukłe. [UZUPEŁNIJ: czy macie własny park maszynowy – ploter, frezarka CNC, drukarka UV]
"""),
        ("Szyld z podświetleniem czy bez – jak wybrać", f"""
**Szyld podświetlany** warto wybrać, jeśli lokal działa po zmroku, stoi przy ruchliwej ulicy albo konkuruje z innymi reklamami w okolicy. Stosujemy energooszczędne moduły LED, które zużywają niewiele prądu i świecą równomiernie. Więcej o tym rozwiązaniu piszemy w zakładce o [reklamie LED]({L_LED}).

**Szyld bez podświetlenia** wystarczy, gdy firma działa głównie w dzień, budynek jest dobrze oświetlony lub zależy Ci na niższym koszcie. Możliwe jest też rozwiązanie pośrednie – oświetlenie szyldu z zewnątrz reflektorami.

Przy wyborze pomogą trzy pytania: w jakich godzinach przychodzą klienci, z jakiej odległości szyld ma być czytelny i czy w miejscu montażu jest dostęp do zasilania.
"""),
        ("Realizacje szyldów reklamowych w Gdyni", f"""
Wykonaliśmy szyldy dla [UZUPEŁNIJ: branże klientów, np. gastronomia, usługi, handel] w Gdyni [UZUPEŁNIJ: dzielnice lub miejscowości, np. Śródmieście, Orłowo, Rumia]. Zdjęcia przed i po montażu znajdziesz w zakładce [Realizacje]({L_RE}).

{{{{gallery:szyld-1,szyld-2}}}}

Jak wygląda współpraca:

1. Kontakt i przesłanie zdjęć elewacji lub wnętrza z wymiarami.
2. Projekt i wizualizacja szyldu na zdjęciu Twojego budynku.
3. Wycena i akceptacja projektu.
4. Produkcja szyldu – [UZUPEŁNIJ: orientacyjny czas realizacji].
5. Montaż na miejscu – [UZUPEŁNIJ: czy montaż wykonuje własna ekipa, czy dysponujecie podnośnikiem].

**Obszar działania:** Gdynia oraz [UZUPEŁNIJ: czy także Sopot, Gdańsk, Rumia, Reda, Wejherowo].
"""),
        ("Od czego zależy cena szyldu", f"""
Cena szyldu reklamowego zależy od kilku czynników, dlatego każdą realizację wyceniamy indywidualnie:

- wymiary szyldu i liczba elementów,
- materiał – PCV, dibond, plexi, stal,
- podświetlenie LED i doprowadzenie zasilania,
- stopień skomplikowania – frezowanie, litery 3D, nietypowe kształty,
- projekt graficzny – czy masz gotowe logo, czy trzeba je przygotować,
- montaż – wysokość, rodzaj ściany, potrzeba podnośnika lub rusztowania.

Orientacyjne ceny znajdziesz w [cenniku]({L_CE}): [UZUPEŁNIJ: przykładowe widełki cenowe lub ceny „od”]. Dokładną wycenę przygotujemy po otrzymaniu zdjęcia i wymiarów.
"""),
        ("Formalności przed montażem szyldu reklamowego w Gdyni", """
Zanim szyld zawiśnie na budynku, warto sprawdzić kilka spraw. To ogólne wskazówki, nie porada prawna – szczegóły najlepiej potwierdzić w urzędzie lub u zarządcy budynku.

- **Zgoda właściciela lub wspólnoty** – przy montażu na elewacji zwykle potrzebna jest zgoda zarządcy nieruchomości.
- **Uchwała krajobrazowa** – warto sprawdzić, czy w gminie obowiązują przepisy określające wielkość, wygląd i miejsce szyldów.
- **Ochrona konserwatorska** – w zabytkowych częściach miasta, np. w modernistycznym śródmieściu Gdyni, mogą obowiązywać dodatkowe wymagania.
- **Zgłoszenia i opłaty** – w zależności od wielkości i sposobu montażu może być potrzebne zgłoszenie lub opłata.

[UZUPEŁNIJ: czy pomagacie w przygotowaniu dokumentacji, np. wizualizacji do zgłoszenia]
"""),
    ],
    faq=[
        ("Ile kosztuje szyld reklamowy w Gdyni?", "Cena zależy od wymiarów, materiału, podświetlenia i sposobu montażu. Prosty szyld płaski kosztuje zwykle znacznie mniej niż kaseton czy litery przestrzenne z LED. Orientacyjnie: [UZUPEŁNIJ: ceny od]. Dokładną wycenę przygotujemy po otrzymaniu zdjęcia elewacji i wymiarów."),
        ("Jak długo trwa wykonanie szyldu?", "Czas realizacji zależy od rodzaju szyldu i akceptacji projektu. Standardowo jest to [UZUPEŁNIJ: liczba dni roboczych] od zatwierdzenia wizualizacji. Przy pilnych zleceniach zapytaj o możliwość szybszego terminu."),
        ("Czy zajmujecie się także montażem szyldu?", "[UZUPEŁNIJ: czy montaż jest w ofercie i na jakim obszarze]. Przed montażem oceniamy rodzaj ściany, wysokość i dostęp do zasilania, żeby dobrać właściwe mocowanie."),
        ("Jaka jest gwarancja na szyldy reklamowe?", "Na wykonane szyldy udzielamy gwarancji [UZUPEŁNIJ: okres i zakres gwarancji, osobno dla konstrukcji i modułów LED]. Szczegóły zapisujemy w ofercie."),
        ("Z jakiego materiału najlepiej zrobić szyld na zewnątrz?", "Na zewnątrz najczęściej polecamy dibond lub plexi, bo dobrze znoszą deszcz, słońce i wiatr. Do szyldów podświetlanych świetnie sprawdza się mleczna plexi z modułami LED. PCV lepiej zostawić do wnętrz lub reklam krótkoterminowych."),
        ("Czy możecie zaprojektować szyld, jeśli nie mam logo?", "[UZUPEŁNIJ: czy oferujecie projekt logo]. Jeśli masz gotowe logo, prześlij je najlepiej w formie wektorowej (PDF, AI, SVG) – przygotujemy na jego podstawie wizualizację szyldu na Twoim budynku."),
    ],
    cta="Wyślij nam zdjęcie elewacji i przybliżone wymiary – przygotujemy wizualizację i wycenę szyldu reklamowego dla Twojej firmy w Gdyni.",
    links=["litery-przestrzenne-gdynia", "reklama-led-gdynia", "realizacje", "cennik"],
))

# ============================ LITERY =========================================
PAGES.append(dict(
    slug="litery-przestrzenne-gdynia",
    mock="3d",
    title="Litery przestrzenne Gdynia – litery 3D na fasadę i do wnętrz",
    desc="Litery przestrzenne Gdynia – litery 3D na fasadę, recepcję i do biura, z podświetleniem LED lub bez. Projekt, produkcja i montaż. Poproś o wycenę.",
    h1="Litery przestrzenne Gdynia",
    lead="Litery przestrzenne w Gdyni wykonujemy dla sklepów, lokali usługowych, biur i firm, które chcą wyraźnie oznaczyć fasadę, witrynę lub recepcję. Robimy litery 3D z podświetleniem LED lub bez, na zewnątrz i do wnętrz, od projektu po montaż na miejscu. Działamy w Gdyni i okolicach Trójmiasta [UZUPEŁNIJ: dokładny obszar działania].",
    crumb="Litery przestrzenne Gdynia",
    schema=("service", "Litery przestrzenne", "Projekt, produkcja i montaż liter przestrzennych 3D w Gdyni – na fasadę, witrynę, recepcję i do biura, z podświetleniem LED lub bez."),
    sections=[
        ("Czym są litery przestrzenne", f"""
Litery przestrzenne (nazywane też literami 3D) to pojedyncze, wycinane znaki z widoczną grubością, montowane bezpośrednio na ścianie, elewacji, panelu lub konstrukcji. W przeciwieństwie do płaskiej tablicy czy naklejki tworzą trójwymiarowy napis, który dobrze wygląda z bliska i z daleka, także pod kątem.

Z liter przestrzennych można ułożyć nazwę firmy, logo, hasło albo pojedynczy znak graficzny. To jedna z najczęściej wybieranych form oznakowania przy szyldach na fasadę, bo wygląda elegancko, a jednocześnie nie zasłania architektury budynku. Więcej o innych formach oznakowania przeczytasz na stronie [szyldy reklamowe Gdynia]({L_SZ}).
"""),
        ("Rodzaje liter przestrzennych – podświetlane i niepodświetlane", f"""
Rodzaj liter dobieramy do miejsca montażu, budżetu i tego, czy napis ma być widoczny po zmroku. Najczęściej wybierane warianty to:

{{{{letters}}}}

::cards
- **Litery niepodświetlane** – prostsze i tańsze, dobre do wnętrz, na recepcję lub na dobrze oświetloną elewację.
- **Litery podświetlane od frontu** – świeci lico litery, napis jest czytelny wieczorem i z dużej odległości.
- **Litery z podświetleniem tylnym (efekt halo)** – światło pada na ścianę za literą, dając dyskretną, elegancką poświatę.
- **Litery z podświetleniem frontu i boków** – maksymalna widoczność, np. przy ruchliwej ulicy.

W zależności od projektu stosujemy m.in. plexi, PCV spienione, dibond, aluminium lub stal nierdzewną [UZUPEŁNIJ: potwierdź materiały, z których wykonujecie litery]. Do podświetlenia używamy modułów LED – szczegóły znajdziesz na stronie [reklama LED Gdynia]({L_LED}).
"""),
        ("Gdzie sprawdzają się litery przestrzenne w Gdyni: fasada, recepcja, biuro", """
Litery 3D pasują zarówno do lokalu przy głównej ulicy, jak i do biura w biurowcu. Typowe zastosowania to:

::cards
- **Fasada i elewacja** – nazwa sklepu, restauracji, salonu czy gabinetu nad wejściem lub na ścianie budynku.
- **Witryna i nadproże** – napis nad drzwiami albo na pasie nad oknem wystawowym.
- **Recepcja** – logo za ladą, które klient widzi od razu po wejściu.
- **Biuro i sala konferencyjna** – logo lub hasło firmy na ścianie, często bez podświetlenia albo z efektem halo.
- **Hale, salony i obiekty usługowe** – duże napisy widoczne z parkingu lub drogi.

Przy montażu na zewnątrz warto wcześniej sprawdzić, czy potrzebna jest zgoda wspólnoty, zarządcy budynku lub urzędu – podpowiemy, o co zapytać.
"""),
        ("Realizacje liter przestrzennych w Gdyni", f"""
Najlepiej ocenić efekt, oglądając gotowe projekty. W naszej galerii pokazujemy litery na fasadach, w witrynach i we wnętrzach, z różnymi rodzajami podświetlenia.

{{{{gallery:litery-1,litery-2}}}}

[UZUPEŁNIJ: 2–4 krótkie opisy realizacji – branża klienta, miejsce montażu, materiał, rodzaj podświetlenia, zdjęcie]

Pełną listę projektów znajdziesz w zakładce [realizacje]({L_RE}).
"""),
        ("Od czego zależy cena liter przestrzennych", f"""
Każdy napis wyceniamy indywidualnie, bo na koszt wpływa kilka czynników:

- **Wielkość i liczba znaków** – wysokość liter i długość napisu.
- **Materiał** – PCV czy plexi są tańsze niż aluminium lub stal nierdzewna.
- **Podświetlenie** – litery z LED kosztują więcej niż niepodświetlane; znaczenie ma też rodzaj świecenia (front, halo, boki).
- **Kształt i szczegóły** – skomplikowane logo i drobne elementy wymagają więcej pracy.
- **Montaż** – wysokość, rodzaj podłoża, konieczność użycia podnośnika i doprowadzenia zasilania.
- **Projekt** – czy masz gotowe logo w wersji wektorowej, czy trzeba je przygotować.

Orientacyjne ceny: [UZUPEŁNIJ: przedziały cenowe lub cena od]. Więcej informacji znajdziesz w [cenniku]({L_CE}).
"""),
        ("Jak wygląda współpraca i gdzie działamy", """
Proces jest prosty i przejrzysty:

1. **Zapytanie** – przesyłasz logo, wymiary ściany lub zdjęcie miejsca montażu.
2. **Wycena i wizualizacja** – proponujemy materiał, wielkość i rodzaj podświetlenia, pokazujemy, jak napis będzie wyglądał na budynku.
3. **Produkcja** – po akceptacji wykonujemy litery [UZUPEŁNIJ: czy we własnej pracowni].
4. **Montaż** – instalujemy litery na miejscu i podłączamy podświetlenie.

Litery przestrzenne wykonujemy przede wszystkim dla firm z Gdyni, a także z Sopotu, Gdańska i okolic [UZUPEŁNIJ: potwierdź obszar działania i ewentualną wysyłkę liter na terenie Polski].
"""),
    ],
    faq=[
        ("Ile kosztują litery przestrzenne w Gdyni?", "Cena zależy od wielkości i liczby liter, materiału, rodzaju podświetlenia oraz warunków montażu. Orientacyjnie: [UZUPEŁNIJ: cena od / przedziały cenowe]. Dokładną wycenę przygotujemy po otrzymaniu logo i wymiarów miejsca montażu."),
        ("Ile trwa wykonanie liter przestrzennych?", "Standardowy czas realizacji to [UZUPEŁNIJ: liczba dni roboczych] od akceptacji projektu. Termin może się wydłużyć przy dużych napisach, nietypowych materiałach lub konieczności uzyskania zgód na montaż na elewacji."),
        ("Czy zajmujecie się montażem liter na fasadzie?", "Tak, montujemy litery na elewacji, w witrynie i we wnętrzach, łącznie z podłączeniem podświetlenia LED [UZUPEŁNIJ: potwierdź zakres montażu i ewentualne prace na wysokości]. Przed montażem na zewnątrz warto mieć zgodę wspólnoty lub zarządcy budynku."),
        ("Z jakiego materiału najlepiej zrobić litery na zewnątrz?", "Na zewnątrz sprawdzają się materiały odporne na wilgoć, mróz i słońce, np. aluminium, stal nierdzewna, dibond lub odpowiednio zabezpieczone plexi. Do wnętrz można zastosować lżejsze i tańsze materiały, np. PCV spienione. Dobierzemy materiał do miejsca i budżetu."),
        ("Jaką gwarancję dajecie na litery przestrzenne i podświetlenie LED?", "Na litery przestrzenne udzielamy gwarancji [UZUPEŁNIJ: okres gwarancji na wykonanie], a na elementy podświetlenia LED [UZUPEŁNIJ: okres gwarancji na LED i zasilacze]. Szczegółowe warunki przekazujemy razem z ofertą."),
        ("Czy muszę mieć gotowy projekt lub logo?", "Nie, ale najlepiej, jeśli masz logo w wersji wektorowej (np. PDF, AI, SVG). Jeśli go nie masz, pomożemy przygotować plik do produkcji [UZUPEŁNIJ: czy i na jakich zasadach przygotowujecie projekt]."),
    ],
    cta="Wyślij nam logo i zdjęcie miejsca montażu, a przygotujemy wycenę liter przestrzennych dla Twojej firmy w Gdyni.",
    links=["szyldy-reklamowe-gdynia", "reklama-led-gdynia", "realizacje", "cennik"],
))

# ============================ LED ============================================
PAGES.append(dict(
    slug="reklama-led-gdynia",
    mock="led",
    title="Reklama LED Gdynia – podświetlane szyldy i kasetony",
    desc="Reklama LED Gdynia – podświetlane szyldy, kasetony i litery LED dla firm. Widoczne także po zmroku. Sprawdź, od czego zależy cena i zapytaj o wycenę.",
    h1="Reklama LED w Gdyni",
    lead="Reklama LED w Gdyni to podświetlane szyldy, kasetony i litery przestrzenne, które wykonujemy dla sklepów, lokali usługowych, gabinetów i biur. Projektujemy, produkujemy i montujemy oznakowanie widoczne w dzień i po zmroku, na terenie Gdyni i Trójmiasta. Nie zajmujemy się ekranami LED – nasza specjalność to podświetlana reklama zewnętrzna na elewację i witrynę [UZUPEŁNIJ: potwierdź zakres, np. czy wykonujecie ekrany LED].",
    crumb="Reklama LED Gdynia",
    schema=("service", "Reklama LED", "Podświetlane szyldy, kasetony i litery przestrzenne LED w Gdyni – projekt, produkcja i montaż oznakowania widocznego w dzień i po zmroku."),
    sections=[
        ("Reklama LED w Gdyni – co wykonujemy", f"""
Hasło „reklama LED” bywa rozumiane różnie – czasem chodzi o ekrany wyświetlające filmy, czasem o podświetlane oznakowanie firmy. My zajmujemy się tym drugim: szyldami i literami podświetlanymi diodami LED, które montujemy na elewacjach, nad wejściem i w witrynach.

W naszej ofercie znajdziesz:

::cards
- **Kasetony podświetlane** – skrzynki z grafiką na froncie, podświetlone od środka [UZUPEŁNIJ: jednostronne / dwustronne, kasetony semaforowe].
- **Litery przestrzenne LED** – pojedyncze litery i logo podświetlane od frontu, od tyłu (efekt halo) lub z boków [UZUPEŁNIJ: jakie warianty podświetlenia wykonujecie].
- **Szyldy podświetlane** – tablice z logo i nazwą firmy z podświetleniem LED [UZUPEŁNIJ: rodzaje, np. dibond z literami, plexi].
- **Modernizację starych reklam** – wymianę świetlówek na moduły LED w istniejących kasetonach [UZUPEŁNIJ: czy wykonujecie].

Jeśli szukasz oznakowania bez podświetlenia, zajrzyj na stronę o [szyldach reklamowych w Gdyni]({L_SZ}).
"""),
        ("Zalety podświetlenia LED w reklamie zewnętrznej", """
Podświetlenie LED zastąpiło dawne świetlówki i neony gazowe w większości reklam zewnętrznych. Powody są praktyczne:

- **Widoczność po zmroku** – w sezonie jesienno-zimowym robi się ciemno już po południu, a podświetlony szyld nadal pracuje na Twoją firmę.
- **Niższe zużycie prądu** – diody LED pobierają wyraźnie mniej energii niż świetlówki o podobnej jasności.
- **Długa żywotność** – moduły LED rzadziej wymagają wymiany, więc reklama mniej kosztuje w utrzymaniu.
- **Równomierne światło** – dobrze zaprojektowany kaseton świeci jednolicie, bez ciemnych plam i prześwitów.
- **Swoboda projektu** – LED-y mieszczą się w płytkich obudowach i małych literach, dzięki czemu reklama może być smukła i elegancka.

Podświetlenie można sterować zegarem lub czujnikiem zmierzchu, żeby reklama włączała się tylko wtedy, gdy jest potrzebna [UZUPEŁNIJ: czy montujecie sterowniki].
"""),
        ("Kaseton, litery LED czy szyld podświetlany – co wybrać?", f"""
Każde z rozwiązań sprawdza się w innej sytuacji. Krótkie porównanie:

::cards
- **Kaseton podświetlany** – dużo miejsca na grafikę i tekst, dobra widoczność z daleka, rozsądny koszt przy większym formacie. Dobry wybór dla sklepów, punktów usługowych i lokali przy ruchliwych ulicach.
- **Litery przestrzenne LED** – najbardziej prestiżowy efekt, logo „wychodzi” z elewacji. Polecane dla marek, które chcą wyglądać nowocześnie: salony, restauracje, biura, kliniki. Zwykle droższe od kasetonu o podobnej szerokości.
- **Szyld podświetlany** – kompromis między ceną a wyglądem, np. tablica z podświetlanym logo. Sprawdza się przy mniejszych wejściach i w lokalach w kamienicach.

Przy wyborze warto wziąć pod uwagę także wymagania wspólnoty lub zarządcy budynku oraz ewentualne zasady miejskiego planu reklamowego. Pomożemy dobrać rozwiązanie do miejsca, budżetu i charakteru firmy. Więcej o samych literach przeczytasz na stronie o [literach przestrzennych w Gdyni]({L_LI}).
"""),
        ("Jak wygląda współpraca i nasze realizacje", f"""
Proces jest prosty i zwykle przebiega w kilku krokach:

1. **Kontakt i zdjęcie elewacji** – wysyłasz nam zdjęcie miejsca, wymiary i logo.
2. **Wizualizacja i wycena** – przygotowujemy projekt reklamy na zdjęciu Twojego budynku [UZUPEŁNIJ: czy wizualizacja jest bezpłatna].
3. **Produkcja** – wykonujemy reklamę [UZUPEŁNIJ: we własnej pracowni / u podwykonawcy, z jakich materiałów, np. plexi, dibond, profile aluminiowe].
4. **Montaż i podłączenie** – montujemy reklamę na miejscu i uruchamiamy podświetlenie [UZUPEŁNIJ: kto wykonuje podłączenie elektryczne].

Działamy w Gdyni oraz [UZUPEŁNIJ: obszar, np. Sopot, Gdańsk, Rumia, Reda, Wejherowo]. Przykłady wykonanych reklam podświetlanych znajdziesz w zakładce z [realizacjami]({L_RE}) [UZUPEŁNIJ: 2–3 przykładowe realizacje LED ze zdjęciami].

{{{{gallery:led-1,led-2}}}}
"""),
        ("Reklama LED Gdynia – koszty i od czego zależą", f"""
Cena podświetlanej reklamy jest zawsze liczona indywidualnie, bo każda elewacja i każde logo są inne. Na koszt wpływają przede wszystkim:

- **Rozmiar** – szerokość i wysokość kasetonu lub liter.
- **Rodzaj reklamy** – litery przestrzenne są zwykle bardziej pracochłonne niż kaseton.
- **Sposób podświetlenia** – front, halo, boki, podświetlenie jedno- lub dwustronne.
- **Materiały** – rodzaj plexi, lica, obudowy i folii.
- **Skomplikowanie logo** – drobne detale i nietypowe kształty wydłużają produkcję.
- **Montaż** – wysokość, rodzaj elewacji, konieczność użycia podnośnika.

Orientacyjne ceny: [UZUPEŁNIJ: np. kaseton od … zł, litery LED od … zł za literę/mb]. Szczegóły znajdziesz w [cenniku]({L_CE}), a dokładną wycenę przygotujemy po otrzymaniu zdjęcia i wymiarów.
"""),
    ],
    faq=[
        ("Ile kosztuje reklama LED w Gdyni?", "Cena zależy od rozmiaru, rodzaju reklamy, sposobu podświetlenia i montażu. Orientacyjnie: [UZUPEŁNIJ: widełki cenowe]. Dokładną wycenę przygotujemy po otrzymaniu zdjęcia elewacji i wymiarów."),
        ("Jak długo trwa wykonanie podświetlanego szyldu lub kasetonu?", "Czas realizacji zależy od wielkości i stopnia skomplikowania projektu. Standardowo wynosi [UZUPEŁNIJ: liczba dni roboczych] od akceptacji projektu. Termin montażu ustalamy z Tobą indywidualnie."),
        ("Czy zajmujecie się montażem i podłączeniem do prądu?", "Tak, montujemy reklamy na terenie Gdyni i okolic [UZUPEŁNIJ: potwierdź]. [UZUPEŁNIJ: kto wykonuje podłączenie elektryczne – Wasz elektryk czy elektryk klienta]. Przed montażem sprawdzamy rodzaj elewacji i dostęp do zasilania."),
        ("Jaką gwarancję dajecie na reklamę LED?", "Na wykonane reklamy udzielamy gwarancji [UZUPEŁNIJ: okres gwarancji na konstrukcję i na podświetlenie LED]. W razie awarii modułów LED lub zasilacza zapewniamy serwis [UZUPEŁNIJ: warunki serwisu]."),
        ("Czy robicie ekrany LED?", "Nasza specjalność to podświetlane szyldy, kasetony i litery przestrzenne LED, a nie ekrany wyświetlające obraz [UZUPEŁNIJ: potwierdź]. Jeśli nie wiesz, które rozwiązanie będzie lepsze dla Twojej firmy, chętnie doradzimy."),
        ("Czy potrzebuję zgody na montaż reklamy na budynku?", "Często tak – zgody może wymagać wspólnota, zarządca budynku lub urząd, zwłaszcza w strefach objętych zasadami lokalizacji reklam. Warto to sprawdzić przed zamówieniem. Na życzenie przygotujemy wizualizację, którą możesz dołączyć do wniosku [UZUPEŁNIJ: czy pomagacie w formalnościach]."),
    ],
    cta="Wyślij nam zdjęcie elewacji i logo – przygotujemy propozycję podświetlanej reklamy LED dla Twojej firmy w Gdyni wraz z wyceną.",
    links=["szyldy-reklamowe-gdynia", "litery-przestrzenne-gdynia", "realizacje", "cennik"],
))

# ============================ REALIZACJE =====================================
PAGES.append(dict(
    slug="realizacje",
    title="Szyldy reklamowe realizacje Gdynia – zdjęcia i opisy prac",
    desc="Szyldy reklamowe realizacje Gdynia: zobacz zdjęcia szyldów, liter przestrzennych i reklam LED z opisem materiałów. Wybierz rozwiązanie dla firmy.",
    h1="Szyldy reklamowe – realizacje w Gdyni",
    lead="Na tej stronie pokazujemy nasze szyldy reklamowe – realizacje z Gdyni i okolic, czyli zdjęcia wykonanych i zamontowanych szyldów, liter przestrzennych oraz reklam LED. Przy każdej realizacji opisujemy, co zrobiliśmy, z jakich materiałów i w jakiej części miasta. To dobry punkt wyjścia dla właścicieli sklepów, lokali usługowych, gabinetów i biur, którzy chcą zobaczyć gotowe efekty, zanim zamówią oznakowanie.",
    crumb="Realizacje",
    schema=None,
    sections=[
        ("Szyldy reklamowe – realizacje w Gdyni według kategorii", f"""
Galerię podzieliliśmy na kategorie, żeby łatwo było znaleźć przykłady podobne do tego, czego potrzebujesz. Każde zdjęcie przedstawia rzeczywistą realizację – bez wizualizacji i zdjęć ze stocków.

::cards
- **Szyldy reklamowe** – płaskie szyldy na elewację, kasetony, tablice przy wejściu, oznakowanie witryn. Więcej o tej usłudze: [szyldy reklamowe Gdynia]({L_SZ}).
- **Litery przestrzenne** – logo i napisy 3D, nieoświetlone i podświetlane, montowane na elewacji, ścianie w lokalu lub na konstrukcji. Szczegóły: [litery przestrzenne Gdynia]({L_LI}).
- **Reklama LED** – podświetlane kasetony, litery z diodami LED, neony LED i oznakowanie widoczne po zmroku. Zobacz: [reklama LED Gdynia]({L_LED}).

[UZUPEŁNIJ: liczba realizacji w każdej kategorii lub informacja, jak często dodajecie nowe zdjęcia]
"""),
        ("Galeria: szyldy reklamowe", "{{gallery:szyld-1,szyld-2}}"),
        ("Galeria: litery przestrzenne", "{{gallery:litery-1,litery-2}}"),
        ("Galeria: reklama LED", "{{gallery:led-1,led-2}}"),
        ("Co znajdziesz w opisie każdej realizacji", """
Samo zdjęcie nie mówi wszystkiego. Dlatego pod każdą realizacją podajemy krótki, konkretny opis, który pomaga ocenić, czy podobne rozwiązanie sprawdzi się u Ciebie:

- **Co wykonaliśmy** – rodzaj oznakowania, wymiary, liczba elementów.
- **Gdzie** – dzielnica Gdyni lub ulica (podajemy ją tylko za zgodą klienta), np. Śródmieście, Redłowo, Orłowo, Chylonia.
- **Z czego** – użyte materiały, rodzaj podświetlenia, sposób montażu.
- **Zakres prac** – projekt, produkcja, montaż, demontaż starego szyldu, ewentualna pomoc przy formalnościach.

Przykładowy opis realizacji:

::info
- **Klient / branża:** [UZUPEŁNIJ: np. kawiarnia, salon fryzjerski]
- **Lokalizacja:** [UZUPEŁNIJ: dzielnica lub ulica – za zgodą klienta]
- **Wykonanie:** [UZUPEŁNIJ: np. litery przestrzenne podświetlane LED, wymiary]
- **Materiały:** [UZUPEŁNIJ: np. plexi, dibond, profil aluminiowy]
- **Czas realizacji:** [UZUPEŁNIJ: od akceptacji projektu do montażu]
"""),
        ("Z jakich materiałów wykonujemy szyldy i reklamy", """
Dobór materiału zależy od miejsca montażu, ekspozycji na słońce i wiatr oraz od tego, czy reklama ma być widoczna również wieczorem. W nadmorskim klimacie Gdyni szczególnie ważna jest odporność na wilgoć i sól w powietrzu.

- **Dibond (płyta kompozytowa)** – sztywny, lekki, odporny na warunki atmosferyczne, dobry na szyldy płaskie.
- **Plexi (PMMA)** – do liter przestrzennych i elementów podświetlanych, dobrze rozprasza światło.
- **PCV spienione** – ekonomiczna opcja, głównie do wnętrz i oznakowania wewnętrznego.
- **Profile aluminiowe i stal** – do kasetonów, konstrukcji i liter o podwyższonej trwałości.
- **Moduły i taśmy LED** – energooszczędne podświetlenie liter i kasetonów.

[UZUPEŁNIJ: potwierdź listę materiałów, z którymi faktycznie pracujecie, i dopisz ewentualne folie, producentów LED itp.]
"""),
        ("Jak wygląda współpraca i od czego zależy cena", f"""
Każda realizacja z galerii przeszła podobną drogę:

1. **Kontakt i wycena wstępna** – przesyłasz zdjęcie elewacji lub miejsca montażu, wymiary i logo.
2. **Projekt** – przygotowujemy wizualizację na zdjęciu Twojego budynku lub lokalu.
3. **Akceptacja i produkcja** – po zatwierdzeniu projektu wykonujemy oznakowanie.
4. **Montaż** – instalujemy szyld na miejscu, podłączamy podświetlenie, w razie potrzeby demontujemy stary.

Na cenę wpływają przede wszystkim: wielkość i liczba elementów, rodzaj materiału, podświetlenie LED lub jego brak, wysokość i trudność montażu (np. konieczność użycia podnośnika) oraz stopień skomplikowania projektu. Orientacyjne stawki znajdziesz w [cenniku]({L_CE}). [UZUPEŁNIJ: czy wycena i projekt są bezpłatne]
"""),
        ("Realizacje szyldów reklamowych w Gdyni i okolicy", f"""
Większość prezentowanych prac powstała w Gdyni – w różnych dzielnicach, od ścisłego centrum po osiedla mieszkaniowe. Wykonujemy też oznakowanie dla firm z pozostałej części Trójmiasta i powiatu.

**Obszar działania:** [UZUPEŁNIJ: np. Gdynia, Sopot, Gdańsk, Rumia, Reda, Wejherowo – potwierdź zasięg montażu]

Jeśli zobaczysz w galerii rozwiązanie, które Ci się podoba, napisz, o którą realizację chodzi – przygotujemy podobny projekt dopasowany do Twojej elewacji, kolorów i budżetu. Dane kontaktowe znajdziesz na stronie [kontakt]({L_KO}).
"""),
    ],
    faq=[
        ("Ile kosztuje szyld reklamowy podobny do tych z galerii?", "Cena zależy od wymiarów, materiału, podświetlenia i warunków montażu, dlatego dwa podobnie wyglądające szyldy mogą różnić się kosztem. Orientacyjne ceny są w cenniku, a dokładną wycenę przygotujemy po otrzymaniu zdjęcia miejsca montażu i wymiarów. [UZUPEŁNIJ: cena od / przykładowy przedział]"),
        ("Ile trwa wykonanie i montaż szyldu?", "Czas zależy od rodzaju oznakowania i aktualnego obłożenia pracowni. Prosty szyld płaski powstaje szybciej niż litery przestrzenne z podświetleniem LED. [UZUPEŁNIJ: typowy czas realizacji od akceptacji projektu]"),
        ("Czy zajmujecie się montażem na elewacji?", "Tak, montaż jest częścią naszych realizacji – instalujemy szyldy, litery i kasetony na miejscu oraz podłączamy podświetlenie. Przy montażu na wysokości dobieramy odpowiedni sprzęt. [UZUPEŁNIJ: czy montaż na wysokości / podnośnik jest w cenie]"),
        ("Jaką gwarancję dajecie na szyldy i podświetlenie LED?", "Gwarancja obejmuje wykonanie oraz zastosowane elementy, w tym podświetlenie. Szczegółowe warunki ustalamy przy zamówieniu. [UZUPEŁNIJ: okres gwarancji na wykonanie i na LED]"),
        ("Czy na szyld potrzebna jest zgoda?", "Często tak – zgody może wymagać zarządca lub wspólnota budynku, a w niektórych lokalizacjach także przepisy miejskie lub konserwator zabytków. Warto to sprawdzić przed zamówieniem. [UZUPEŁNIJ: czy pomagacie w formalnościach i przygotowaniu projektu do zgłoszenia]"),
        ("Czy mogę zobaczyć realizację na żywo?", "Przy części realizacji podajemy dzielnicę lub ulicę – robimy to wyłącznie za zgodą klienta, więc takie szyldy można obejrzeć osobiście. Jeśli interesuje Cię konkretny typ oznakowania, zapytaj, gdzie w Gdyni możesz zobaczyć podobny przykład."),
    ],
    cta="Spodobała Ci się któraś realizacja? Wyślij nam zdjęcie swojej elewacji, a przygotujemy projekt i wycenę szyldu dla Twojej firmy w Gdyni.",
    links=["szyldy-reklamowe-gdynia", "litery-przestrzenne-gdynia", "reklama-led-gdynia", "kontakt"],
))

# ============================ CENNIK =========================================
PAGES.append(dict(
    slug="cennik",
    title="Szyldy reklamowe cena Gdynia – cennik i wycena szyldu",
    desc="Szyldy reklamowe cena Gdynia: sprawdź, od czego zależy koszt szyldu, liter przestrzennych i reklamy LED. Wyślij zdjęcie elewacji – przygotujemy wycenę.",
    h1="Cennik szyldów reklamowych w Gdyni – od czego zależy cena",
    lead="Szyldy reklamowe w Gdyni mają cenę zależną głównie od wymiarów, materiału, podświetlenia i sposobu montażu, dlatego każdą realizację wyceniamy indywidualnie. Orientacyjnie prosty szyld płaski kosztuje [UZUPEŁNIJ: widełki cen szyldu płaskiego], a szyld z literami przestrzennymi lub podświetleniem LED [UZUPEŁNIJ: widełki cen szyldu podświetlanego]. Wycenę dla firm z Gdyni i okolic przygotujemy na podstawie zdjęcia elewacji, wymiarów i logo.",
    crumb="Cennik",
    schema=None,
    cta_href="#formularz",
    sections=[
        ("Szyldy reklamowe – cena w Gdyni zależy od kilku czynników", """
Nie ma jednej stawki za szyld, bo dwa szyldy o tej samej szerokości mogą różnić się kosztem kilkukrotnie. Na cenę wpływają przede wszystkim:

- **Wymiary** – im większa powierzchnia szyldu lub wyższe litery, tym więcej materiału i pracy. Duże formaty mogą też wymagać mocniejszej konstrukcji nośnej.
- **Materiał** – inaczej kosztuje szyld z płyty kompozytowej z nadrukiem lub folią, inaczej z plexi, PCV czy z liter przestrzennych frezowanych lub giętych.
- **Podświetlenie** – szyld bez oświetlenia jest najtańszy. Podświetlenie LED (od frontu, od tyłu w formie poświaty lub kaseton świetlny) podnosi koszt, ale sprawia, że reklama działa także po zmroku.
- **Liczba elementów i stopień skomplikowania** – rozbudowane logo, wiele kolorów, drobne detale czy nietypowe kształty wymagają więcej pracy przy produkcji.
- **Montaż** – znaczenie ma wysokość, rodzaj elewacji (tynk, docieplenie, cegła, blacha), dostęp do miejsca montażu oraz to, czy potrzebny jest podnośnik lub doprowadzenie zasilania.

Warto też pamiętać o formalnościach: przed montażem dobrze jest sprawdzić zgodę zarządcy budynku lub wspólnoty oraz lokalne przepisy dotyczące reklam na danym obszarze Gdyni. [UZUPEŁNIJ: czy firma pomaga w formalnościach i zgodach]
"""),
        ("Przykładowe widełki cen szyldów reklamowych w Gdyni", """
Poniższe kwoty mają charakter orientacyjny i pomagają ocenić budżet przed zapytaniem. Dokładną cenę podajemy po obejrzeniu zdjęcia elewacji i projektu.

::price
- **Szyld płaski z płyty kompozytowej z nadrukiem lub folią** – [UZUPEŁNIJ: cena od–do, np. za m² lub za sztukę]
- **Kaseton podświetlany LED** – [UZUPEŁNIJ: cena od–do]
- **Litery przestrzenne bez podświetlenia** – [UZUPEŁNIJ: cena od–do, np. za literę o określonej wysokości]
- **Litery przestrzenne podświetlane LED** – [UZUPEŁNIJ: cena od–do]
- **Montaż** – [UZUPEŁNIJ: czy wliczony w cenę, czy liczony osobno i od czego zależy]

Ceny podane są [UZUPEŁNIJ: netto/brutto]. Jeśli masz określony budżet, napisz o nim w zapytaniu – zaproponujemy rozwiązanie, które się w nim zmieści, np. tańszy materiał lub podświetlenie tylko części napisu.
"""),
        ("Co jest wliczone w cenę szyldu", """
Żeby uniknąć niespodzianek, w wycenie zawsze wypisujemy, co obejmuje oferta. Standardowo może to być:

- Projekt lub dopasowanie logo do formatu szyldu – [UZUPEŁNIJ: czy wliczone, czy płatne osobno]
- Wizualizacja na zdjęciu Twojej elewacji – [UZUPEŁNIJ: czy firma ją przygotowuje]
- Produkcja szyldu z wybranych materiałów
- Elementy montażowe – konstrukcja, dystanse, kotwy
- Transport i montaż na terenie Gdyni [UZUPEŁNIJ: oraz Sopotu, Gdańska, Rumi, Redy – jeśli dotyczy]
- Podłączenie podświetlenia – [UZUPEŁNIJ: zakres prac elektrycznych, np. do istniejącego punktu zasilania]
- Gwarancja – [UZUPEŁNIJ: okres gwarancji na szyld i podświetlenie LED]

Jeśli coś jest płatne dodatkowo (np. demontaż starej reklamy, wynajem podnośnika), wpisujemy to w wycenie osobną pozycją.
"""),
        ("Jak przygotować zapytanie o cenę szyldu reklamowego", """
Im więcej informacji dostaniemy na początku, tym szybciej i dokładniej wycenimy szyld. Najlepiej przygotuj:

- **Zdjęcie elewacji** – zrobione na wprost, z widocznym miejscem, gdzie ma zawisnąć szyld. Dobrze, jeśli na zdjęciu widać też okna lub drzwi dla skali.
- **Wymiary** – szerokość i wysokość dostępnego miejsca oraz wysokość montażu nad ziemią. Wystarczą przybliżone pomiary, dokładne zrobimy przed produkcją.
- **Logo** – najlepiej w formie wektorowej (PDF, AI, EPS, SVG). Jeśli masz tylko plik graficzny lub zdjęcie, też go prześlij – ocenimy, czy trzeba go odtworzyć.
- **Twoje preferencje** – czy szyld ma być podświetlany, czy interesują Cię litery przestrzenne, jaki masz budżet i termin.
- **Adres lokalu** – żeby ocenić warunki montażu i dojazd.

Nie wiesz, co wybrać? Opisz, jaki efekt chcesz osiągnąć, a my zaproponujemy 1–2 warianty w różnych cenach.
"""),
        ("Formularz wyceny – szyldy reklamowe w Gdyni", """
Wypełnij formularz i dołącz zdjęcie elewacji, wymiary oraz logo. Odpowiadamy z wyceną [UZUPEŁNIJ: w jakim czasie, np. w ciągu 1–2 dni roboczych].

{{form:cennik}}

Wolisz porozmawiać? Zadzwoń pod numer {TEL_LINK} lub napisz na {EMAIL_LINK}. Działamy w Gdyni [UZUPEŁNIJ: i okolicach – wymień miejscowości].
"""),
    ],
    faq=[
        ("Ile kosztuje szyld reklamowy w Gdyni?", "Cena zależy od wymiarów, materiału, podświetlenia i warunków montażu. Prosty szyld płaski to orientacyjnie [UZUPEŁNIJ: widełki], a szyld z literami przestrzennymi lub LED [UZUPEŁNIJ: widełki]. Dokładną kwotę podajemy po otrzymaniu zdjęcia elewacji i wymiarów."),
        ("Czy montaż szyldu jest wliczony w cenę?", "[UZUPEŁNIJ: czy montaż jest wliczony, czy liczony osobno]. Koszt montażu zależy od wysokości, rodzaju elewacji i tego, czy potrzebny jest podnośnik. Wszystkie dodatkowe pozycje zaznaczamy w wycenie."),
        ("Ile trwa wykonanie szyldu reklamowego?", "Standardowo od akceptacji projektu do montażu mija [UZUPEŁNIJ: czas realizacji]. Szyldy z podświetleniem LED i litery przestrzenne wymagają zwykle więcej czasu niż proste szyldy płaskie. Jeśli zależy Ci na terminie, np. otwarciu lokalu, napisz o tym w zapytaniu."),
        ("Czy szyld podświetlany LED dużo zużywa prądu?", "Podświetlenie LED jest energooszczędne w porównaniu ze starszymi źródłami światła, a zużycie zależy od wielkości szyldu i liczby modułów. Można też zastosować zegar lub czujnik zmierzchu, żeby szyld świecił tylko wtedy, gdy jest potrzebny. [UZUPEŁNIJ: czy firma montuje sterowniki czasowe]"),
        ("Jaką gwarancję dajecie na szyldy?", "Na szyldy udzielamy [UZUPEŁNIJ: okres gwarancji], a na podświetlenie LED [UZUPEŁNIJ: okres gwarancji]. Szczegóły gwarancji wpisujemy w ofercie."),
        ("Czy potrzebuję zgody na powieszenie szyldu?", "Zwykle potrzebna jest zgoda właściciela lub zarządcy budynku, a w niektórych miejscach trzeba też spełnić lokalne przepisy dotyczące reklam. Warto to sprawdzić przed zamówieniem. [UZUPEŁNIJ: czy firma pomaga w przygotowaniu dokumentów]"),
    ],
    cta="Wyślij nam zdjęcie elewacji, wymiary i logo – przygotujemy wycenę szyldu reklamowego dla Twojej firmy w Gdyni.",
    links=["szyldy-reklamowe-gdynia", "litery-przestrzenne-gdynia", "reklama-led-gdynia", "kontakt"],
))

# ============================ KONTAKT ========================================
PAGES.append(dict(
    slug="kontakt",
    title="Szyldy reklamowe Gdynia kontakt – wycena szyldu i liter",
    desc="Szyldy reklamowe Gdynia – kontakt. Wyślij zdjęcie elewacji i wymiary, a przygotujemy wycenę szyldu, liter przestrzennych lub reklamy LED.",
    h1="Szyldy reklamowe Gdynia – kontakt i wycena",
    lead="Szyldy reklamowe Gdynia – kontakt z nami jest najprostszy przez telefon, e-mail lub formularz, do którego możesz dołączyć zdjęcie elewacji i wymiary miejsca na reklamę. Wykonujemy szyldy, litery przestrzenne i reklamy LED dla sklepów, lokali usługowych, biur i firm z Gdyni i okolic. Na podstawie przesłanych informacji przygotujemy wycenę i zaproponujemy rozwiązanie dopasowane do budynku.",
    crumb="Kontakt",
    schema="contact",
    cta_href="#formularz",
    sections=[
        ("Dane kontaktowe – szyldy reklamowe Gdynia", """
Poniżej znajdziesz wszystkie dane potrzebne do kontaktu. Są takie same jak w naszym Profilu Firmy w Google, więc łatwo nas znaleźć także w Mapach Google.

::info
- **Nazwa firmy:** {NAZWA}
- **Adres:** {ADRES}
- **Telefon:** {TEL_LINK}
- **E-mail:** {EMAIL_LINK}
- **NIP:** {NIP}

Jeśli wolisz rozmowę na miejscu, zadzwoń wcześniej i umów się – wtedy będziemy mieć czas, żeby pokazać próbki materiałów i omówić projekt.
"""),
        ("Godziny pracy i dojazd", """
Odbieramy telefony i odpowiadamy na wiadomości w godzinach pracy. Zapytania wysłane przez formularz poza tymi godzinami obsługujemy w kolejnym dniu roboczym.

::info
- **Poniedziałek – piątek:** {GODZ_PNPT}
- **Sobota:** {GODZ_SOB}
- **Niedziela:** nieczynne

Na mapie poniżej zaznaczyliśmy naszą siedzibę w Gdyni. [UZUPEŁNIJ: informacja o parkingu lub dojeździe, np. najbliższy przystanek]

{{map}}
"""),
        ("Jak wysłać zapytanie o szyld reklamowy – formularz ze zdjęciem", """
Najszybciej przygotujemy wycenę, gdy od razu dostaniemy kilka podstawowych informacji. W formularzu możesz dodać zdjęcia, dlatego warto je zrobić przed wysłaniem wiadomości.

- **Zdjęcie elewacji** – najlepiej z daleka, tak żeby było widać cały front budynku i miejsce na reklamę.
- **Wymiary** – szerokość i wysokość miejsca, w którym ma się pojawić szyld lub litery (wystarczą orientacyjne).
- **Logo lub nazwa firmy** – plik graficzny, a jeśli go nie masz, sam napis.
- **Rodzaj reklamy** – szyld płaski, kaseton, litery przestrzenne, reklama LED, a jeśli nie wiesz, co wybrać, napisz po prostu, czego oczekujesz.
- **Podświetlenie** – czy reklama ma być widoczna po zmroku.
- **Adres montażu** – miejscowość i ulica.

Jeśli nie masz wszystkich danych, wyślij to, co masz. Resztę ustalimy telefonicznie lub podczas pomiaru.

{{form:kontakt}}
"""),
        ("Od czego zależy cena szyldu", f"""
Każdy szyld wyceniamy indywidualnie, bo dwa podobne napisy mogą mieć zupełnie inny koszt. Na cenę wpływają przede wszystkim:

- **Rozmiar** – wielkość szyldu lub wysokość liter.
- **Rodzaj i materiał** – np. dibond, plexi, PCV, stal, aluminium.
- **Podświetlenie** – reklama z LED-ami kosztuje więcej niż wersja bez światła.
- **Projekt** – czy dostarczasz gotowy plik, czy potrzebujesz projektu od podstaw.
- **Montaż** – wysokość, rodzaj ściany, potrzeba podnośnika lub rusztowania, doprowadzenie prądu.

Orientacyjne ceny znajdziesz w zakładce [cennik]({L_CE}), a dokładną kwotę podamy po otrzymaniu zdjęć i wymiarów.
"""),
        ("Co dzieje się po kontakcie", f"""
Współpraca przebiega w kilku prostych krokach:

1. **Zapytanie** – wysyłasz formularz, e-mail lub dzwonisz.
2. **Wstępna wycena** – na podstawie zdjęć i wymiarów podajemy orientacyjny koszt w ciągu [UZUPEŁNIJ: czas odpowiedzi].
3. **Pomiar i projekt** – w razie potrzeby przyjeżdżamy na miejsce i przygotowujemy wizualizację szyldu na Twojej elewacji.
4. **Akceptacja i produkcja** – po zatwierdzeniu projektu wykonujemy reklamę.
5. **Montaż** – montujemy szyld lub litery w umówionym terminie.

Przykłady wykonanych szyldów i liter przestrzennych możesz zobaczyć w zakładce [realizacje]({L_RE}).
"""),
        ("Obszar działania", """
Szyldy reklamowe wykonujemy i montujemy przede wszystkim w Gdyni. [UZUPEŁNIJ: inne miejscowości, w których faktycznie realizujecie montaż, np. Sopot, Gdańsk, Rumia, Reda – wpisz tylko te prawdziwe]

Jeśli Twoja firma znajduje się poza tym obszarem, napisz do nas – sprawdzimy, czy możemy zrealizować zlecenie, i podamy koszt dojazdu.
"""),
    ],
    faq=[
        ("Ile kosztuje szyld reklamowy w Gdyni?", "Cena zależy od rozmiaru, materiału, podświetlenia i warunków montażu. Orientacyjne kwoty znajdziesz w cenniku, a dokładną wycenę przygotujemy po otrzymaniu zdjęcia elewacji i wymiarów. [UZUPEŁNIJ: ewentualna cena „od”]"),
        ("Jak długo czeka się na wykonanie szyldu?", "Czas realizacji zależy od rodzaju reklamy i obłożenia produkcji. Prosty szyld zwykle powstaje szybciej niż podświetlane litery przestrzenne. [UZUPEŁNIJ: typowy czas realizacji]"),
        ("Czy zajmujecie się też montażem?", "Tak, montujemy wykonane przez nas szyldy, kasetony i litery przestrzenne. Przed montażem oceniamy rodzaj ściany i sposób mocowania, a przy reklamach podświetlanych ustalamy podłączenie zasilania. [UZUPEŁNIJ: czy podłączenie elektryczne wykonujecie sami]"),
        ("Czy mogę wysłać zdjęcie zamiast umawiać pomiar?", "Tak, do wstępnej wyceny w zupełności wystarczy zdjęcie elewacji i orientacyjne wymiary. Pomiar na miejscu robimy zwykle przed produkcją, żeby szyld idealnie pasował do budynku."),
        ("Z jakich materiałów wykonujecie szyldy?", "Dobieramy materiał do miejsca i budżetu, najczęściej są to dibond, plexi, PCV oraz elementy ze stali lub aluminium. Podczas rozmowy podpowiemy, co sprawdzi się na Twojej elewacji. [UZUPEŁNIJ: potwierdź listę materiałów]"),
        ("Czy dajecie gwarancję na szyldy i podświetlenie?", "[UZUPEŁNIJ: okres i zakres gwarancji na szyld, podświetlenie LED i montaż]. Szczegóły gwarancji podajemy w ofercie przed rozpoczęciem realizacji."),
    ],
    cta="Wyślij nam zdjęcie elewacji i wymiary przez formularz lub zadzwoń – przygotujemy wycenę Twojego szyldu reklamowego w Gdyni.",
    links=["szyldy-reklamowe-gdynia", "litery-przestrzenne-gdynia", "cennik", "realizacje"],
))

# ---------------------------------------------------------------------------
# 5. JSON-LD
# ---------------------------------------------------------------------------
BIZ_ID = f"{DOMENA}/#firma"


def local_business():
    return {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "@id": BIZ_ID,
        "name": FIRMA["nazwa"],
        "description": "Agencja reklamy zewnętrznej z Gdyni: szyldy reklamowe, litery przestrzenne i reklama LED – projekt, wykonanie i montaż.",
        "url": f"{DOMENA}/",
        "image": f"{DOMENA}/img/og-image.jpg",
        "logo": f"{DOMENA}/favicon.svg",
        "telephone": FIRMA["telefon_href"],
        "email": FIRMA["email"],
        "address": {
            "@type": "PostalAddress",
            "streetAddress": FIRMA["ulica"],
            "postalCode": FIRMA["kod"],
            "addressLocality": FIRMA["miasto"],
            "addressRegion": "pomorskie",
            "addressCountry": "PL",
        },
        "areaServed": [{"@type": "City", "name": n} for n in FIRMA["obszar"]],
        "openingHoursSpecification": [{
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
            "opens": FIRMA["otwarcie_pn_pt"],
            "closes": FIRMA["zamkniecie_pn_pt"],
        }],
        "hasMap": FIRMA["mapa_link"],
        "knowsAbout": ["szyldy reklamowe", "kasetony reklamowe", "litery przestrzenne", "litery do wnętrz", "litery metalowe", "neony LED"],
        "makesOffer": [
            {"@type": "Offer", "itemOffered": {"@type": "Service", "name": n, "url": f"{DOMENA}/#oferta"}}
            for n in ["Kasetony reklamowe podświetlane LED (frezowanie wklęsłe lub wypukłe)", "Litery przestrzenne 3D",
                      "Logo do środka", "Litery metalowe", "Neony LED"]
        ],
    }


def service(p, name, desc):
    return {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": f"{name} Gdynia",
        "serviceType": name,
        "description": desc,
        "url": url(p["slug"]),
        "areaServed": [{"@type": "City", "name": n} for n in FIRMA["obszar"]],
        "provider": {
            "@type": "LocalBusiness",
            "@id": BIZ_ID,
            "name": FIRMA["nazwa"],
            "telephone": FIRMA["telefon_href"],
            "address": {"@type": "PostalAddress", "streetAddress": FIRMA["ulica"], "postalCode": FIRMA["kod"],
                        "addressLocality": FIRMA["miasto"], "addressCountry": "PL"},
        },
    }


def faq_ld(p):
    return {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {"@type": "Question", "name": q, "acceptedAnswer": {"@type": "Answer", "text": plain(a)}}
            for q, a in p["faq"]
        ],
    }


def crumbs_ld(p):
    return {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Strona główna", "item": url("index")},
            {"@type": "ListItem", "position": 2, "name": p["crumb"], "item": url(p["slug"])},
        ],
    }


def ld_blocks(p):
    blocks = []
    s = p["schema"]
    if s in ("home", "contact"):
        blocks.append(local_business())
    elif isinstance(s, tuple):
        blocks.append(service(p, s[1], s[2]))
    if p["faq"]:
        blocks.append(faq_ld(p))
    if p["crumb"]:
        blocks.append(crumbs_ld(p))
    return "\n".join(
        '<script type="application/ld+json">\n' + json.dumps(b, ensure_ascii=False, indent=1) + "\n</script>"
        for b in blocks)


# ---------------------------------------------------------------------------
# 6. Szablon strony
# ---------------------------------------------------------------------------
def header(active):
    if LANDING_ONLY:
        items = "".join(f'<li><a href="{h}">{n}</a></li>' for h, n in LANDING_NAV)
    else:
        items = "".join(
            f'<li><a href="{href(s)}"{" aria-current=\"page\"" if s == active else ""}>{n}</a></li>' for s, n in NAV)
    return f'''<a class="skip" href="#tresc">Przejdź do treści</a>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="logo" href="./" aria-label="{html.escape(FIRMA["logo"])} – strona główna"><span class="logo-mark" aria-hidden="true"></span><span class="logo-txt">{html.escape(FIRMA["logo"])}</span></a>
    <nav class="main-nav" aria-label="Menu główne"><ul>{items}</ul></nav>
    <div class="header-actions">
      <a class="btn btn-outline btn-sm header-call" href="tel:{FIRMA["telefon_href"]}">Zadzwoń</a>
      <a class="btn btn-primary btn-sm" href="{"#wycena" if LANDING_ONLY else "kontakt.html#formularz"}" data-quote-open>Wyślij zdjęcie</a>
    </div>
  </div>
</header>'''


def footer():
    if LANDING_ONLY:
        oferta = "".join(f'<li><a href="#oferta">{n}</a></li>' for n in ["Kaseton reklamowy", "Litery przestrzenne 3D", "Logo do środka"])
        firma = "".join(f'<li><a href="{h}">{n}</a></li>' for h, n in LANDING_NAV[1:])
    else:
        oferta = "".join(f'<li><a href="{href(s)}">{n}</a></li>' for s, n in NAV[:3])
        firma = "".join(f'<li><a href="{href(s)}">{n}</a></li>' for s, n in NAV[3:])
    return f'''<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand" itemscope itemtype="https://schema.org/LocalBusiness">
      <a class="logo" href="./"><span class="logo-mark" aria-hidden="true"></span><span class="logo-txt">{html.escape(FIRMA["logo"])}</span></a>
      <p class="footer-tag">Szyldy reklamowe, litery przestrzenne i reklama LED w Gdyni.</p>
      <p><span itemprop="name">{fill(FIRMA["nazwa"])}</span><br><span itemprop="address">{fill(html.escape(ADRES, quote=False))}</span></p>
      <p>Tel.: <a itemprop="telephone" href="tel:{FIRMA["telefon_href"]}">{fill(FIRMA["telefon"])}</a><br>E-mail: <a itemprop="email" href="mailto:{html.escape(FIRMA["email"])}">{fill(FIRMA["email"])}</a></p>
    </div>
    <div><p class="footer-h">Oferta</p><ul class="footer-nav">{oferta}</ul></div>
    <div><p class="footer-h">{"Na stronie" if LANDING_ONLY else "Firma"}</p><ul class="footer-nav">{"" if LANDING_ONLY else '<li><a href="./">Strona główna</a></li>'}{firma}</ul></div>
    <div><p class="footer-h">Godziny pracy</p><p>Pn–pt: {fill(FIRMA["godziny_pn_pt"])}<br>Sobota: {fill(html.escape(FIRMA["godziny_sob"], quote=False))}<br>Niedziela: nieczynne</p>
      <p class="footer-h">Obszar działania</p><p>{fill(", ".join(FIRMA["obszar"]))}</p></div>
  </div>
  <div class="wrap copy"><span>© 2026 {fill(FIRMA["nazwa"])} · Gdynia</span><a href="{html.escape(FIRMA["polityka_prywatnosci"])}">Polityka prywatności</a></div>
</footer>
<nav class="mobile-bar" aria-label="Szybki kontakt">
  <a class="btn btn-dark" href="tel:{FIRMA["telefon_href"]}">Zadzwoń</a>
  <a class="btn btn-dark" href="https://wa.me/{FIRMA["whatsapp"]}" rel="noopener">WhatsApp</a>
  <a class="btn btn-primary" href="{"#wycena" if LANDING_ONLY else "kontakt.html#formularz"}" data-quote-open>Wyślij zdjęcie</a>
</nav>
<dialog class="sheet" id="okno-wyceny" aria-labelledby="wycena-tytul">
  <div class="sheet-head"><p class="sheet-title" id="wycena-tytul">Wyślij zdjęcie do wyceny</p><button type="button" class="sheet-close" aria-label="Zamknij">×</button></div>
  {form("okno")}
</dialog>
<script src="wycena.js" defer></script>'''


MOCK_TXT = {"index": "TWOJA FIRMA", "szyldy-reklamowe-gdynia": "TWOJA FIRMA",
            "litery-przestrzenne-gdynia": "LOGO 3D", "reklama-led-gdynia": "OTWARTE"}
TRUST = ["Wycena na podstawie zdjęcia", "Wizualizacja przed produkcją", "Szyldy, litery 3D i LED", "Gdynia i okolice"]


def mock(p):
    v = p.get("mock")
    if not v:
        return ""
    t = MOCK_TXT.get(p["slug"], "TWOJA FIRMA")
    return f'''<div class="mock mock--{v}" aria-hidden="true">
  <div class="mock-sign"><span>{t}</span></div>
  <div class="mock-shop"><i></i><i class="door"></i><i></i></div>
</div>'''


FONTS = """<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@125,500;125,600;125,700&amp;family=Pacifico&amp;display=swap">
"""


HERO_IMG = {"index": "hero-index", "szyldy-reklamowe-gdynia": "hero-szyldy",
            "litery-przestrzenne-gdynia": "hero-litery", "reklama-led-gdynia": "hero-led"}
HERO_PHOTO = {"index": "Litery przestrzenne 3D na białej elewacji budynku"}  # alt zdjęcia w górnej części strony
HERO_EYEBROW = {"index": "Reklama zewnętrzna · Gdynia", "szyldy-reklamowe-gdynia": "Szyldy · kasetony · semafory",
                "litery-przestrzenne-gdynia": "Litery 3D · fasada · recepcja", "reklama-led-gdynia": "Kasetony i litery LED",
                "realizacje": "Realizacje", "cennik": "Cennik i wycena", "kontakt": "Kontakt i wycena"}
EYEBROWS = {
    "Szyldy reklamowe Gdynia – co robimy i dla kogo": "Dla kogo",
    "Kasetony, litery przestrzenne i litery do wnętrz": "Oferta",
    "Katalog: litery metalowe, litery do środka i neony LED": "Katalog",
    "Jak wygląda współpraca krok po kroku": "Wycena ze zdjęcia",
    "Od czego zależy cena szyldu reklamowego": "Cena",
    "Realizacje i opinie klientów": "Realizacje",
    "Szyldy reklamowe w Gdyni i okolicy – obszar działania i kontakt": "Gdzie działamy",
    "Uchwała krajobrazowa Gdyni – co sprawdzić przed montażem szyldu": "Przepisy",
}
STATS = [("[UZUPEŁNIJ]", "lat", "doświadczenia"), ("[UZUPEŁNIJ]", "+", "wykonanych szyldów"),
         ("3", "", "kasetony · litery 3D · do wnętrz"), ("1", "zdjęcie", "wystarczy do wyceny")]


def h1_html(t):
    m = re.search(r"(w Gdyni|Gdynia)(.*)$", t)
    if m and m.start() > 0:
        return f'{t[:m.start()]}<span class="h1-accent">{t[m.start():]}</span>'
    return t


def split_intro(body_html):
    if body_html.startswith("<p>"):
        i = body_html.index("</p>") + 4
        return body_html[:i].replace("<p>", '<p class="sec-intro">', 1), body_html[i:]
    return "", body_html


def page_html(p):
    slug = p["slug"]
    title, desc = p["title"], p["desc"]
    canon = url(slug)
    cta_href = p.get("cta_href", "#wycena" if slug == "index" else "kontakt.html#formularz")
    crumb = ""
    if p["crumb"]:
        crumb = f'<nav class="crumbs" aria-label="Okruszki"><a href="./">Strona główna</a> <span aria-hidden="true">/</span> <span>{p["crumb"]}</span></nav>'
    secs = []
    for i, (h2, body) in enumerate(p["sections"]):
        cls = "sec alt" if i % 2 else "sec"
        intro, rest = split_intro(md(body))
        eb = EYEBROWS.get(h2)
        eb = f'<p class="eyebrow">{eb}</p>' if eb else ""
        if slug == "index" and h2 == "Jak wygląda współpraca krok po kroku":
            rest = (f'<div class="quote-grid"><div class="quote-info">{rest}</div>'
                    f'<div class="quote-form"><p class="quote-form-title">Wyślij zdjęcie do wyceny</p>{form("index")}</div></div>')
            cls += " sec-quote"
        sid = SECTION_IDS.get(h2) if slug == "index" else None
        sid = f' id="{sid}"' if sid else ""
        secs.append(f'<section class="{cls}"{sid}><div class="wrap">\n<div class="sec-head"><div>{eb}<h2>{h2}</h2></div>{intro}</div>\n<div class="sec-body">{rest}</div>\n</div></section>')
    faq = "".join(
        f'<details class="qa"{" open" if n == 0 else ""}><summary><h3>{inline(q)}</h3></summary><p>{inline(a)}</p></details>'
        for n, (q, a) in enumerate(p["faq"]))
    cards = "".join(
        f'<a class="card" href="{href(s)}"><strong>{CARDS[s][0]}</strong><span>{CARDS[s][1]}</span><b aria-hidden="true">→</b></a>'
        for s in p["links"])
    stats = ""
    if slug == "index":
        stats = '<ul class="stats">' + "".join(
            f'<li><span class="stat-n">{fill(n)}<small>{u}</small></span><span class="stat-l">{l}</span></li>' for n, u, l in STATS) + "</ul>"
    img = HERO_IMG.get(slug)
    hero_cls = "hero has-img" if img else "hero"
    hero_style = f' style="--hero-img: url(img/{img}.webp)"' if img else ""
    hero_photo = ""
    faq_media = ""
    if (OUT / "img" / "faq.mp4").exists():
        faq_media = ('<figure class="faq-video"><video autoplay muted loop playsinline preload="metadata" poster="img/faq-poster.webp" '
                     'aria-label="Litery przestrzenne na elewacji zapalają się po zmroku"><source src="img/faq.mp4" type="video/mp4"></video>'
                     f'<figcaption>{fill("[UZUPEŁNIJ: film z Waszej realizacji – np. montaż liter albo zapalanie podświetlenia]")}</figcaption></figure>')
    if slug in HERO_PHOTO:  # zwykłe zdjęcie obok tekstu zamiast tła
        hero_cls, hero_style = "hero hero-split", ""
        vids = [(ext, typ) for ext, typ in (("webm", "video/webm"), ("mp4", "video/mp4")) if (OUT / "img" / f"{img}.{ext}").exists()]
        if vids:  # animacja (np. świecące litery) – zdjęcie zostaje jako klatka zastępcza
            sources = "".join(f'<source src="img/{img}.{ext}" type="{typ}">' for ext, typ in vids)
            hero_photo = (f'<figure class="hero-photo hero-video"><video autoplay muted loop playsinline preload="metadata" '
                          f'poster="img/{img}.webp" aria-label="{html.escape(HERO_PHOTO[slug])}">{sources}</video></figure>')
        elif (OUT / "img" / f"{img}-noc.webp").exists() and (OUT / "img" / f"{img}-zmierzch.webp").exists():
            # animacja dzień → zmierzch → litery zapalają się (halo) – same zdjęcia + CSS
            hero_photo = (f'<figure class="hero-photo hero-switch"><img src="img/{img}.webp" width="1400" height="1050" '
                          f'fetchpriority="high" decoding="async" alt="{html.escape(HERO_PHOTO[slug])} – w dzień i nocą z podświetleniem LED">'
                          f'<img class="hs-dusk" src="img/{img}-zmierzch.webp" width="1400" height="1050" decoding="async" alt="" aria-hidden="true">'
                          f'<img class="hs-night" src="img/{img}-noc.webp" width="1400" height="1050" decoding="async" alt="" aria-hidden="true">'
                          f'<figcaption class="hs-chip"><span class="hs-day-txt">W dzień: litery 3D</span><span class="hs-night-txt">Po zmroku: podświetlenie LED na elewację</span></figcaption></figure>')
        else:
            hero_photo = (f'<figure class="hero-photo"><img src="img/{img}.webp" width="1400" height="1050" '
                          f'fetchpriority="high" decoding="async" alt="{html.escape(HERO_PHOTO[slug])}"></figure>')
    preload = f'<link rel="preload" as="image" href="img/{img}.webp">\n' if img and not MAKIETA else ""
    return f'''<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{html.escape(title)}</title>
<meta name="description" content="{html.escape(desc)}">
<link rel="canonical" href="{canon}">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="theme-color" content="#0f1d27">
<meta property="og:type" content="website">
<meta property="og:locale" content="pl_PL">
<meta property="og:site_name" content="{html.escape(plain(FIRMA['nazwa']))}">
<meta property="og:title" content="{html.escape(title)}">
<meta property="og:description" content="{html.escape(desc)}">
<meta property="og:url" content="{canon}">
<meta property="og:image" content="{DOMENA}/img/og-image.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Szyldy reklamowe, litery przestrzenne i reklama LED – Gdynia">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="favicon.svg" type="image/svg+xml">
{"" if MAKIETA else FONTS}{preload}<link rel="stylesheet" href="style.css">
{ld_blocks(p)}
</head>
<body>
{header(slug)}
<main id="tresc">
<section class="{hero_cls}"{hero_style}><div class="wrap hero-inner">
<div class="hero-copy">
{crumb}
<p class="hero-eyebrow">{HERO_EYEBROW.get(slug, "")}</p>
<h1>{h1_html(p["h1"])}</h1>
<p class="lead">{inline(p["lead"])}</p>
<div class="actions"><a class="btn btn-light btn-lg" href="{cta_href}" data-quote-open>Wyślij zdjęcie do wyceny</a><a class="btn btn-outline btn-lg" href="tel:{FIRMA["telefon_href"]}">Zadzwoń</a></div>
</div>
{hero_photo}
{stats}
</div></section>
{chr(10).join(secs)}
<section class="sec faq" id="faq"><div class="wrap">
<div class="sec-head"><div><p class="eyebrow">FAQ</p><h2>Pytania i odpowiedzi</h2></div></div>
<div class="sec-body faq-grid"><div class="faq-list">{faq}</div>{faq_media}</div>
</div></section>
<section class="cta"><div class="wrap cta-inner">
<div><p class="eyebrow">Wyślij zdjęcie – albo zadzwoń</p><h2>Zapytaj o wycenę</h2>
<p>{inline(p["cta"])}</p></div>
<div class="actions"><a class="btn btn-light btn-lg" href="{cta_href}" data-quote-open>Wyślij zdjęcie elewacji</a><a class="btn btn-outline btn-lg" href="tel:{FIRMA["telefon_href"]}">Zadzwoń</a></div>
</div></section>
{f'<section class="more"><div class="wrap"><h2>Zobacz też</h2><div class="cards">{cards}</div></div></section>' if cards else ""}
</main>
{footer()}
</body>
</html>
'''

# ---------------------------------------------------------------------------
# 7. Pliki dodatkowe
# ---------------------------------------------------------------------------
def sitemap():
    prio = {"index": "1.0", "kontakt": "0.8", "cennik": "0.8", "realizacje": "0.7"}
    rows = "".join(
        f"  <url><loc>{url(p['slug'])}</loc><lastmod>{DATA_AKTUALIZACJI}</lastmod><priority>{prio.get(p['slug'], '0.9')}</priority></url>\n"
        for p in PAGES if not LANDING_ONLY or p["slug"] == "index")
    return f'<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n{rows}</urlset>\n'


def robots():
    return f"""User-agent: *
Allow: /

# Boty wyszukiwarek i czatów AI – mają pełny dostęp
User-agent: Googlebot
User-agent: Bingbot
User-agent: GPTBot
User-agent: OAI-SearchBot
User-agent: ChatGPT-User
User-agent: Google-Extended
User-agent: ClaudeBot
User-agent: PerplexityBot
Allow: /

Sitemap: {DOMENA}/sitemap.xml
"""


def llms():
    if LANDING_ONLY:
        lines = "\n".join(f"- [{n}]({DOMENA}/{h})" for h, n in LANDING_NAV)
    else:
        lines = "\n".join(f"- [{p['h1']}]({url(p['slug'])}): {p['desc']}" for p in PAGES)
    return f"""# {plain(FIRMA['nazwa'])} – szyldy reklamowe Gdynia

> Agencja reklamy zewnętrznej z Gdyni. Wykonujemy i montujemy szyldy z kasetonów, litery przestrzenne na elewację i litery do wnętrz – podświetlane LED lub bez podświetlenia. W katalogu: litery metalowe, litery do środka i neony LED. Dla sklepów, lokali usługowych, gabinetów, restauracji i biur. Wycena na podstawie zdjęcia elewacji, wymiarów i logo.

## Dane firmy
- Nazwa: {plain(FIRMA['nazwa'])}
- Adres: {plain(ADRES)}
- Telefon: {FIRMA['telefon']}
- E-mail: {FIRMA['email']}
- Godziny pracy: {plain(GODZINY)}
- Obszar działania: {", ".join(FIRMA['obszar'])}
- Mapy Google: {FIRMA['mapa_link']}

## Na stronie
{lines}

## Jak zamówić
Wyślij przez formularz wyceny ({DOMENA}/#wycena) zdjęcie elewacji (miejsca montażu), przybliżone wymiary i logo. Przygotujemy wizualizację i wycenę.
"""


FAVICON = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="#16191f"/><rect x="12" y="20" width="40" height="24" rx="3" fill="#f5b400"/><rect x="18" y="29" width="28" height="6" rx="1" fill="#16191f"/></svg>
'''


def images():
    """Rysuje przykładowe grafiki (do podmiany na prawdziwe zdjęcia realizacji)."""
    from PIL import Image, ImageDraw, ImageFilter, ImageFont
    img_dir = OUT / "img"
    img_dir.mkdir(parents=True, exist_ok=True)
    W, H = 800, 600

    def font(size, bold=True):
        try:
            return ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf" if bold
                                      else "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", size)
        except OSError:
            return ImageFont.load_default()

    def grad(top, bottom):
        im = Image.new("RGB", (W, H), top)
        d = ImageDraw.Draw(im)
        for y in range(H):
            t = y / H
            d.line([(0, y), (W, y)], fill=tuple(int(top[i] + (bottom[i] - top[i]) * t) for i in range(3)))
        return im

    def facade(im, wall, night):
        d = ImageDraw.Draw(im)
        d.rectangle([0, 0, W, 470], fill=wall)
        for x in range(0, W, 200):  # podziały elewacji
            d.line([(x, 0), (x, 470)], fill=tuple(max(0, c - 12) for c in wall), width=2)
        d.rectangle([0, 470, W, H], fill=(40, 42, 48) if night else (150, 150, 146))
        glass = (30, 44, 60) if night else (120, 150, 170)
        for x0 in (60, 470):
            d.rectangle([x0, 290, x0 + 270, 470], fill=glass, outline=(25, 25, 28), width=8)
        d.rectangle([350, 300, 450, 470], fill=(25, 27, 32), outline=(25, 25, 28), width=8)
        if night:  # ciepłe światło z witryn
            glow = Image.new("RGB", (W, H), (0, 0, 0))
            gd = ImageDraw.Draw(glow)
            for x0 in (60, 470):
                gd.rectangle([x0 + 10, 300, x0 + 260, 460], fill=(120, 90, 40))
            im.paste(Image.blend(im, glow.filter(ImageFilter.GaussianBlur(4)), 0.35))

    def text_c(d, xy, txt, f, fill):
        d.text(xy, txt, font=f, fill=fill, anchor="mm")

    def neon(im, xy, txt, size, color):
        from PIL import ImageChops
        layer = Image.new("RGB", (W, H))
        ImageDraw.Draw(layer).text(xy, txt, font=font(size), fill=color, anchor="mm")
        for r in (34, 14, 5):
            im.paste(ImageChops.screen(im, layer.filter(ImageFilter.GaussianBlur(r))))
        ImageDraw.Draw(im).text(xy, txt, font=font(size), fill=(255, 248, 225), anchor="mm")

    def badge(im):
        d = ImageDraw.Draw(im)
        d.rounded_rectangle([16, 16, 300, 50], radius=6, fill=(0, 0, 0))
        d.text((28, 33), "PRZYKŁAD – podmień na zdjęcie", font=font(15, False), fill=(255, 255, 255), anchor="lm")

    scenes = {}

    im = grad((215, 220, 226), (200, 205, 210)); facade(im, (232, 228, 220), False)
    d = ImageDraw.Draw(im); d.rectangle([150, 150, 650, 250], fill=(22, 26, 32)); d.rectangle([150, 244, 650, 250], fill=(245, 180, 0))
    text_c(d, (400, 197), "KAWIARNIA", font(56), (255, 255, 255)); scenes["szyld-1"] = im

    im = grad((40, 50, 80), (90, 80, 100)); facade(im, (70, 72, 80), True)
    d = ImageDraw.Draw(im); d.rectangle([140, 140, 660, 250], fill=(255, 246, 220))
    glow = Image.new("RGB", (W, H)); ImageDraw.Draw(glow).rectangle([140, 140, 660, 250], fill=(255, 220, 140))
    im.paste(Image.blend(im, glow.filter(ImageFilter.GaussianBlur(30)), .35)); d = ImageDraw.Draw(im)
    d.rectangle([140, 140, 660, 250], fill=(255, 248, 228), outline=(30, 30, 34), width=6)
    text_c(d, (400, 195), "PIEKARNIA", font(58), (180, 40, 30)); scenes["szyld-2"] = im

    im = grad((200, 215, 230), (225, 228, 232)); facade(im, (205, 203, 198), False)
    d = ImageDraw.Draw(im)
    for off in range(9, 0, -1):
        text_c(d, (400 + off, 200 + off), "STUDIO", font(110), (60, 62, 68))
    text_c(d, (400, 200), "STUDIO", font(110), (20, 22, 26)); scenes["litery-1"] = im

    im = Image.new("RGB", (W, H), (236, 233, 228)); d = ImageDraw.Draw(im)
    d.rectangle([0, 0, W, 430], fill=(70, 58, 48))
    for x in range(0, W, 40): d.line([(x, 0), (x, 430)], fill=(62, 51, 42), width=2)
    d.rectangle([140, 430, 660, 560], fill=(245, 243, 238)); d.rectangle([140, 430, 660, 446], fill=(210, 206, 198))
    halo = Image.new("RGB", (W, H)); ImageDraw.Draw(halo).text((400, 215), "BIURO", font=font(120), fill=(255, 210, 120), anchor="mm")
    im.paste(Image.blend(im, halo.filter(ImageFilter.GaussianBlur(18)), .55)); d = ImageDraw.Draw(im)
    text_c(d, (400, 215), "BIURO", font(120), (240, 236, 228)); scenes["litery-2"] = im

    im = grad((12, 16, 30), (40, 34, 60)); facade(im, (34, 36, 44), True)
    neon(im, (400, 190), "BISTRO", 112, (255, 190, 40)); scenes["led-1"] = im

    im = grad((16, 20, 34), (36, 30, 52)); facade(im, (40, 42, 50), True)
    d = ImageDraw.Draw(im)
    glow = Image.new("RGB", (W, H)); ImageDraw.Draw(glow).rectangle([200, 120, 600, 230], fill=(255, 255, 255))
    im.paste(Image.blend(im, glow.filter(ImageFilter.GaussianBlur(26)), .3)); d = ImageDraw.Draw(im)
    d.rounded_rectangle([200, 120, 600, 230], radius=14, fill=(250, 250, 255), outline=(20, 20, 24), width=6)
    text_c(d, (400, 175), "SALON", font(70), (20, 70, 200)); scenes["led-2"] = im

    # tła górnej części stron: zdjęcie po prawej, płynnie przechodzące w granat po lewej
    navy = (15, 29, 39)
    for key, name in [("led-1", "hero-index"), ("szyld-2", "hero-szyldy"), ("litery-1", "hero-litery"), ("led-2", "hero-led")]:
        path = img_dir / f"{name}.webp"
        if path.exists():  # nigdy nie nadpisuj – usuń plik, żeby wygenerować przykład od nowa
            continue
        hw, hh = 1920, 1000
        hero = Image.new("RGB", (hw, hh), navy)
        sc = scenes[key].resize((960, 720))
        hero.paste(sc, (hw - 960, hh - 720))
        mask = Image.new("L", (hw, hh), 0)
        md_ = ImageDraw.Draw(mask)
        for x in range(hw):
            t = (x - 960) / 420
            md_.line([(x, 0), (x, hh)], fill=int(255 * max(0, min(1, t))))
        hero = Image.composite(hero, Image.new("RGB", (hw, hh), navy), mask)
        d = ImageDraw.Draw(hero)
        d.rounded_rectangle([hw - 330, hh - 54, hw - 20, hh - 18], radius=6, fill=(0, 0, 0))
        d.text((hw - 318, hh - 36), "PRZYKŁAD – podmień na zdjęcie", font=font(16, False), fill=(255, 255, 255), anchor="lm")
        hero.save(path, "WEBP", quality=72)

    for key, im in scenes.items():
        path = img_dir / f"{key}.webp"
        if path.exists():  # nigdy nie nadpisuj – usuń plik, żeby wygenerować przykład od nowa
            continue
        badge(im)
        im.save(path, "WEBP", quality=78)

    og = img_dir / "og-image.jpg"
    im = grad((12, 16, 30), (34, 30, 52))
    neon(im, (W // 2, 170), "SZYLDY", 120, (255, 190, 40))
    im = im.resize((1200, 900)).crop((0, 0, 1200, 630))
    d = ImageDraw.Draw(im)
    d.text((600, 430), "Szyldy reklamowe Gdynia", font=font(64), fill=(255, 255, 255), anchor="mm")
    d.text((600, 510), "Litery przestrzenne · Reklama LED · Projekt i montaż", font=font(32, False), fill=(200, 205, 215), anchor="mm")
    im.save(og, "JPEG", quality=85)


def images_makieta():
    """Szare prostokąty w miejscu zdjęć – do makiety."""
    from PIL import Image, ImageDraw, ImageFont
    img_dir = OUT / "img"
    img_dir.mkdir(parents=True, exist_ok=True)
    try:
        f = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 26)
    except OSError:
        f = ImageFont.load_default()
    for key in IMAGES:
        im = Image.new("RGB", (800, 600), (221, 221, 221))
        d = ImageDraw.Draw(im)
        d.line([(0, 0), (800, 600)], fill=(190, 190, 190), width=2)
        d.line([(0, 600), (800, 0)], fill=(190, 190, 190), width=2)
        d.rectangle([300, 278, 500, 322], fill=(221, 221, 221))
        d.text((400, 300), f"ZDJĘCIE: {key}", font=f, fill=(110, 110, 110), anchor="mm")
        im.save(img_dir / f"{key}.webp", "WEBP", quality=60)
    for name in ["hero-index", "hero-szyldy", "hero-litery", "hero-led"]:
        im = Image.new("RGB", (1920, 1000), (200, 200, 200))
        ImageDraw.Draw(im).text((1300, 500), f"ZDJĘCIE W TLE: {name}", font=f, fill=(110, 110, 110), anchor="mm")
        im.save(img_dir / f"{name}.webp", "WEBP", quality=50)
    im = Image.new("RGB", (1200, 630), (221, 221, 221))
    ImageDraw.Draw(im).text((600, 315), "og-image 1200×630", font=f, fill=(110, 110, 110), anchor="mm")
    im.save(img_dir / "og-image.jpg", "JPEG", quality=70)


def main():
    OUT.mkdir(exist_ok=True)
    pages = [p for p in PAGES if p["slug"] == "index"] if LANDING_ONLY else PAGES
    for p in PAGES:  # usuń stare podstrony z folderu do wgrania, gdy budujemy tylko landing
        f = OUT / f"{p['slug']}.html"
        if LANDING_ONLY and p["slug"] != "index" and f.exists():
            f.unlink()
    for p in pages:
        name = "index.html" if p["slug"] == "index" else f"{p['slug']}.html"
        (OUT / name).write_text(page_html(p), encoding="utf-8")
    (OUT / "sitemap.xml").write_text(sitemap(), encoding="utf-8")
    (OUT / "robots.txt").write_text(robots(), encoding="utf-8")
    (OUT / "llms.txt").write_text(llms(), encoding="utf-8")
    (OUT / "favicon.svg").write_text(FAVICON, encoding="utf-8")
    (OUT / "wycena.js").write_text((Path(__file__).parent / "wycena.js").read_text(encoding="utf-8"), encoding="utf-8")
    images_makieta() if MAKIETA else images()
    print("Gotowe:", ", ".join(sorted(x.name for x in OUT.iterdir())))


if __name__ == "__main__":
    main()
