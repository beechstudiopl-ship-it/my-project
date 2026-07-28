=== Claude SEO — AI Visibility ===
Contributors: northtc
Tags: seo, ai, aeo, schema, json-ld, robots, gpt, claude, perplexity
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatyczne SEO pod AI (AEO): wpuszcza boty AI w robots.txt oraz wstrzykuje dane
strukturalne (firma, FAQ, kursy) do sekcji <head>. Konfiguracja w jednym miejscu.

== Description ==

Wtyczka "na automat" zwieksza widocznosc firmy w asystentach i wyszukiwarkach AI
(ChatGPT, Claude, Perplexity, Google AI Overviews). Trzy filary:

1. robots.txt — automatycznie dopisuje reguly "Allow" dla botow AI
   (GPTBot, ClaudeBot, PerplexityBot, Google-Extended, Applebot-Extended i inne).
2. Dane firmy — wstrzykuje schema Organization / EducationalOrganization / LocalBusiness
   (nazwa, adres, telefon, obszary dzialania, profile spolecznosciowe).
3. FAQ i kursy — buduje schema FAQPage z wpisow FAQ oraz Course na oznaczonych stronach.

== Instalacja ==

1. Skopiuj katalog "claude-seo-ai" do wp-content/plugins/ (lub spakuj do ZIP i wgraj
   przez Wtyczki > Dodaj nowa > Wyslij wtyczke na serwer).
2. Aktywuj wtyczke.
3. Wejdz w Ustawienia > Claude SEO (AI) i wypelnij dane firmy.
4. Dodaj pytania w menu "FAQ (AI SEO)".
5. (Opcjonalnie) Na stronach kursow zaznacz "Ta strona opisuje kurs" w metaboksie.

== Uwaga o robots.txt ==

Modul robots.txt dziala tylko, gdy WordPress generuje robots.txt dynamicznie
(czyli w katalogu glownym NIE ma statycznego pliku robots.txt). Jesli plik istnieje,
wtyczka wyswietli ostrzezenie — wtedy reguly botow AI dodaj recznie do tego pliku.

Pamietaj tez, ze blokada botow (np. bledy 403) czesto pochodzi z firewalla / WAF
(Cloudflare, Wordfence) — to trzeba odblokowac poza WordPressem.

== Changelog ==

= 1.0.0 =
* Pierwsza wersja: robots.txt (boty AI), schema firmy, FAQPage (CPT), Course (metabox).
