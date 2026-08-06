# Audyt SEO i plan wzmocnienia — Bogdanka Bieszczady

**Domena:** https://bogdankabieszczady.pl/
**Cel biznesowy:** wzmocnienie widoczności jako **obiekt dla grup, dużych firm, integracji i imprez firmowych**.
**Źródło danych:** Google Search Console, eksport „Skuteczność w wyszukiwarce".
**Zakres danych:** ostatnie 28 dni (**2026-07-08 → 2026-08-05**), Sieć (web).
**Data audytu:** 2026-08-06.

---

## 1. Podsumowanie zarządcze (TL;DR)

- W 28 dni: **424 kliknięcia**, **11 969 wyświetleń**, śr. CTR ~3,5%, śr. pozycja ~6–9.
- **Praktycznie cały ruch pochodzi z zapytań markowych** (bogdanka / kalnica). Frazy generyczne
  („ośrodek wypoczynkowy…", „imprezy integracyjne…") zbierają wyświetlenia, ale **niemal zero kliknięć**,
  bo strona rankuje na 2. stronie wyników lub dalej.
- **Wszystkie 7 fraz docelowych ma dziś 0 kliknięć.** Najbliżej progu TOP10 są frazy „ośrodkowe" i „noclegowe";
  cały klaster **firmowy / integracyjny leży na pozycjach 23–70** — mimo realnego popytu.
- Największy pojedynczy problem strukturalny: **kanibalizacja** — kilkanaście bliźniaczych podstron pakietowych
  i 3 konkurujące URL-e „konferencyjno-firmowe" rozbijają moc; do tego **strony testowe/śmieciowe w indeksie**.
- Wniosek: nie brakuje popytu ani indeksacji — brakuje **jednej mocnej, dedykowanej strony na intencję**
  + **linków wewnętrznych** + **porządku technicznego**. To jest do odzyskania stosunkowo szybko.

---

## 2. Frazy docelowe — stan bazowy (baseline)

Wszystkie poniższe frazy mają dziś **0 kliknięć**. Kolumna „Priorytet" = jak blisko konwersji na kliknięcie.

| # | Fraza docelowa | Pozycja | Wyśw. | Strona wyników | Priorytet |
|---|---|---|---|---|---|
| 1 | bieszczady noclegi dla grup | **7,9** | 18 | dół 1. str. | 🟢 quick win |
| 2 | ośrodek wypoczynkowy w bieszczadach | 10,3 | 53 | 2. str. | 🟢 quick win |
| 3 | bieszczady noclegi z wyżywieniem | 11,4 | 37 | 2. str. | 🟢 quick win |
| 4 | bieszczady noclegi dla grup szkolnych | 12,1 | 28 | 2. str. | 🟢 quick win |
| 5 | ośrodek wypoczynkowy bieszczady | 12,5 | 68 | 2. str. | 🟢 quick win |
| 6 | integracja w bieszczadach | 23,1 | 50 | 3. str. | 🟡 średni |
| 7 | wyjazdy integracje dla firm bieszczadach | 37,0 | 31 | 4. str. | 🔴 do zbudowania |
| 8 | sale konferencyjne bieszczady | 34,4 | 28 | 4. str. | 🔴 do zbudowania |
| 9 | imprezy firmowe bieszczady | 66,9 | 17 | 7. str. | 🔴 do zbudowania |
| 10 | **imprezy integracyjne bieszczady** | **70,4** | **63** | 7. str. | 🔴 **największy popyt** |

**Interpretacja:** frazy 1–5 (ośrodek / noclegi / grupy) są w „strefie strzału" — kilka poprawek on-page
i linki wewnętrzne mogą je wepchnąć na 1. stronę. Frazy 7–10 (**dokładnie ten segment, który chcesz wzmocnić**)
są daleko, bo **nie mają jednej dedykowanej, mocnej strony** — moc rozłażą się po duplikatach.

---

## 3. Analiza stron (co rankuje i gdzie leży problem)

**Strony, które niosą ruch (kliknięcia):**

