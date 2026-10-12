<?php
// Opis edytowalnych pól. Panel buduje z tego formularz i według tego
// zapisuje dane (pola spoza schematu nie da się zmienić z panelu).
// Pole: [ścieżka, etykieta, typ, opcje]. Typy: text, textarea, url, image,
// bool, select, strlist, objlist.

return [
    ['id' => 'seo', 'title' => 'Google i nagłówek strony', 'hint' => 'Tak strona wygląda w wynikach Google i na karcie przeglądarki.', 'fields' => [
        ['meta.title', 'Tytuł strony', 'text'],
        ['meta.description', 'Opis w Google (ok. 150 znaków)', 'textarea'],
        ['brand.part1', 'Logo – część granatowa', 'text'],
        ['brand.part2', 'Logo – część pomarańczowa', 'text'],
        ['brand.nav_cta', 'Przycisk w menu', 'text'],
    ]],
    ['id' => 'hero', 'title' => 'Góra strony', 'hint' => 'Pierwszy ekran: nagłówek, opis i grafika z dymkami.', 'fields' => [
        ['hero.kicker', 'Etykieta nad nagłówkiem', 'text'],
        ['hero.title', 'Nagłówek', 'text'],
        ['hero.lead', 'Opis pod nagłówkiem', 'textarea'],
        ['hero.cta_primary', 'Przycisk główny', 'text'],
        ['hero.cta_secondary', 'Przycisk drugi', 'text'],
        ['hero.bubbles', 'Dymki rozmowy', 'objlist', ['add' => 'Dodaj dymek', 'sub' => [
            ['who', 'Kto mówi', 'text'],
            ['text', 'Tekst', 'text'],
            ['side', 'Kolor', 'select', ['options' => ['them' => 'Biały (rozmówca)', 'you' => 'Pomarańczowy (Ty)']]],
        ]]],
        ['hero.badges', 'Odznaki pod dymkami', 'objlist', ['add' => 'Dodaj odznakę', 'sub' => [
            ['value', 'Wartość (np. 399 zł)', 'text'],
            ['label', 'Podpis', 'text'],
        ]]],
    ]],
    ['id' => 'tematy', 'title' => 'Tematy zajęć', 'fields' => [
        ['topics.label', 'Etykieta sekcji', 'text'],
        ['topics.title', 'Tytuł sekcji', 'text'],
        ['topics.sub', 'Opis sekcji', 'textarea'],
        ['topics.items', 'Karty tematów', 'objlist', ['add' => 'Dodaj temat', 'sub' => [
            ['title', 'Tytuł', 'text'],
            ['text', 'Opis', 'textarea'],
        ]]],
    ]],
    ['id' => 'biznes', 'title' => 'Dla firm', 'fields' => [
        ['business.label', 'Etykieta sekcji', 'text'],
        ['business.title', 'Tytuł sekcji', 'text'],
        ['business.questions', 'Pytania', 'strlist', ['add' => 'Dodaj pytanie']],
        ['business.note', 'Tekst pod pytaniami', 'textarea'],
        ['business.quote', 'Cytat', 'textarea'],
        ['business.quote_note', 'Tekst pod cytatem', 'textarea'],
    ]],
    ['id' => 'cena', 'title' => 'Cena', 'fields' => [
        ['price.label', 'Etykieta sekcji', 'text'],
        ['price.title', 'Tytuł sekcji', 'text'],
        ['price.amount', 'Kwota (np. 399 zł)', 'text'],
        ['price.period', 'Okres (np. / miesiąc)', 'text'],
        ['price.per', 'Podpis pod ceną', 'text'],
        ['price.cta', 'Przycisk', 'text'],
        ['price.points', 'Punkty z ptaszkiem', 'strlist', ['add' => 'Dodaj punkt']],
    ]],
    ['id' => 'prowadzaca', 'title' => 'Prowadząca', 'fields' => [
        ['teacher.label', 'Etykieta sekcji', 'text'],
        ['teacher.name', 'Imię i nazwisko', 'text'],
        ['teacher.bio', 'Opis (bio)', 'textarea'],
        ['teacher.photo', 'Zdjęcie', 'image'],
    ]],
    ['id' => 'partnerzy', 'title' => 'Partnerzy (Praust Office + Leo)', 'fields' => [
        ['partners.enabled', 'Pokazuj blok partnerów', 'bool'],
        ['partners.label', 'Etykieta', 'text'],
        ['partners.title', 'Tytuł', 'text'],
        ['partners.text', 'Opis', 'textarea'],
        ['partners.items', 'Logotypy', 'objlist', ['add' => 'Dodaj partnera', 'sub' => [
            ['name', 'Nazwa (tekst alternatywny)', 'text'],
            ['url', 'Link do strony', 'url'],
            ['logo', 'Logo', 'image'],
        ]]],
    ]],
    ['id' => 'dojazd', 'title' => 'Dojazd i kontakt', 'hint' => 'Puste pola (np. telefon) nie pokazują się na stronie.', 'fields' => [
        ['location.label', 'Etykieta sekcji', 'text'],
        ['location.title', 'Tytuł sekcji', 'text'],
        ['location.place', 'Miejsce', 'text'],
        ['location.address', 'Adres (ulica, kod, miasto)', 'text'],
        ['location.schedule', 'Rytm zajęć', 'text'],
        ['location.phone', 'Telefon', 'text'],
        ['location.email', 'E-mail', 'text'],
        ['location.map_lat', 'Mapa: szerokość geogr. (np. 54.2620)', 'text'],
        ['location.map_lng', 'Mapa: długość geogr. (np. 18.6360)', 'text'],
        ['location.contact_name', 'Osoba kontaktowa', 'text'],
        ['location.contact_role', 'Podpis osoby', 'text'],
        ['location.contact_photo', 'Zdjęcie osoby', 'image'],
    ]],
    ['id' => 'test', 'title' => 'Test poziomu (wkrótce)', 'fields' => [
        ['test.enabled', 'Pokazuj sekcję', 'bool'],
        ['test.badge', 'Plakietka', 'text'],
        ['test.title', 'Tytuł', 'text'],
        ['test.sub', 'Opis', 'textarea'],
        ['test.meanwhile', 'Tekst „do tego czasu”', 'textarea'],
        ['test.cta', 'Przycisk', 'text'],
    ]],
    ['id' => 'zapis', 'title' => 'Formularz zapisu', 'fields' => [
        ['signup.label', 'Etykieta sekcji', 'text'],
        ['signup.title', 'Tytuł sekcji', 'text'],
        ['signup.sub', 'Opis sekcji', 'textarea'],
        ['signup.levels_label', 'Nagłówek nad poziomami', 'text'],
        ['signup.levels', 'Poziomy do wyboru', 'objlist', ['add' => 'Dodaj poziom', 'sub' => [
            ['name', 'Nazwa', 'text'],
            ['hint', 'Podpis (np. A1–A2)', 'text'],
        ]]],
        ['signup.message_placeholder', 'Przykład w polu wiadomości', 'text'],
        ['signup.consent', 'Treść zgody (RODO)', 'textarea'],
        ['signup.button', 'Przycisk wysyłki', 'text'],
        ['signup.note', 'Tekst pod przyciskiem', 'text'],
        ['signup.done_title', 'Podziękowanie – tytuł', 'text'],
        ['signup.done_text', 'Podziękowanie – tekst', 'textarea'],
    ]],
    ['id' => 'stopka', 'title' => 'Stopka', 'fields' => [
        ['footer.left', 'Lewa strona', 'textarea'],
        ['footer.right', 'Prawa strona', 'text'],
    ]],
];
