# zacznijangielski.pl: instalacja na cyber_Folks

Paczka zawiera stronę, działający formularz zapisu i panel do edycji treści pod adresem `/admin`. Wymaga PHP 8.1 lub nowszego (cyber_Folks ma go domyślnie). Nie potrzebuje bazy danych.

## 1. Wgranie plików

1. W panelu cyber_Folks (DirectAdmin) dodaj domenę `zacznijangielski.pl`, jeśli jeszcze jej nie ma.
2. Otwórz **Menedżer plików** albo połącz się przez FTP i wejdź do `domains/zacznijangielski.pl/public_html/`.
3. Usuń stąd domyślny plik `index.html` (strona powitalna hostingu).
4. Wgraj **zawartość** folderu `public_html` z paczki, czyli pliki i foldery `index.php`, `wyslij.php`, `admin/`, `app/`, `assets/`, `data/`, `uploads/`, `.htaccess`, `robots.txt` i `sitemap.xml`. Nie wgrywaj samego folderu `public_html` jako podfolderu.
   - Plik `.htaccess` jest ukryty. W menedżerze plików włącz pokazywanie ukrytych plików albo wgraj cały ZIP i rozpakuj go na serwerze.
5. Sprawdź, czy katalogi `data/` i `uploads/` mają prawo zapisu (zwykle `755`, na cyber_Folks działa od razu).

## 2. Konfiguracja: plik `app/config.php`

Otwórz go w menedżerze plików (Edytuj) i uzupełnij:

| Ustawienie | Co wpisać |
|---|---|
| `site_url` | `https://zacznijangielski.pl` (bez ukośnika na końcu) |
| `admins` | adresy e-mail osób, które mogą wejść do panelu, np. Twój i Doroty |
| `leads_to` | adres(y), na które mają przychodzić zgłoszenia z formularza |
| `mail_from` | adres nadawcy w tej domenie, np. `formularz@zacznijangielski.pl` |

**Ważne:** skrzynkę z `mail_from` załóż w panelu cyber_Folks (Poczta → Konta e-mail). Bez niej maile ze strony mogą trafiać do spamu albo w ogóle nie wychodzić.

## 3. Sprawdzenie

1. Wejdź na `https://zacznijangielski.pl` i wyślij testowe zgłoszenie z formularza. Powinno przyjść na adres z `leads_to`.
2. Wejdź na `https://zacznijangielski.pl/admin`, wpisz swój adres z listy `admins` i kliknij **Wyślij link do logowania**. Kliknij link z maila: działa 15 minut i tylko raz.
3. W panelu, w zakładce **Zgłoszenia**, powinno być widać testowe zgłoszenie. Zgłoszenia zapisują się tu zawsze, nawet gdyby mail nie doszedł; takie są oznaczone jako „bez maila”.

## 4. Logowanie przez Google (opcjonalne)

Bez tego kroku logowanie działa linkiem na e-mail, także na adres Gmail. Przycisk „Zaloguj przez Google” to wygodniejsza opcja.

1. Wejdź na <https://console.cloud.google.com/> i utwórz projekt, np. „zacznijangielski”.
2. **APIs & Services → OAuth consent screen**: typ **External**, nazwa aplikacji „zacznijangielski panel”, Twój e-mail jako kontakt. Zakresy zostaw domyślne. Na końcu opublikuj aplikację (**Publish app**) albo dodaj adresy z listy `admins` jako „Test users”.
3. **APIs & Services → Credentials → Create credentials → OAuth client ID**, typ **Web application**.
4. W polu **Authorized redirect URIs** wpisz dokładnie: `https://zacznijangielski.pl/admin/google.php`
5. Skopiuj **Client ID** i **Client secret** do `app/config.php` (`google_client_id`, `google_client_secret`).
6. Na stronie logowania pojawi się przycisk Google. Wpuszczone zostaną tylko konta z listy `admins`.

## 5. Korzystanie z panelu

- **Treść strony**: każda sekcja strony ma swoją kartę. Listy (tematy, dymki, punkty ceny, poziomy) mają przyciski dodawania, usuwania i zmiany kolejności (↑ ↓). Zdjęcia podmieniasz, wybierając plik (JPG/PNG/WEBP, do 3 MB); panel sam je zmniejsza.
- W tekstach działa `**pogrubienie**` i linki `[tekst](https://adres.pl)`.
- Puste pola (np. telefon albo adres) po prostu nie pokazują się na stronie.
- **Poprzednie wersje**: przed każdym zapisem panel zachowuje kopię treści (ostatnie 20) i można do niej wrócić jednym kliknięciem.
- **Zgłoszenia**: lista z formularza, eksport do Excela (CSV), usuwanie.

## 6. Aktualizacja plików w przyszłości

Przy wgrywaniu nowej wersji strony **nie nadpisuj** katalogów `data/` (treści, zgłoszenia, kopie) i `uploads/` (wgrane zdjęcia) ani pliku `app/config.php`.

## Bezpieczeństwo, w skrócie

- `app/` i `data/` są zablokowane z zewnątrz (`.htaccess`). W `uploads/` nie da się uruchomić żadnego skryptu.
- Formularz ma ukryte pole na boty, minimalny czas wypełnienia i limit 10 zgłoszeń na godzinę z jednego adresu IP.
- Panel: jednorazowe linki logowania, ochrona CSRF, sesja wygasa po 8 h.
