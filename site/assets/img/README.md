# Zdjęcia z realizacji — instrukcja i konwencje (Image SEO)

Strona jest już gotowa na Wasze zdjęcia. **Nie trzeba ruszać HTML-a** — wystarczy wgrać
pliki JPG pod właściwe nazwy i ścieżki (do czasu wgrania widać placeholder, potem zdjęcie
pojawia się automatycznie dzięki `onerror`).

## Zasada 1 — nazwa pliku to słowa kluczowe

Nazwa pliku jest sygnałem SEO. Zamiast `IMG_3245.jpg` używaj opisu z myślnikami, po polsku,
bez polskich znaków:

```
mycie-elewacji-gdansk-wrzeszcz-01.jpg          ✅
prace-wysokosciowe-gdynia-port-przed.jpg       ✅
inspekcja-offshore-baltyk-turbina-02.jpg       ✅
DSC_0001.jpg                                   ❌
```

## Zasada 2 — rozmiar i format

- Szerokość docelowa: **1600 px** dla zdjęć w treści, **800 px** dla miniatur.
- Format: **JPG** (zdjęcia) lub **WebP** (jeśli hosting wspiera; wtedy zmień rozszerzenie w HTML).
- Waga: docelowo < 250 kB na zdjęcie (skompresuj, np. squoosh.app).
- Proporcje: miniatury 4:3, zdjęcia w treści 3:2, „przed/po" 3:2.

## Zasada 3 — atrybut `alt` (najważniejszy dla SEO obrazów)

`alt` jest już wpisany w HTML dla każdego placeholdera — opisuje, co MA być na zdjęciu.
Po wgraniu prawdziwego zdjęcia **sprawdź, czy alt zgadza się z tym, co widać**. Wzór alt:

```
[co robimy] + [obiekt] + [miasto]
"Mycie elewacji budynku wielorodzinnego metodą alpinistyczną, Gdańsk Wrzeszcz"
"Inspekcja łopaty turbiny wiatrowej w dostępie linowym IRATA, Bałtyk offshore"
```

## Gdzie wgrać pliki (mapa ścieżek)

| Folder | Do czego |
|---|---|
| `assets/img/realizacje/` | zdjęcia konkretnych realizacji (case studies) |
| `assets/img/uslugi/` | zdjęcia ilustrujące typy usług |
| `assets/img/og/` | obrazki Open Graph (podgląd przy udostępnianiu, 1200×630) |

Dokładne nazwy plików, których oczekują istniejące strony, znajdziesz szukając w kodzie
frazy `assets/img/` — każdy `<img>` ma docelową nazwę i gotowy `alt`.

## Zasada 4 — po dodaniu zdjęć zaktualizuj `sitemap.xml`

W `sitemap.xml` są już wpisy `<image:image>` dla przykładowych realizacji. Dla nowych
realizacji dopisz kolejne — to przyspiesza indeksację zdjęć w Grafice Google.

## Zasada 5 — prawa do zdjęć

Używaj **wyłącznie własnych zdjęć z realizacji** (macie do nich pełne prawa i są unikalne —
to najlepsze możliwe źródło pod SEO). Unikaj zdjęć stockowych z licencją „Editorial Use Only"
(uwaga z analizy Search Console, pkt 7).
