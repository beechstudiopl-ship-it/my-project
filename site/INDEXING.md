# Dlaczego Google nie indeksuje strony — diagnoza i naprawa

> Cel: doprowadzić do zaindeksowania wszystkich podstron i utrzymać stan „mistrzowski"
> strukturalnie. Poniżej diagnoza oparta na danych z analizy Search Console + konkretna
> checklista działań.

## Krok 0 — co już wiemy z danych (ważne)

Strona **główna JEST indeksowana** — w analizie miała 999 wyświetleń. To znaczy, że **nie ma
twardej blokady** (`noindex`, zablokowany robots.txt, kara). Problemem są **podstrony**:
strony miejskie miały zero wyświetleń, a wpis blogowy był „orphanowany" (bez linków
wewnętrznych). To klasyczny stan w Search Console:

> **„Discovered – currently not indexed"** / **„Crawled – currently not indexed"**

Google zna adresy, ale uznaje je za zbyt cienkie / słabo podlinkowane / o niskim autorytecie
i świadomie ich nie indeksuje. **To problem strukturalny, nie techniczny** — i dokładnie to
naprawia nowa struktura strony.

## Jak to naprawia nowa strona (już wdrożone w kodzie)

1. **Koniec z orphanami** — każda podstrona jest podlinkowana z nagłówka, stopki i z treści
   (strona główna → miasta → realizacje → usługi i z powrotem). Google dociera do wszystkiego.
2. **Unikalna, „gruba" treść** — strony miejskie i realizacje mają własny, niepowtarzalny tekst
   (nie kopie), co podnosi postrzeganą wartość strony.
3. **Sygnały świeżości i lokalności** — sekcja Realizacji ze zdjęciami z prac to najsilniejsze
   źródło unikalnej treści dla firmy usługowej.
4. **`sitemap.xml` + `robots.txt`** — mapa strony ze wszystkimi URL-ami i regułami dla botów.
5. **Dane strukturalne** (LocalBusiness / Service / Article / BreadcrumbList) — ułatwiają
   Google zrozumienie i indeksację.

## Checklista naprawcza w Google Search Console (zrób po wdrożeniu)

- [ ] **Zweryfikuj domenę** w Google Search Console (jeśli jeszcze nie).
- [ ] **Prześlij `sitemap.xml`** (Sitemapy → dodaj `https://rope-ag.pl/sitemap.xml`).
- [ ] Dla każdej kluczowej podstrony użyj **Sprawdzenia URL → Poproś o zindeksowanie**
      (strona główna, obie miejskie, usługi, realizacje, /en/).
- [ ] W raporcie **Strony (Indeksowanie)** sprawdź, ile URL-i jest „Nie zaindeksowano" i z jakim
      powodem — to Twój licznik postępu.
- [ ] Po 1–2 tygodniach ponów „Poproś o zindeksowanie" dla stron, które dalej nie weszły.

## Checklista techniczna — WYKLUCZ twarde blokady (zrób na żywej stronie)

Choć dane sugerują problem strukturalny, upewnij się, że nic nie blokuje botów:

- [ ] **`robots.txt`** — wejdź na `https://rope-ag.pl/robots.txt`. Nie może być `Disallow: /`
      ani blokady podstron. (Nowy plik `robots.txt` jest poprawny.)
- [ ] **Meta robots / nagłówek `X-Robots-Tag`** — żadna podstrona nie może mieć `noindex`.
      Uwaga na WordPressie: *Ustawienia → Czytanie → „widoczność w wyszukiwarkach"* musi być
      ODZNACZONE. To najczęstsza przypadkowa przyczyna deindeksacji.
- [ ] **Canonical** — każda podstrona musi wskazywać kanonicznie **na siebie**, nie na stronę
      główną. (W nowym kodzie tak jest.) Błędny canonical → Google traktuje podstrony jako
      duplikat strony głównej i ich nie indeksuje.
- [ ] **WAF / firewall (Cloudflare, Wordfence)** — sprawdź, czy Googlebot nie dostaje **403**.
      To realny scenariusz zasygnalizowany w readme wtyczki `claude-seo-ai`. Test:
      w GSC „Sprawdzenie URL → Pobierz jak Google" — jeśli zwraca błąd, odblokuj boty
      (GPTBot, Googlebot itd.) w regułach firewalla/WAF. Blokada bywa poza WordPressem.
- [ ] **HTTP → HTTPS i www vs bez-www** — jedna wersja kanoniczna, reszta przez przekierowanie 301.
- [ ] **Czas odpowiedzi / błędy 5xx** — wolny lub niestabilny serwer ogranicza budżet indeksacji.

## Po dodaniu zdjęć

- [ ] Uzupełnij `assets/img/` prawdziwymi zdjęciami (patrz `assets/img/README.md`).
- [ ] Dodaj wpisy `<image:image>` w `sitemap.xml` dla nowych realizacji → indeksacja w Grafice Google.

## Poza kodem (z analizy — realny wpływ na widoczność)

- **Profil Firmy w Google** — bez wypełnionej wizytówki (zdjęcia, kategorie, opinie) lokalne
  frazy nie zamienią się w kliknięcia nawet z pozycji 1 (wniosek #3 analizy).
- **Autorytet domeny / linki** — jeśli po naprawie strukturalnej część fraz (np. „prace
  wysokościowe Gdańsk") dalej stoi na 2. stronie, to już kwestia linków przychodzących,
  nie treści. Wtedy warto zająć się profilem linków.