| URL | Klik. | Wyśw. | CTR | Poz. |
|---|---|---|---|---|
| `/` (home) | 243 | 3407 | 7,1% | 6,7 |
| `/atrakcje/` | 65 | 2150 | 3,0% | 6,4 |
| `/pokoje/` | 39 | 1593 | 2,5% | 4,3 |
| `/oferta/` | 27 | 1152 | 2,3% | 3,9 |
| `/galeria/` | 24 | 969 | 2,5% | 2,5 |
| `/kontakt/` | 13 | 943 | 1,4% | 2,9 |

> **Homepage przenosi 57% wszystkich kliknięć.** Money keywords „lądują" na stronie głównej,
> co uniemożliwia wypozycjonowanie wyspecjalizowanej podstrony (np. na „imprezy integracyjne bieszczady").

**Kanibalizacja — klaster firmowy/konferencyjny (3 konkurujące URL-e):**

| URL | Wyśw. | Poz. |
|---|---|---|
| `/sale-konferencyjne-bieszczady/` | 6 | 4,8 |
| `/sale-konferencyjne-bieszczady-integracje-i-szkolenia-dla-firm/` | 11 | 21,1 |
| `/dla-organizatorow/` | 5 | 28,8 |

**Kanibalizacja — pakiety grupowe / wyżywienie (kilkanaście bliźniaczych podstron):**

- Kategorie: `/pakiety-dla-grup/` (poz. 15,4; 91 wyśw.), `/bieszczady-noclegi-z-wyzywieniem-dla-grup-zorganizowanych/`
  (poz. 10; 97 wyśw.), `/noclegi-z-wyzywieniem-bieszczady/` (poz. 26), `/bieszczady-noclegi-dla-grup-szkolnych/` (poz. 12,3).
- CPT `/o/…`: `pakiet-szkolny-dla-grup-3/4/5-dni`, `pakiet-bieszczady-dla-grup-w-3/4/5-dni`,
  `pakiet-esencja-bieszczad-dla-grup-w-3/4/5-dni`, `aktywne-bieszczady-grupy-zorganizowane…` — **pozycje 24–85**,
  wzajemnie się osłabiają i konkurują ze stronami kategorii.

**Śmieci / błędy w indeksie:**
- `/tst-2/` — strona **testowa**, a rankuje na pozycji **5,6** (marnuje crawl budget i wprowadza chaos).
- `/galeria-4/` — duplikat galerii.
- PDF-y w indeksie zamiast stron HTML: `…/Bieszczadzki-Sylwester-2025-1.pdf` (poz. 35,6),
  `…/Swieta-Bozego-Narodzenia-w-Bie…` (poz. 36,9). PDF prawie nie konwertuje i nie linkuje wewnętrznie.

**Urządzenia:** mobile 282 klik. (poz. 6,5) vs desktop 106 klik. (poz. **14,8**). Na desktopie rankujemy wyraźnie
gorzej — warto sprawdzić szybkość/układ desktopowy, ale priorytet ma treść i struktura.

---

## 4. Plan działań (priorytetowo)

### P1 — Klaster FIRMY / INTEGRACJE / IMPREZY  🔴 (główny cel biznesowy)
1. Zbudować **jeden filar**: `/imprezy-integracyjne-bieszczady/`
   - H1: **„Imprezy integracyjne i wyjazdy firmowe w Bieszczadach"**
   - Sekcje (osobne H2 + kotwice, każda pod inną intencję):
     `#integracje` (imprezy integracyjne), `#imprezy-firmowe` (eventy firmowe),
     `#wyjazdy-dla-firm` (wyjazdy integracyjne dla firm), `#sale-konferencyjne` (konferencje/szkolenia),
     `#dla-organizatora` (logistyka: do 135 osób, pełne wyżywienie, 2 sale multimedialne, SPA, ognisko, przewodnik).
2. **Skonsolidować duplikaty:** wybrać jeden URL na „sale konferencyjne"
   (`/sale-konferencyjne-bieszczady/` jako kanoniczny); pozostałe (`…-integracje-i-szkolenia-dla-firm/`,
   `/dla-organizatorow/`) → **301** na filar lub jego sekcję.
3. **Linkowanie wewnętrzne:** z `/` (home), `/oferta/`, `/pokoje/`, `/kontakt/` → filar,
   anchor = fraza docelowa (np. „imprezy integracyjne w Bieszczadach", „wyjazdy integracyjne dla firm").

### P2 — „Striking distance": str. 2 → str. 1  🟢 (szybki zysk)
- **ośrodek wypoczynkowy (w) bieszczadach** (poz. 10–12): cel = **home**. Wzmocnić H1/title,
  dodać w treści dokładne frazy, podlinkować z podstron.
- **bieszczady noclegi z wyżywieniem** (poz. 11): jedna **kanoniczna** strona kategorii (patrz P3),
  z „pełne wyżywienie", „śniadanie/obiad/kolacja", „pełny pakiet" w treści.
- **bieszczady noclegi dla grup** (poz. 7,9) i **…dla grup szkolnych** (poz. 12): wzmocnić dedykowane strony,
  podlinkować z home; scalić bliźniacze pakiety pod te kategorie.

### P3 — Porządki techniczne (kanibalizacja + indeks)  🟡
- `noindex` / usunięcie: `/tst-2/`, `/galeria-4/` (i inne duplikaty).
- **Wyprowadzić treści z PDF → HTML** (Sylwester, Święta, oferty sezonowe).
- **Konsolidacja `/o/…`:** wybrać kanoniczne warianty (np. 3-dniowy i 5-dniowy dla każdego typu grupy),
  resztę → 301 lub `rel=canonical` do właściwej strony kategorii; usunąć thin-content.
- Ustalić **po jednej kanonicznej stronie kategorii**: „noclegi z wyżywieniem", „noclegi dla grup",
  „grupy szkolne", „sale konferencyjne / integracje".
- **WAF/robots:** serwer zwraca 403 dla części botów. Zweryfikować w GSC (Sprawdzenie URL → Pobierz),
  że Googlebot i boty AI mają dostęp; wtyczka `claude-seo-ai` wpuszcza boty AI w robots.txt (patrz P5).

### P4 — Title & meta (poprawa CTR)  🟡
Wzór: `Fraza docelowa | USP | Bogdanka Bieszczady`. Nawet przy dobrej pozycji CTR jest niski
(np. `/oferta/` poz. 3,9 → 2,3%). Priorytet: home, filar firmowy, 4 strony kategorii, `/oferta/`, `/pokoje/`.
Propozycje w sekcji 6.

### P5 — Dane strukturalne / AEO (wtyczka `claude-seo-ai`)  🟢
Wtyczka jest w repo (`claude-seo-ai/`) i wstrzykuje JSON-LD Organization + FAQPage.
- **Zmienić `org_type`** z `EducationalOrganization` na **`Resort`** (dodane w tej zmianie do wtyczki) —
  to podtyp `LodgingBusiness`, właściwy dla ośrodka wypoczynkowego z noclegami i wyżywieniem.
- Uzupełnić dane firmy (sekcja 7).
- Dodać wpisy **FAQ (CPT „FAQ (AI SEO)")** pod grupy/firmy (sekcja 8) → FAQPage w wynikach + lepsza widoczność w AI.

---

## 5. Mapa fraza → docelowy URL (jedna intencja = jedna strona)

| Fraza | Docelowy URL (kanoniczny) |
|---|---|
| ośrodek wypoczynkowy bieszczady / w bieszczadach | `/` (home) |
| bieszczady noclegi z wyżywieniem | `/noclegi-z-wyzywieniem-bieszczady/` |
| bieszczady noclegi dla grup | `/pakiety-dla-grup/` |
| bieszczady noclegi dla grup szkolnych | `/bieszczady-noclegi-dla-grup-szkolnych/` |
| imprezy integracyjne bieszczady | `/imprezy-integracyjne-bieszczady/` (nowy filar) |
| imprezy firmowe bieszczady | `/imprezy-integracyjne-bieszczady/#imprezy-firmowe` |
| wyjazdy integracje dla firm bieszczadach | `/imprezy-integracyjne-bieszczady/#wyjazdy-dla-firm` |
| integracja w bieszczadach | `/imprezy-integracyjne-bieszczady/#integracje` |
| sale konferencyjne bieszczady | `/sale-konferencyjne-bieszczady/` (lub sekcja filaru) |

---

## 6. Gotowe title / meta description (do wklejenia)

> Zasada: `title` ≤ ~60 znaków, `meta description` ≤ ~155 znaków, fraza na początku, USP + telefon w opisie.

**Home (`/`)** — cel: „ośrodek wypoczynkowy bieszczady"
- **Title:** `Ośrodek wypoczynkowy Bieszczady dla grup i firm | Bogdanka`
- **Meta:** `Bogdanka Bieszczady – ośrodek dla grup do 135 osób: noclegi z pełnym wyżywieniem, 2 sale konferencyjne, SPA, integracje. Kalnica k. Wetliny. Tel. 797 797 097.`

**Filar (`/imprezy-integracyjne-bieszczady/`)** — cel: imprezy integracyjne / firmowe
- **Title:** `Imprezy integracyjne i firmowe w Bieszczadach | Bogdanka`
- **Meta:** `Wyjazdy integracyjne dla firm w Bieszczadach: sale konferencyjne, program integracji, ognisko, przewodnik, nocleg z wyżywieniem do 135 osób. Zapytaj o ofertę.`

**Noclegi z wyżywieniem (`/noclegi-z-wyzywieniem-bieszczady/`)**
- **Title:** `Bieszczady – noclegi z wyżywieniem dla grup | Bogdanka`
- **Meta:** `Noclegi z pełnym wyżywieniem w Bieszczadach (śniadanie, obiad, kolacja) dla grup i firm. Regionalna karczma, do 135 miejsc, Kalnica k. Wetliny.`

**Noclegi dla grup (`/pakiety-dla-grup/`)**
- **Title:** `Bieszczady – noclegi dla grup zorganizowanych | Bogdanka`
- **Meta:** `Noclegi dla grup w Bieszczadach: pokoje 2–4 os., pełne wyżywienie, sale, SPA, program pobytu. Grupy szkolne, firmowe i zorganizowane do 135 osób.`

**Grupy szkolne (`/bieszczady-noclegi-dla-grup-szkolnych/`)**
- **Title:** `Bieszczady – noclegi dla grup szkolnych, zielona szkoła | Bogdanka`
- **Meta:** `Wycieczki i zielone szkoły w Bieszczadach: noclegi z wyżywieniem, program dla grup szkolnych, przewodnik, atrakcje. Bezpiecznie, do 135 uczniów.`

**Sale konferencyjne (`/sale-konferencyjne-bieszczady/`)**
- **Title:** `Sale konferencyjne Bieszczady – szkolenia i integracje | Bogdanka`
- **Meta:** `2 sale konferencyjne z multimediami w Bieszczadach: szkolenia, konferencje, integracje firmowe. Nocleg z wyżywieniem na miejscu, do 200 osób.`

---

## 7. Dane firmy do wtyczki (Ustawienia → Claude SEO (AI))

| Pole | Wartość |
|---|---|
| Typ organizacji | **Resort** (osrodek wypoczynkowy) |
| Nazwa | Bogdanka Bieszczady – Karczma, SPA & Noclegi |
| Opis | Ośrodek wypoczynkowo-konferencyjny w Bieszczadach dla grup i firm: noclegi z wyżywieniem, sale konferencyjne, SPA, integracje. |
| E-mail | rezerwacje@bogdankabieszczady.pl |
| Telefon | +48 797 797 097 |
| Ulica i numer | Kalnica 5 |
| Kod pocztowy | 38-608 |
| Miejscowość | Wetlina |
| Region | podkarpackie |
| Kraj | PL |
| Obsługiwane obszary | Bieszczady, Wetlina, Kalnica, województwo podkarpackie |
| Profile (sameAs) | https://www.facebook.com/BogdankaBieszczady/ |

> Uzupełnij `URL logo` (pełny adres pliku logo). Potwierdź numer telefonu i dokładny kod pocztowy przed publikacją.

---

## 8. Propozycje wpisów FAQ (CPT „FAQ (AI SEO)") → FAQPage

1. **Dla ilu osób jest ośrodek Bogdanka w Bieszczadach?** — Przyjmujemy grupy do 135 osób z noclegiem
   i pełnym wyżywieniem; dysponujemy 2 salami konferencyjnymi.
2. **Czy organizujecie integracje i imprezy firmowe?** — Tak: wyjazdy integracyjne, ogniska z grillem,
   wędrówki z przewodnikiem, dedykowane programy integracyjne dla firm.
3. **Czy jest wyżywienie dla grup?** — Tak, pełne wyżywienie w regionalnej karczmie (śniadanie, obiad, kolacja).
4. **Czy przyjmujecie grupy szkolne / zielone szkoły?** — Tak, mamy programy i noclegi dla grup szkolnych.
5. **Gdzie znajduje się ośrodek?** — Kalnica 5 k. Wetliny, w sercu Bieszczadów, u podnóża Smereka.

---

## 9. Checklista wdrożeniowa

**P1 — Filar firmowy**
- [ ] Utworzyć stronę `/imprezy-integracyjne-bieszczady/` (H1 + sekcje z sekcji 4/P1).
- [ ] 301: `/dla-organizatorow/` i `/sale-konferencyjne-bieszczady-integracje-i-szkolenia-dla-firm/` → filar.
- [ ] Dodać linki wewnętrzne z home, `/oferta/`, `/pokoje/`, `/kontakt/` (anchor = fraza docelowa).

**P2 — Quick wins on-page**
- [ ] Home: title/meta z sekcji 6 + fraza „ośrodek wypoczynkowy w Bieszczadach" w H1/treści.
- [ ] Kanoniczne strony: „noclegi z wyżywieniem", „noclegi dla grup", „grupy szkolne" — dopracować treść.

**P3 — Technika**
- [ ] `noindex`/usuń: `/tst-2/`, `/galeria-4/`.
- [ ] PDF (Sylwester/Święta) → strony HTML.
- [ ] Skonsolidować bliźniacze pakiety `/o/…` (301 / canonical do kategorii).
- [ ] GSC → Sprawdzenie URL: potwierdzić dostęp Googlebota do kluczowych stron (WAF/403).

**P4 — CTR**
- [ ] Wdrożyć title/meta z sekcji 6 na 7 stronach.

**P5 — Schema/AEO (wtyczka)**
- [ ] Ustawienia → Claude SEO (AI): typ **Resort** + dane z sekcji 7.
- [ ] Dodać 5 wpisów FAQ (sekcja 8), zasięg „tylko strona główna" lub „cała witryna".
- [ ] Rich Results Test dla home i filaru.

---

## 10. Pomiar efektów (KPI)

- **Baseline** = tabela z sekcji 2 (frazy docelowe, dziś 0 kliknięć).
- Po **4–6 tygodniach** porównać w GSC: pozycję i CTR fraz docelowych oraz kliknięcia filaru firmowego.
- Cele:
  - Frazy 1–5 (ośrodek/noclegi/grupy) → wejście do TOP10 i pierwsze kliknięcia.
  - Klaster firmowy/integracyjny (dziś poz. 23–70) → skok do TOP20, docelowo TOP10 na „imprezy integracyjne bieszczady".
  - Filar `/imprezy-integracyjne-bieszczady/` zaindeksowany i zbierający wyświetlenia dla ≥4 fraz firmowych.
- Kontrola indeksu: `/tst-2/` i duplikaty wypadły z wyników (`site:` + raport „Strony" w GSC).

---

*Analiza oparta na eksporcie GSC z 28 dni (2026-07-08 → 2026-08-05). Po podłączeniu dłuższego zakresu danych
(3–12 mies.) warto powtórzyć analizę sezonowości — widoczne są frazy sezonowe (sylwester, ferie, wakacje).*
