VTG — The Vertical Transportation Group, Londyn, specjaliści od napraw dźwigów. „One group. Four specialist companies. Five focused websites.” Ten system obsługuje wszystkie pięć stron portfolio. Marka mówi trzema rzeczami z logotypu: **czernią**, **szczotkowaną stalą** i parą strzałek — **niebieską w górę** (`up`) i **bursztynową w dół** (`down`). Wszystko inne jest neutralne.

## Portfolio pięciu stron

| # | Strona | Pozycjonowanie | Kluczowe usługi |
| --- | --- | --- | --- |
| 01 | Group website — marka główna | The Vertical Transportation Group | Zapytania o pełny zakres: diagnostyka, naprawy mechaniczne i elektryczne, drzwi, refurbishment i modernizacja |
| 02 | Specialist site A | Mechanical Lift Repairs — traction & mechanical systems | Maszyny wyciągowe, silniki, hamulce, łożyska, przekładnie; liny, koła linowe, prowadniki, regulacje mechaniczne |
| 03 | Specialist site B | Lift Door Systems & Repairs — car & landing door expertise | Drzwi kabinowe i przystankowe, napędy drzwi, zamki, rolki, tory, wózki, mechanizmy, odnowienia |
| 04 | Specialist site C | Electrical Fault Finding — electrical systems & diagnostics | Sterowniki, napędy i falowniki, czujniki, obwody bezpieczeństwa, okablowanie, łączność, diagnostyka |
| 05 | Specialist site D | Modernisation & Refurbishment — upgrade & improvement projects | Modernizacje częściowe, wymiana sterowania, wymiana podzespołów wycofanych, odnowienie kabin |

- Wszystkie pięć stron używa **tych samych tokenów i komponentów**. Strony różni numer (01–05), nazwa specjalizacji i dobór zdjęć — nie kolor. Nie wprowadzaj osobnych akcentów kolorystycznych dla stron specjalistycznych: rozbiłoby to rozpoznawalność grupy.
- Każda strona ma na górze `GroupBar` (cienki pasek z pięcioma stronami, bieżąca wyróżniona `up`) i w nawigacji `SiteMark` (logo grupy + numer i nazwa specjalizacji). Ruch między stronami ma być widoczny, bo praca jest dzielona w zespole.
- Nazwy spółek i finalne logotypy stron specjalistycznych są **do potwierdzenia**. Do tego czasu `SiteMark` składa nazwę z typografii — nie rysuj dla nich znaków.
- Odbiorcy: firmy serwisujące dźwigi, facility managerowie, managing agents, właściciele budynków. To rozmowa B2B między fachowcami — treść ma być techniczna i konkretna.

## Zasady w skrócie

- Domyślny motyw to **Ciemny** (`dark`): grunt `surface-000`, tekst `ink`. Motyw **Jasny** służy podstronom treściowym (oferta, FAQ, polityki) — nawigacja, hero i stopka zostają wtedy ciemne (`data-theme="dark"` na ich korzeniu; komponenty `NavBar`, `Hero`, `Footer` robią to same).
- Niebieski i bursztyn to para znaczeniowa, nie dekoracja: `up` = akcja, ruch naprzód, link; `down` = zgłoszenie usterki, pilna naprawa, uwaga. Na jednym ekranie najwyżej jeden przycisk `primary` i najwyżej jeden `amber`.
- Kanciasto: `radius-0` dla kart, zdjęć i sekcji; `radius-sm` dla pól i badge; `radius-md` dla przycisków. Przyciski to zwykłe prostokąty — bez ścięć, skosów i nietypowych kształtów. Ścięty narożnik (`chamfer-lg`) zostaje tylko na zdjęciach.
- Pion. Kompozycje układaj w pionowe pasy i kolumny (szyb, prowadnice). Zdjęcia portretowe z szybu są atutem — nie przycinaj ich do poziomych pasków.

## Treść i ton

