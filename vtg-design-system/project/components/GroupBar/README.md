# GroupBar

Cienki pasek nad nawigacją, łączący pięć stron portfolio VTG; bieżąca strona podkreślona kolorem `up`.

- Stawiaj na samej górze KAŻDEJ z pięciu stron, nad `NavBar`. Zawsze ciemny (`data-theme="dark"`).
- Konsument podaje `sites`: `{num, label, href, current}` dla wszystkich pięciu stron, w kolejności 01–05. Etykiety krótkie: „Group”, „Mechanical repairs”, „Door systems”, „Electrical fault finding”, „Modernisation”.
- Dokładnie jedna pozycja `current`. Linki prowadzą do osobnych domen — nie otwieraj ich w nowej karcie.
- Na mobile nazwa grupy znika, lista przewija się poziomo.
