# działajmy.online — identyfikacja do strony

## Co jest w paczce
- `dzialajmy-identyfikacja.css` — kolory, logo z kursorem, przycisk, pasy u góry i na dole, pas bloków, wyróżnienie w nagłówku.
- `przyklady.html` — mała strona z gotowymi fragmentami do skopiowania (otwórz w przeglądarce).
- `makieta-F-cala-strona.html` — cała makieta F z identyfikacją i formularzem (#zapytanie) jako jeden plik.
- `svg/` — pas bloków, pikselowa strzałka, kwadrat kursora (np. na favicon) i 13 ikonek pikselowych.

## Jak wpiąć
1. W `<head>` dodaj fonty i arkusz:
   ```html
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Onest:wght@400;600;700&family=Pixelify+Sans:wght@700&display=swap">
   <link rel="stylesheet" href="dzialajmy-identyfikacja.css">
   ```
2. Logo: `<a class="dz-logo" href="/" aria-label="działajmy.online">działajmy<b>.online</b><i></i></a>`
3. Przycisk: `<a class="dz-btn" href="#zapytanie">Działajmy, odhaczamy to <span class="dz-arrow" aria-hidden="true"></span></a>`
4. Pas u góry: `<div class="dz-bar" aria-hidden="true"></div>` zaraz po `<body>`; na dole `<div class="dz-bar dz-bar--bottom" aria-hidden="true"></div>` przed `</body>`.
5. Pas bloków: zawartość `svg/bloki-pas.svg` w `<div class="dz-blocks" aria-hidden="true">…</div>` jako pierwszy element ciemnego modułu (moduł: `overflow:hidden`, padding 56px / 28px na telefonie).

## Zasady
- Jeden przycisk w serwisie: limonka, zielony napis, róg 4px.
- Wszystkie rogi 4px.
- Piksele tylko: logo, strzałka w przycisku, pasy u góry i na dole, jeden pas bloków na stronę.
- Kolory i fonty zmieniasz w `:root` na górze pliku CSS.

## WordPress / kreator stron
CSS wklej w „Dodatkowy CSS” (Wygląd → Dostosuj) albo w ustawieniach motywu, a fragmenty HTML w bloki „Własny HTML”.