- Konkretnie i technicznie, spokojnie. Piszemy jak inżynier do inżyniera albo do zarządcy budynku: podzespół, objaw, zakres naprawy. Bez superlatywów („najlepszy”, „rewolucyjny”).
- Strony są po angielsku (Londyn), brytyjską pisownią i terminologią: *lift*, nie *elevator*; *modernisation*, *refurbishment*; *car and landing doors*; *fault finding*.
- Firma mówi o sobie „we”; do odbiorcy w 2. osobie („your lifts”, „your portfolio”).
- Nazwy podzespołów konkretnie, jak w dokumencie pozycjonowania: „traction machines, brakes, gearboxes”, „door operators, locks, rollers”, „controllers, drives and inverters”.
- Nagłówki zdaniowo (sentence case): „Lift door systems & repairs”, nie „Lift Door Systems & Repairs”. Wersaliki tylko w stylu `label` (tytuły kolumn stopki, etykieta wskaźnika piętra).
- CTA: czasownik na początku, max 3 słowa — „Report a fault”, „Request a quote”, „See projects”.
- Liczby i parametry krojem `data` (mono): „1000 kg · 13 persons · 1.6 m/s”. Numery telefonów zawsze jako link `tel:`.
- Bez emoji. Bez wymyślonych statystyk — liczby („1200 serviced lifts”) tylko od klienta.

## Kolor

| Rola | Token | Gdzie |
| --- | --- | --- |
| Grunt | `surface-000`, `surface-100` (sekcje naprzemienne) | Tła stron i sekcji |
| Karty | `surface-200`, hover `surface-300` + `shadow-card` | ServiceCard, ProjectCard, pola |
| Tekst | `ink`, `ink-muted` (lead, opisy), `ink-faint` (podpisy, tylko do `surface-200`) | |
| Akcja (w górę) | `up` jako tekst/ikona; `up-fill` + `on-up` jako przycisk; `glow-up` w hover | Linki, primary, aktywne menu |
| Uwaga (w dół) | `down` jako tekst/ikona; `down-fill` + `on-down` jako przycisk; `glow-down` | „Report a fault”, pilne naprawy, ostrzeżenia |
| Stal | `steel-hi` → `steel-mid` → `steel-lo` | Gradient w nagłówkach display (`.vtg-steel`), ikony neutralne |
| Linie | `line` (dekoracja), `line-strong` (ramki kontrolek) | |
| Status | `success`, `danger` — zawsze ze słowem lub ikoną | Formularze, status zgłoszenia |

- Nigdy biały tekst na `down-fill` — tylko `on-down`.
- Gradient niebiesko-bursztynowy łączący obie strzałki jest zarezerwowany dla logotypu. W UI kolory występują osobno.
- `up-glow` to wyłącznie poblask — nie tekst, nie tło dużych powierzchni.

## Typografia

- Jedna rodzina **Archivo** (Google Fonts, oś szerokości). Nagłówki `display-xl`, `display-l`, `h1`–`h3` w szerokości Expanded (`font-stretch: 125%`, dokłada `bundle.css`) i grubości 700–800 — najbliżej szerokich, ciężkich liter VTG. Tekst `lead`, `body`, `small` w szerokości normalnej.
- `label` (12px, 0.24em, wersaliki) nawiązuje do „TRANSPORTATION GROUP” i służy tylko jako etykieta danych: tytuły kolumn stopki, podpis wskaźnika piętra, nazwa grupy w `GroupBar`.
- **Nigdy** nie stawiaj nad nagłówkiem etykiety w wersalikach (nadtytułu), ani z kreską-akcentem przed nią, ani bez niej. Sekcję otwiera sam nagłówek; kontekst daje lead pod nim.
- **JetBrains Mono** (`data`, `floor`) tylko dla danych technicznych, numeracji kart i wskaźnika piętra.
- `display-xl` raz na stronę. Nagłówki display można wypełnić gradientem stali (`.vtg-steel`) — tylko na ciemnym gruncie i tylko od 36px w górę.
- Długość linii akapitu: `measure` (60ch).
- Mobile: `display-xl` → 48px, `display-l` → 40px, `h1` → 36px, `h2` → 28px (reguły w `bundle.css`, poniżej 720px).

