# Plan rozwoju strony rope-ag.pl

**Podstawa:** analiza Google Search Console za okres 6 lipca – 2 sierpnia 2026.
**Dokument sporządzony:** 2026-08-04.
**Zakres:** priorytetyzacja i uszczegółowienie zadań SEO/UX wynikających z danych GSC,
z przypisaniem nakładu, oczekiwanego efektu i kryteriów odbioru.

---

## 1. Punkt wyjścia (baseline)

Dane, do których będziemy porównywać efekty. To jest „linia zero" — pierwszy pomiar po wdrożeniach
odnosimy właśnie do tych liczb.

| Metryka | Wartość (28 dni) |
|---|---|
| Wyświetlenia | 1 017 |
| Kliknięcia | 19 (≈0,7 dziennie) |
| CTR | 1,87 % |
| Średnia pozycja | 11,3 |
| Udział strony głównej w wyświetleniach | 98 % (999/1017) |
| Wyświetlenia stron miejskich (`/prace-wysokosciowe-gdansk/`, `/…-gdynia/`) | 0 |

**Diagnoza jednym zdaniem:** Google zauważył domenę, ale cały ruch skupia na stronie głównej,
która kanibalizuje własne podstrony miejskie; lokalne frazy nie zamieniają się w kliknięcia
z powodu braku wizytówki, a najwyższy niewykorzystany potencjał leży w rynku offshore (EN)
i w segmencie mobile.

> **Zastrzeżenie metodologiczne (z notatki):** przy tej skali wiersze z 1–3 wyświetleniami to szum.
> Plan opiera decyzje wyłącznie na sumach i na dziesięciu największych zapytaniach.

---

## 2. Cel i KPI (horyzont 3–4 miesiące)

| KPI | Baseline | Cel 3–4 mies. |
|---|---|---|
| Kliknięcia / miesiąc (organik) | ~20 | **80–150** |
| Wyświetlenia stron miejskich | 0 | > 0 i rosnące (obie strony indeksowane, poz. < 15) |
| Pozycja „prace wysokościowe gdańsk" | 21,3 | < 10 |
| CTR desktop | 1,0 % | > 2,0 % |
| Kliknięcia z rynków EN (NO/UK/NL/DE/DK/SE) | 3 | ≥ 15 |
| Wizytówka Google (GBP) | brak/zaniedbana | zweryfikowana, kompletna, opinie napływają |

Cel jest świadomie ostrożny: baza jest bardzo niska, więc pierwsze poprawki dadzą duży skok
procentowy, ale w liczbach bezwzględnych mówimy o setkach, nie tysiącach kliknięć.

---

## 3. Zasady nadrzędne (obowiązują we wszystkich zadaniach)

1. **Koniec kanibalizacji** — jedna fraza = jedna strona docelowa. Strona główna celuje w markę
   i „pomorskie"; strony miejskie w konkretne miasta.
2. **Mobile-first przy konwersji** — mobile ma 3× lepszy CTR i pozycję 5,4 vs 13,9. Każdą decyzję
   o ścieżce kontaktu (przycisk, numer telefonu) projektujemy z ekranu telefonu.
3. **Wizytówka przed pozycją organiczną** — dla lokalnych fraz usługowych nad organikiem stoi pakiet
   map. Bez GBP nawet pozycja 1 nie da kliknięć.
4. **Mierzalność** — każde zadanie ma kryterium odbioru i metrykę, którą sprawdzamy w GSC po 4–6 tyg.

---

## 4. Roadmapa fazowa

Kolejność wynika z rewizji priorytetów w notatce: najpierw darmowe naprawy o największym ROI,
potem lokalne SEO, potem najwartościowsza ekspansja EN.

### Faza 0 — Naprawy krytyczne (tydzień 1) · koszt ~0, najwyższy ROI

| # | Zadanie | Nakład | Efekt |
|---|---|---|---|
| 0.1 | Rozdzielenie `title` strony głównej i stron miejskich (koniec kanibalizacji) | S | Wysoki |
| 0.2 | Naprawa linku `tel:` (spacja po `+`) | XS | Średni (uderza w najlepszy segment) |
| 0.3 | Założenie / weryfikacja Google Business Profile | M | Wysoki (warunek dla całego lokalnego SEO) |

### Faza 1 — Treść i lokalne SEO (tygodnie 2–4)

