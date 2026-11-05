# NavBar

Ciemny pasek nawigacji głównej: znak strony, maks. 5 linków, telefon i jedno CTA.

- Konsument podaje `brand` (zwykle `SiteMark`), `links` (`{label, href, active}`), opcjonalnie `phone` (renderowany jako `tel:` w kroju `data`) i `cta`.
- CTA domyślnie `amber` — „Report a fault”. Na stronie modernizacji (05) użyj `variant: 'primary'` z „Request a survey”.
- Zawsze ciemny, także w motywie jasnym strony. Wysokość `header-height`.
- Poniżej 720px linki i telefon chowają się za przyciskiem menu (menu mobilne dostarcza konsument).
- Numer w podglądzie to placeholder — wstaw prawdziwy.
