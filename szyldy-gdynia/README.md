# Szyldy reklamowe Gdynia – strona gotowa do wgrania

Są dwie wersje z tą samą treścią i SEO:
- `strona/` – pełna wersja z wyglądem (`python3 build.py`),
- `makieta/` – makieta bez stylu: szkielet w odcieniach szarości, font systemowy, szare prostokąty zamiast zdjęć (`python3 build.py --makieta`).

Na serwer wgrywasz **zawartość jednego z tych folderów** (do głównego katalogu domeny).
`build.py` to generator. Dane firmy wpisujesz w nim raz, w sekcji `FIRMA`, i uruchamiasz `python3 build.py`.
Wtedy nazwa, adres, telefon i e-mail są identyczne na każdej podstronie, w JSON-LD i w llms.txt.
Treści podstron też są w `build.py`, w sekcji `PAGES`. Jeśli edytujesz HTML ręcznie, nie uruchamiaj potem `build.py`, bo nadpisze Twoje zmiany.

## Pliki w `strona/`
| Plik | Co to jest |
|---|---|
| index.html | Strona główna (LocalBusiness + FAQPage) |
| szyldy-reklamowe-gdynia.html | Usługa: szyldy (Service + FAQPage + Breadcrumb) |
| litery-przestrzenne-gdynia.html | Usługa: litery 3D (Service + FAQPage + Breadcrumb) |
| reklama-led-gdynia.html | Usługa: reklama LED (Service + FAQPage + Breadcrumb) |
| realizacje.html | Galeria realizacji (FAQPage + Breadcrumb) |
| cennik.html | Cennik + formularz wyceny (FAQPage + Breadcrumb) |
| kontakt.html | Kontakt, formularz, mapa (LocalBusiness + FAQPage + Breadcrumb) |
| style.css | Jeden wspólny arkusz stylów |
| sitemap.xml, robots.txt, llms.txt | Mapa strony, reguły dla robotów (także AI), opis dla czatów AI |
| favicon.svg | Ikona strony |
| img/*.webp | 6 zastępczych zdjęć realizacji, do podmiany |
| img/og-image.jpg | Obrazek do udostępnień (1200×630) |

## Co musisz uzupełnić
Każde miejsce do uzupełnienia jest na stronie podświetlone na żółto (`[UZUPEŁNIJ: …]`). Wpisuj tylko prawdziwe dane.

**1. Dane firmy (raz, w `build.py` → `FIRMA`):** krótka nazwa marki w nagłówku (`logo`), domena (`DOMENA`), pełna nazwa, ulica i numer, kod pocztowy,
telefon (wyświetlany i w formacie +48…), e-mail, NIP, godziny pn–pt i w soboty (tekst oraz format 24h do JSON-LD),
obszar działania, link do wizytówki w Mapach Google, adres obsługi formularza, adres polityki prywatności.

**2. Treści, które powtarzają się na kilku podstronach:**
- czas realizacji (dni robocze od akceptacji projektu),
- gwarancja: osobno na konstrukcję i osobno na LED i zasilacze,
- widełki cenowe: szyld płaski, kaseton LED, litery bez podświetlenia i z LED, montaż; netto czy brutto,
- montaż: sami czy przez partnera, podnośnik, kto robi podłączenie elektryczne,
- lista materiałów, z którymi faktycznie pracujecie, i park maszynowy,
- projekt graficzny lub logo od zera: czy robicie i czy jest w cenie; czy wizualizacja jest bezpłatna,
- pomoc przy zgodach i formalnościach,
- obszar działania poza Gdynią (tylko miejscowości, gdzie naprawdę montujecie),
- czy wykonujecie ekrany LED, sterowniki czasowe i modernizację kasetonów.

**3. Do uzupełnienia na konkretnych podstronach:**
- index: doświadczenie (od kiedy działacie, ile realizacji), opinie klientów; galeria to 6 zdjęć w różnych rozmiarach (bez podpisów, opisy w tekście alt),
- realizacje: opisy realizacji (branża, lokalizacja, wykonanie, materiały, czas), liczba realizacji w kategoriach,
- cennik: czas odpowiedzi na zapytanie, co jest wliczone w cenę,
- kontakt: parking i dojazd, mapa Google (wklej kod iframe z `loading="lazy"`), czas odpowiedzi.

**4. Zdjęcia:** podmień przykładowe grafiki w `img/` (szyld-1, szyld-2, litery-1, litery-2, led-1, led-2 – mają napis „PRZYKŁAD”) na prawdziwe zdjęcia w formacie WebP, 800×600, i zachowaj te same nazwy.
Teksty alt i podpisy zmienisz w `build.py` → `IMAGES`.

**Szybka wycena (plik `wycena.js`):** na każdej stronie na dole jest pasek: Zadzwoń, WhatsApp, Wyślij zdjęcie. „Wyślij zdjęcie” otwiera krótki formularz: zdjęcie, telefon lub e-mail, wyślij. Wymiary (z polem w m²), pomiar szyldu na zdjęciu, rodzaj reklamy, adres i wiadomość są zwinięte jako opcjonalne. Zdjęcia większe niż 1,5 MB są zmniejszane w przeglądarce przed wysłaniem. Do uzupełnienia w `FIRMA`: `whatsapp` i `czas_odpowiedzi`.

**5. Formularz:** strona jest statyczna, więc formularz potrzebuje obsługi (np. Formspree, Getform albo skrypt PHP na Twoim hostingu).
Wpisz jej adres w `FIRMA["formularz_action"]`. Obsługa musi przyjmować załączniki.

**6. Po publikacji:** dodaj `sitemap.xml` w Google Search Console. Nazwa, adres i telefon muszą być takie same jak w Profilu Firmy w Google i w katalogach.
Sprawdź JSON-LD w teście wyników z elementami rozszerzonymi (Rich Results Test), a dane w FAQ w JSON-LD muszą odpowiadać tekstom na stronie (generator robi to automatycznie).