| # | Zadanie | Nakład | Efekt |
|---|---|---|---|
| 1.1 | Wpleść „usługi wysokościowe" i „usługi alpinistyczne" w nagłówki/`title` stron miejskich | S | Wysoki (108 wyśw. czeka) |
| 1.2 | Rozbudowa i realne zaindeksowanie stron `/prace-wysokosciowe-gdansk/` i `/…-gdynia/` | M | Wysoki |
| 1.3 | Uzupełnienie danych strukturalnych (schema LocalBusiness) przez wtyczkę | S | Średni |
| 1.4 | Naprawa permalinków bloga (anglojęzyczny wpis rankuje na 6,8 mimo złego URL) | S | Średni |

### Faza 2 — Ekspansja EN / offshore (miesiąc 2) · najwyższy potencjał wartościowy

| # | Zadanie | Nakład | Efekt |
|---|---|---|---|
| 2.1 | Sekcja `/en/` z `hreflang` | L | Wysoki |
| 2.2 | Landing page: *Offshore rope access services Poland* + *IRATA rope access contractor Baltic* | M | Wysoki |
| 2.3 | Blok prekwalifikacyjny (nr IRATA, poziom, GWO, OC, statystyki BHP) | S | Wysoki dla B2B |

### Faza 3 — Utrwalenie i pomiar (miesiące 3–4)

| # | Zadanie | Nakład | Efekt |
|---|---|---|---|
| 3.1 | Systematyczne pozyskiwanie opinii + rozbudowa wizytówki (zdjęcia, kategorie, opis) | M (ciągłe) | Wysoki |
| 3.2 | Decyzja biznesowa: linia szkoleń IRATA/GWO — rozwijać czy odfiltrować | S | Zależny |
| 3.3 | Wymiana zdjęcia z licencją „Editorial Use Only" | XS | Ryzyko prawne |
| 3.4 | Przegląd wyników w GSC i korekta planu | S | — |

Legenda nakładu: XS < 1 h · S ~½ dnia · M ~1–2 dni · L > 2 dni.

---

## 5. Szczegóły zadań (kryteria odbioru)

### 0.1 — Rozdzielenie `title` (kanibalizacja) 🔴 priorytet #1

**Problem:** `title` strony głównej — *„Prace na wysokościach Pomorskie | IRATA | ROPE Access Group Gdańsk"*
— zawiera jednocześnie „Gdańsk", „prace na wysokościach" i „Pomorskie", więc konkuruje z każdą własną
podstroną. Efekt: strona główna na poz. 21 zamiast dedykowanej strony na poz. 5.

**Działanie — propozycje `title` (do dopracowania w CMS):**

- Strona główna → marka + region, **bez** nazw miast:
  `ROPE Access Group — prace i usługi wysokościowe, Pomorskie (IRATA)`
- `/prace-wysokosciowe-gdansk/` → miasto + synonimy (patrz 1.1):
  `Prace wysokościowe Gdańsk — usługi alpinistyczne i wysokościowe | RAG`
- `/prace-wysokosciowe-gdynia/` → analogicznie dla Gdyni.

**Kryterium odbioru:** żadna para (strona główna ↔ strona miejska) nie współdzieli tej samej frazy
głównej w `title`/H1. Po 4–6 tyg. strona miejska pojawia się w GSC z niezerowymi wyświetleniami,
a pozycja frazy „prace wysokościowe gdańsk" rośnie (spada liczbowo poniżej ~15).

### 0.2 — Naprawa linku `tel:` 🔴

**Problem:** `tel:+ 48 501...` — spacja po `+` psuje klikalność numeru dokładnie w segmencie mobile,
który ma najlepszy CTR.

**Działanie:** poprawić na `tel:+48501...` (bez spacji, format E.164) we wszystkich miejscach
(nagłówek, stopka, przyciski kontaktu). Dodać klikalny przycisk WhatsApp jako główną ścieżkę mobilną.

**Kryterium odbioru:** kliknięcie numeru na telefonie inicjuje połączenie; link przechodzi walidację
E.164; przycisk WhatsApp działa.

### 0.3 — Google Business Profile 🔴

**Problem:** „usługi alpinistyczne gdańsk" — pozycja 1,96, ale **0 kliknięć**. Przy poz. 2 oczekiwany
CTR to 10–15 %. Zero oznacza, że nad organikiem stoi pakiet map i użytkownik klika w wizytówkę,
nie w link.

