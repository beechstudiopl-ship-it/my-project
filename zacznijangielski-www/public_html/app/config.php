<?php
// Konfiguracja strony. To jedyny plik, który trzeba uzupełnić po wgraniu.
return [
    // Pełny adres strony, bez ukośnika na końcu.
    'site_url' => 'https://zacznijangielski.pl',

    // Adresy e-mail, które mogą logować się do panelu (/admin).
    // Ten sam adres działa przy logowaniu linkiem i przez Google.
    'admins' => [
        'biuro@praustoffice.pl',
    ],

    // Gdzie mają przychodzić zgłoszenia z formularza (można podać kilka).
    'leads_to' => [
        'biuro@praustoffice.pl',
    ],

    // Nadawca maili wysyłanych przez stronę. Musi to być adres w domenie
    // strony (serwer cyber_Folks może wysyłać tylko w jej imieniu), inaczej
    // maile wpadną do spamu. Odpowiedzi i tak trafiają do klienta (Reply-To).
    'mail_from' => 'formularz@zacznijangielski.pl',
    'mail_from_name' => 'zacznijangielski',

    // Logowanie przez Google (opcjonalne). Zostaw puste, żeby wyłączyć.
    // Jak zdobyć te dane: INSTRUKCJA.md, sekcja „Logowanie przez Google”.
    'google_client_id' => '',
    'google_client_secret' => '',
];
