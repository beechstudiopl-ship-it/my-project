<?php
// Konfiguracja strony. To jedyny plik, który trzeba uzupełnić po wgraniu.
return [
    // Pełny adres strony, bez ukośnika na końcu.
    'site_url' => 'https://zacznijangielski.pl',

    // Adresy e-mail, które mogą logować się do panelu (/admin).
    // Ten sam adres działa przy logowaniu linkiem i przez Google.
    'admins' => [
        'wpisz-swoj-email@gmail.com',
    ],

    // Gdzie mają przychodzić zgłoszenia z formularza (można podać kilka).
    'leads_to' => [
        'wpisz-adres-na-zgloszenia@gmail.com',
    ],

    // Nadawca maili wysyłanych przez stronę. Musi to być adres w domenie
    // strony (załóż taką skrzynkę w panelu cyber_Folks), inaczej maile
    // mogą trafiać do spamu.
    'mail_from' => 'formularz@zacznijangielski.pl',
    'mail_from_name' => 'zacznijangielski',

    // Logowanie przez Google (opcjonalne). Zostaw puste, żeby wyłączyć.
    // Jak zdobyć te dane: INSTRUKCJA.md, sekcja „Logowanie przez Google”.
    'google_client_id' => '',
    'google_client_secret' => '',
];
