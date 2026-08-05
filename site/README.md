# ROPE Access Group — nowa strona (rope-ag.pl)

Statyczna, mobile-first strona zbudowana wprost na wnioskach z analizy Search Console
(6 lipca – 2 sierpnia 2026). Bez zależności zewnętrznych — czyste HTML/CSS + odrobina JS.
Wrzucasz na dowolny hosting statyczny (Netlify, Cloudflare Pages, S3, nginx/Apache) albo
przenosisz do WordPressa jako szablon.

## Jak uruchomić lokalnie

```bash
cd site
python3 -m http.server 8000
# otwórz http://localhost:8000
```

## Struktura i decyzje SEO (z numerami wniosków z analizy)

| Ścieżka | Cel / fraza | Wniosek |
|---|---|---|
| `/` | marka + „Pomorskie" + IRATA + offshore — **bez** frazy miejskiej w title | #1 koniec kanibalizacji |
| `/prace-wysokosciowe-gdansk/` | „prace/usługi wysokościowe Gdańsk", „usługi alpinistyczne Gdańsk" | #1, #2 |
| `/prace-wysokosciowe-gdynia/` | jw. dla Gdyni (akcent portowo-przemysłowy) | #1, #2 |
| `/uslugi/prace-na-wysokosci-przemyslowe/` | pełen zakres usług, wariant „usługi wysokościowe" | #2 |
| `/en/` | „offshore rope access Poland", „IRATA contractor Baltic" + hreflang + dane prekwalifikacyjne | #4 |
| `/zespol/`, `/kontakt/` | E-E-A-T, konwersja | #3, #5 |

Wdrożone bezpośrednio z analizy:
- **#1 Rozdzielenie title** — strona główna vs. strony miejskie nie nakładają się na frazy.
- **#2 Warianty fraz** — „usługi wysokościowe" i „usługi alpinistyczne" wplecione w nagłówki, treść i tagi.
- **#3 Wizytówka/opinie** — link do opinii Google wyeksponowany (nie schowany w stopce) na każdej stronie.
- **#4 Sekcja `/en/`** — pełny landing offshore z hreflang, danymi IRATA/GWO/OC/BHP do prekwalifikacji.
- **#4 (mobile)** — **naprawiony `tel:`** bez spacji po plusie (`tel:+48501000000`), sticky pasek Zadzwoń/WhatsApp na dole ekranu, klikalny numer i przycisk WhatsApp wszędzie.
- **#6 permalinki** — czyste, płaskie adresy z hreflang; brak „orphanów" jak we wcześniejszej strukturze bloga.
- Dane strukturalne JSON-LD (LocalBusiness / Service / BreadcrumbList) + `robots.txt` z regułami dla botów AI (spójne z wtyczką `claude-seo-ai`) + `sitemap.xml`.

## DO PODMIANY przed publikacją (placeholdery)

Wpisałem dane firmy tak, jak wynikają z analizy, ale kilka rzeczy to **placeholdery** —
przeszukaj i podmień w całym katalogu `site/`:

| Placeholder | Gdzie | Na co zmienić |
|---|---|---|
| `+48 501 000 000` / `+48501000000` / `wa.me/48501000000` | wszędzie | prawdziwy numer (był ucięty w analizie: „+48 501…") |
| `biuro@rope-ag.pl` | wszędzie | prawdziwy adres e-mail |
| `https://g.page/r/rope-access-group/review` | link do opinii | prawdziwy link „napisz opinię" z Profilu Firmy w Google |
| `foundingDate: 2001` / „od 2001", „25 lat" | JSON-LD, treść | dokładny rok założenia |
| `action="#"` w formularzu (`/kontakt/`) | formularz | endpoint (np. Formspree lub własny) |
| Adres pocztowy w JSON-LD | `/` (LocalBusiness) | pełny adres, jeśli ma być publiczny |

> Uwaga: numeru telefonu, dokładnego e-maila i roku założenia **nie znałem z danych** —
> dlatego są to jawne placeholdery, a nie zmyślone wartości. Podmień je przed startem.

## Indeksacja (Google nie indeksuje strony)

Osobny dokument: **`INDEXING.md`** — diagnoza (najpewniej „Discovered/Crawled – currently not
indexed", czyli problem strukturalny, nie blokada) + checklista naprawcza w Google Search Console
i lista twardych blokad do wykluczenia (robots, noindex, canonical, WAF/403).

## Realizacje i zdjęcia (Image SEO)

- `/realizacje/` — hub portfolio + dwie gotowe realizacje (`mycie-elewacji-gdansk-wrzeszcz`,
  `inspekcja-offshore-baltyk`) jako szablony. Kopiujesz katalog, podmieniasz treść i zdjęcia.
- Zdjęcia: patrz **`assets/img/README.md`** (nazwy plików = słowa kluczowe, `alt`, rozmiary).
  Do czasu wgrania prawdziwych JPG widać placeholder (`onerror` → `placeholder.svg`) —
  **HTML-a nie trzeba ruszać**, wystarczy wgrać pliki pod właściwe nazwy.
- `sitemap.xml` zawiera wpisy `<image:image>` dla realizacji (indeksacja w Grafice Google).

## Do zrobienia poza kodem (z analizy, nie wchodzi do repo)
- Uzupełnić **Profil Firmy w Google** (zdjęcia, kategorie, opis, systematyczne opinie) — wniosek #3.
- Sprawdzić licencję zdjęcia oznaczonego „Editorial Use Only" — wniosek #7 (ryzyko prawne).
