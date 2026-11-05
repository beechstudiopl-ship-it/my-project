# Button

Zwykły prostokątny przycisk z zaokrągleniem `radius-md` — bez ścięć i skosów.

- `primary` (niebieski `up-fill`): główna akcja widoku — raz na ekran. `amber` (`down-fill`, tekst `on-down`): zgłoszenie usterki / pilna naprawa — raz na ekran. `secondary`: akcja poboczna. `ghost`: link z strzałką.
- Konsument podaje `children` (czasownik na początku, ≤ 3 słowa), opcjonalnie `href` (renderuje `<a>`), `icon` (zwykle `right`), `size` (`lg` w hero).
- Nigdy dwa `primary` obok siebie; obok `primary` stawiaj `secondary`.