## Układ i odstępy

- Kontener `container-max` 1280px, gutter `space-8` (desktop) / `space-4` (mobile).
- Sekcje: padding pionowy `space-24` (desktop) / `space-16` (mobile); hero `space-32`.
- Siatka kart: 3 kolumny, gap `space-6`; realizacje: siatka mieszana — jedno zdjęcie portretowe na dwie poziome.
- Linie 1px `line` oddzielają sekcje zamiast cieni.

## Ruch i stany

- Krótko i pionowo: hover kart podnosi je o 4px (`translateY(-4px)`) w 180ms `cubic-bezier(.2,.7,.2,1)`; strzałka w linku przesuwa się 4px w prawo. Respektuj `prefers-reduced-motion`.
- Focus: `outline: 2px solid var(--focus); outline-offset: 2px` na wszystkim, co klikalne.
- Disabled: 45% krycia, kursor `not-allowed`, bez poblasku.

## Logotyp

- Dostarczone wersje to **rastrowe rendery 3D** (stal + świecące strzałki) na czarnym tle — pliki w grupie **Logos**. Na www używaj `vtg-lockup-web.webp` w nawigacji (wys. 40–48px) i `vtg-lockup-dark.png` w stopce.
- Logo stawiaj wyłącznie na `surface-000` motywu ciemnego (czarne tło renderu musi zlać się z gruntem). Nie kładź go na jasnym tle, zdjęciach ani kolorze.
- Brakuje wersji wektorowej (SVG) i płaskiej jedno-/dwukolorowej — potrzebne na favicon, jasne tła i druk. Do uzupełnienia przez autora logo; do tego czasu nie odtwarzaj znaku samodzielnie.
- Rendery `vtg-mark-3d-*` (bez podpisu) to wersje wizerunkowe: tło hero strony 404, social media, prezentacje — nie nawigacja.

## Fotografia

- Grupa **Realizacje**: prawdziwe zdjęcia z prac VTG — szyb, prowadnice, podszybie, napęd MRL, hol z drzwiami przystankowymi. To podstawowy materiał wizualny strony; zero zdjęć stockowych i renderów AI.
- Charakter: dokumentalny, chłodny beton i stal, mocna perspektywa pionowa. Ujęcia w górę szybu (`szyb-prowadnice`, `szyb-dach-kabiny`) to najmocniejszy obraz marki — idealne do hero.
- Obróbka: lekka desaturacja i chłodny balans; nie podbijaj zieleni/czerwieni z osprzętu. Na zdjęciach z tekstem nakładaj gradient `surface-000` od dołu (0 → 85%).
- Zdjęcia zawsze z `radius-0` i opcjonalnie `chamfer-lg` w prawym górnym rogu. Podpis: `small` + `ink-faint`, lokalizacja i typ prac.
- `alt` opisuje to, co widać technicznie: „Lift shaft with guide rails, looking up”.

## Ikonografia

- Źródła nie zawierają zestawu ikon. `VTG.Icon` to **dodany** prosty zestaw liniowy (24px siatka, kreska 1.75px, końce kwadratowe — pasuje do kanciastej marki): `up`, `down`, `right`, `phone`, `mail`, `wrench`, `shield`, `clock`, `check`, `menu`. Gdy potrzeba więcej, dobieraj z Lucide z `strokeWidth 1.75` i `strokeLinecap square`.
- Strzałki `up`/`down` są znaczeniowe jak kolory: `up` w `up`, `down` w `down`.
- Ikony dziedziczą `currentColor`; neutralne w `steel-mid` lub `ink-muted`.

## Komponenty

`GroupBar`, `SiteMark`, `NavBar`, `Hero`, `SectionHeading`, `ServiceCard`, `ProjectCard`, `FloorIndicator`, `Button`, `Badge`, `Icon`, `Field`, `Footer` — globalny obiekt `window.VTG`, React 18. Źródła nie definiowały komponentów, więc zestaw jest dobrany pod portfolio pięciu stron firmowych. Szczegóły w README każdego komponentu.