**Działanie:** założyć/zweryfikować wizytówkę; uzupełnić kategorie, opis, godziny, obszar działania,
komplet zdjęć realizacji; wyeksponować link do opinii (dziś schowany w stopce); uruchomić
systematyczne pozyskiwanie opinii (patrz 3.1).

**Kryterium odbioru:** wizytówka zweryfikowana i kompletna; ≥ 5 nowych opinii w pierwszym kwartale;
link do opinii widoczny nad zakładką (nie tylko w stopce).

### 1.1 — Synonimy „usługi wysokościowe" / „usługi alpinistyczne"

**Problem:** największe zapytanie w całym zbiorze — **„usługi wysokościowe gdańsk", 108 wyświetleń** —
a fraza „usługi wysokościowe" nie występuje na stronie ani razu. Zależność jest bezpośrednia:
„usługi alpinistyczne gdańsk" (jest w treści, w górnym pasku) ma pozycję 1,96.

**Działanie:** wpleść „usługi wysokościowe" i „usługi alpinistyczne" jako równoprawne warianty
w H1/H2 i `title` stron miejskich oraz w treść (naturalnie, bez upychania).

**Kryterium odbioru:** obie frazy występują w H1/H2 i `title` stron miejskich; w GSC pojawiają się
wyświetlenia dla „usługi wysokościowe gdańsk" na stronie miejskiej.

### 1.2 — Realne zaindeksowanie stron miejskich

**Działanie:** rozbudować treść stron Gdańsk/Gdynia (zakres usług, realizacje, lokalne odnośniki,
wewnętrzne linkowanie ze strony głównej i menu), zgłosić do indeksacji w GSC, sprawdzić w raporcie
„Strony", czy nie są wykluczone.

**Kryterium odbioru:** obie strony w statusie „zindeksowana" w GSC i z niezerowymi wyświetleniami.

### 1.3 — Dane strukturalne (schema) przez wtyczkę Claude SEO AI

Repozytorium zawiera wtyczkę `claude-seo-ai`, która wstrzykuje schema `LocalBusiness`/`Organization`
i `FAQPage`. **Nie trzeba pisać kodu** — wystarczy konfiguracja w *Ustawienia → Claude SEO (AI)*:

- `org_type` = `LocalBusiness`,
- `telephone` w formacie E.164 (spójne z naprawą 0.2),
- `address` (ulica, kod, miasto, `region` = Pomorskie),
- `area_served` = Gdańsk, Gdynia, Sopot, Trójmiasto, Pomorskie,
- `same_as` = profile (w tym **URL wizytówki Google** — spina schema z GBP),
- FAQ (CPT „FAQ (AI SEO)") z realnymi pytaniami klientów → `FAQPage`.

**Kryterium odbioru:** test w Rich Results / walidatorze schema przechodzi bez błędów dla
`LocalBusiness` i `FAQPage`; telefon i adres zgodne z wizytówką (spójne NAP).

### 1.4 — Permalinki bloga

**Problem:** anglojęzyczny wpis „Industrial Rope Access Poland" rankuje na poz. 6,8 — lepiej niż strona
główna (11,25) — mimo że stoi na polskim URL-u, który mu szkodzi.

**Działanie:** ustawić czytelną strukturę permalinków; dla wpisów EN nadać anglojęzyczne slugi;
przy zmianie URL-i ustawić przekierowania 301, żeby nie stracić obecnej pozycji.

**Kryterium odbioru:** wpisy EN mają anglojęzyczne slugi; stare adresy przekierowane 301; brak spadku
pozycji po zmianie (kontrola w GSC po 2–3 tyg.).

### 2.1–2.3 — Sekcja `/en/` pod offshore

**Uzasadnienie z danych:** Norwegia CTR 25 %, pozycje NL 5,8 / DE 5,7 — **lepsze niż w Polsce (11,8)**.
To mapa rynku offshore Morza Północnego i Bałtyku. Wartość zlecenia B2B offshore jest nieporównanie
wyższa niż lokalna wspólnota mieszkaniowa, a strona **już tam jest widoczna — tylko nie ma czego kliknąć**.

**Działanie:**
- `/en/` jako pełna sekcja z `hreflang` (`en` ↔ `pl`, plus `x-default`),
- landing pages: *Offshore rope access services Poland*, *IRATA rope access contractor Baltic*,
- blok prekwalifikacyjny B2B: nr członkostwa IRATA, poziom (L1–L3), certyfikaty GWO, polisa OC,
  statystyki BHP/LTI — to, czego szuka dział zakupów kontraktora offshore.

**Kryterium odbioru:** sekcja `/en/` zaindeksowana; poprawny `hreflang` (walidacja bez błędów);
w GSC rosnące wyświetlenia/kliknięcia z NO/UK/NL/DE/DK/SE.

### 3.2 — Decyzja: szkolenia IRATA/GWO

**Sygnał (słaby, pojedyncze liczby — traktować ostrożnie):** „szkolenie irata", „kursy irata",
„irata oferty pracy", „irata gdansk" (18 wyśw., poz. 9,4). Firma deklaruje bycie instruktorami GWO.

**Decyzja do podjęcia przez właściciela:**
- **Jeśli szkolenia to realna linia biznesowa** → osobna strona kursów + schema `Course`
  (wtyczka to obsługuje: metabox „Ta strona opisuje kurs").
- **Jeśli nie** → odfiltrować te zapytania w analizie, bo generują ruch, który nigdy nie kupi usługi.

### 3.3 — Licencja zdjęcia „Editorial Use Only"

Bez związku z danymi GSC, ale realne ryzyko prawne: zdjęcie z licencją „tylko do użytku redakcyjnego"
użyte komercyjnie. Wymienić na zdjęcie z licencją komercyjną lub własne.

---

## 6. Mapowanie zadań na wtyczkę `claude-seo-ai`

Część planu realizuje się **konfiguracją istniejącej wtyczki**, bez nowego kodu:

| Zadanie | Mechanizm wtyczki |
|---|---|
| 1.3 Schema firmy / NAP | `CSA_Schema::output_organization()` — pola `org_type`, `telephone`, `address`, `area_served`, `same_as` |
| 0.3 Spięcie z wizytówką | `same_as` = URL Google Business Profile |
| FAQ pod AI i rich results | CPT „FAQ (AI SEO)" → `FAQPage` |
| 3.2 Kursy (jeśli tak) | metabox „Ta strona opisuje kurs" → schema `Course` |
| Widoczność w AI (ChatGPT/Claude/Perplexity) | moduł robots.txt (boty AI) |

> Uwaga techniczna z readme wtyczki: moduł robots.txt działa tylko, gdy WordPress generuje
> `robots.txt` dynamicznie (brak statycznego pliku w katalogu głównym), a blokady botów (403)
> często pochodzą z WAF (Cloudflare/Wordfence) — to trzeba odblokować poza WordPressem.

---

## 7. Pomiar i rytm przeglądów

- **Po każdej fazie** (4–6 tyg. od wdrożenia) porównanie z baseline z sekcji 1 w GSC.
- **Kluczowe raporty GSC:** „Skuteczność" (filtr per zapytanie/strona/kraj/urządzenie),
  „Strony" (status indeksacji stron miejskich i `/en/`).
- **Segmenty do śledzenia osobno:** mobile vs desktop, PL vs rynki EN, strona główna vs strony miejskie.
- **Comiesięczny przegląd:** aktualizacja KPI z sekcji 2 i korekta kolejności zadań.

---

## 8. Otwarte pytania / dane, które podniosłyby trafność planu

- **Eksport z 12 miesięcy** — pozwoli ocenić trend i rozstrzygnąć, czy poz. 21 dla „prace wysokościowe
  gdańsk" to problem treści, czy autorytetu domeny.
- **Dane z Ahrefs (profil linków)** — do oceny autorytetu domeny i decyzji, czy potrzebny link building.
- **Decyzja o linii szkoleń** (zad. 3.2) — determinuje, czy budujemy sekcję kursów.

---

### Podsumowanie priorytetów (kolejność wdrażania)

1. Rozdzielić `title` strony głównej i stron miejskich (kanibalizacja — kosztuje najwięcej, naprawa darmowa).
2. Google Business Profile (bez tego lokalne frazy nie zamienią się w kliknięcia).
3. Dodać „usługi wysokościowe" / „usługi alpinistyczne" do treści i `title` (108 wyświetleń czeka).
4. Naprawić link `tel:` (psuje najlepiej konwertujący segment).
5. Sekcja `/en/` pod offshore (najwyższy potencjał wartościowy, pozycje już są).
6. Naprawić permalinki bloga.
7. Wymienić zdjęcie „Editorial Use Only" (ryzyko prawne).
