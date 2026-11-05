# VTG · Research przed budową strony grupy (01)

Data: 2026-11-05 · Zakres: konkurencja w Londynie, słowa kluczowe SEO, struktura strony grupy.
Źródło designu: design system „VTG” (pliki w `project/` obok tego dokumentu).

> Uwaga o metodzie: strony konkurencji nie dały się otworzyć z tej sesji (blokada sieci),
> więc wnioski o konkurencji pochodzą z wyników wyszukiwania i opisów stron, a nie z
> przeglądu ich kodu ani układu. Słowa kluczowe to klastry intencji, **bez wolumenów**.
> Przed finalnym wyborem fraz sprawdź je w Ahrefs, Semrush albo Google Keyword Planner (lokalizacja: Londyn).

---

## 1. Konkurencja w Londynie

### Kto jest na rynku

| Firma | Pozycjonowanie | Co komunikują | Wniosek dla VTG |
| --- | --- | --- | --- |
| **VM Elevators** (East London, od 2008) | Niezależna firma serwisowa, Londyn + Home Counties | 24/7 call-out, BS EN 81, CHAS Elite, SafeContractor, Constructionline, dużo case studies (szkoły, hotele, biura, projekty pod NDA), artykuły o przepisach dla FM | Najmocniejszy content i SEO w niszy. Wzór dla sekcji case studies i bloga o compliance. |
| **Murray Lift Group** (od 2005) | „Leading independent lift specialist”, South of England | Naprawy wszystkich marek, wymiana i overhaul door operatorów, uszczelnienia hydrauliki, blog („signs your lift needs repair”) | Lista podzespołów w ofercie, podobnie jak u VTG. |
| **Apex Lifts** (Bexleyheath, rodzinna) | „Independent and family-run, deliberately different” | Budynki zabytkowe Grade II, City of London, mieszkania komunalne. ISO 9001/14001/45001, LEIA, CHAS, SafeContractor, SMAS, London Living Wage. Case studies w PDF | Pełna ściana akredytacji, czyli standard, którego oczekują zamawiający B2B. |
| **Tower Lifts** | Naprawy, utrzymanie, modernizacje od 2006 | Setki stron lokalnych („Lift Repair London”, „Lift Engineer London”, „Lift Servicing London”), naprawy wszystkich marek, poradniki | Agresywne SEO lokalne na wielu stronach. Pokazuje, ile fraz jest do wzięcia. |
| **Dainton Lift Services** (od 1988) | Komercyjni klienci, Londyn, Kent, Essex, Surrey | Planned preventative maintenance + emergency call-outs | Klasyczny model kontraktu serwisowego. |
| **Stannah** (oddział Dartford) | Duża brytyjska firma rodzinna | Serwis wszystkich marek, 24/7 breakdown cover, może być main contractorem przy modernizacji | Duży gracz; VTG wygrywa specjalizacją, nie skalą. |
| **Fly Lifts, Deltron Lifts** | Lokalne firmy, Greater London / M25 | 24/7 emergency, „all London boroughs” | „24/7” to dziś standard, a nie wyróżnik. |
| **JH Elevators, Focus Lifts** | Podwykonawcy dla branży dźwigowej | Praca dla innych firm serwisowych | Bezpośrednia konkurencja dla oferty VTG skierowanej do firm serwisowych. |

Firmy od samych drzwi i części: **Shorts Lifts** (dostawca dla branży, operatory Langer/Laumann), **ILE** (operator GAL MONXT-OMNI pod refurbishment), **Easi-Lift Door Services**.

### Wzorce stron konkurencji

1. **24/7 i telefon w nagłówku** mają prawie wszyscy. VTG ma to już w `NavBar` (telefon `tel:` + amber „Report a fault”).
2. **Ściana akredytacji**: CHAS, SafeContractor, Constructionline, ISO, LEIA, SMAS. Zamawiający B2B (FM, managing agents, zamówienia publiczne) filtrują dostawców po SSIP/Constructionline. **Brak w design systemie**: trzeba dodać pas logotypów akredytacji (patrz §3).
3. **Case studies z typem budynku i lokalizacją** (szkoła, hotel butikowy, biuro w City, blok mieszkalny). VTG ma do tego `ProjectCard` z polem `location`.
4. **„All makes”**: naprawy wszystkich marek (Otis, Schindler, KONE, TK Elevator/Thyssen, Stannah). Warto to napisać wprost.
5. **Content o compliance**: LOLER, BS EN 81-20/50, BS EN 81-80, Building Safety Act. Dobrze pozycjonuje się na frazy informacyjne, których szukają FM.
6. **Strony lokalne** (dzielnice / boroughs): Tower Lifts i VM Elevators budują nimi zasięg.

### Gdzie VTG może się wyróżnić

- **Specjalizacja zamiast „robimy wszystko”**: cztery wyspecjalizowane spółki i osobne strony. Nikt w wynikach tak tego nie komunikuje. To główny argument.
- **Obsługa firm serwisowych (trade)**: dla firm, które mają kontrakt, ale brakuje im ludzi albo kompetencji (np. fault finding na sterowniku, drzwi). Rynek podwykonawców jest mały i ma słabe strony.
- **Prawdziwe zdjęcia z szybu**: konkurencja często używa stocków. Design system już na tym stoi (grupa Realizacje).

### Wymogi, o które zapytają klienci (do potwierdzenia u VTG)

- [ ] Członkostwo **LEIA** (wymaga OC/OZ, wykwalifikowanych inżynierów, kodeksu praktyk, sprawdzenia finansów)
- [ ] **SSIP**: CHAS / SafeContractor / SMAS · **Constructionline** (Silver/Gold)
- [ ] **ISO 9001 / 14001 / 45001**
- [ ] Kwalifikacje inżynierów: **NVQ Level 3** Lift Engineering, karty ECS/CSCS
- [ ] Ubezpieczenia: public liability, employer's liability, professional indemnity (kwoty)
- [ ] Czas reakcji na awarię (deklarowany SLA)
- [ ] Obsługiwane marki i typy (traction, MRL, hydraulic, platformy)

---

## 2. Słowa kluczowe SEO (Londyn, brytyjski angielski)

Zasada z design systemu: *lift*, nie *elevator* (z wyjątkiem fraz, które ludzie naprawdę wpisują, np. „elevator repair London”: tylko w meta i treści, nie w nagłówkach).

### Strona grupy (01): frazy ogólne i usługi „pełnego zakresu”

| Klaster | Przykładowe frazy | Intencja | Gdzie |
| --- | --- | --- | --- |
| Naprawy ogólne | lift repair London · lift repairs London · lift repair company London · lift engineers London | transakcyjna | Home (title/H1), Services |
| Awaria | emergency lift repair London · lift breakdown London · 24 hour lift engineer London · lift stuck call out | pilna | Report a fault (landing + formularz) |
| Wszystkie marki | Otis lift repair London · Schindler / KONE / Thyssen lift repair · all makes lift repairs | transakcyjna | Home (sekcja „All makes”), osobne podstrony później |
| Niezależny serwis | independent lift company London · independent lift maintenance | porównawcza | About |
| Dla firm serwisowych | lift subcontractor UK · subcontract lift engineers · lift repair for lift companies · specialist lift repair support | B2B | „For lift companies” |
| Typy budynków | commercial lift repair · residential block lift repair · office lift repair London | transakcyjna | Who we work with |

### Klastry przekazywane na strony 02–05 (strona grupy linkuje, nie konkuruje)

| Strona | Frazy |
| --- | --- |
| 02 Mechanical | lift motor repair · traction machine repair/overhaul · lift brake repair · lift gearbox repair · lift rope replacement · sheave replacement |
| 03 Doors | lift door repair London · lift door operator replacement · lift door not closing · landing door repair · car door repair · lift door refurbishment |
| 04 Electrical | lift fault finding · lift controller repair · lift inverter / drive repair · lift safety circuit fault · lift PCB repair · lift wiring |
| 05 Modernisation | lift modernisation London · lift refurbishment London · lift controller replacement · obsolete lift parts · lift upgrade BS EN 81-80 · lift car refurbishment |

Jedna fraza = jedna strona docelowa. Strona grupy celuje w „lift repair London”, a frazy specjalistyczne zostawia stronom 02–05, linkując do nich z kart `ServiceCard`.

### Frazy informacyjne (blog / „Guides”), na które szukają FM i managing agents

- LOLER lift inspection (passenger lifts co 6 mies., towarowe co 12 mies.; inspekcja ≠ konserwacja)
- lift maintenance regulations UK · building owner lift responsibilities
- BS EN 81-80 (SNEL) lift safety upgrade
- signs your lift needs repair · lift keeps breaking down
- lift modernisation vs replacement · cost of lift modernisation
- Building Safety Act lifts (budynki ≥18 m / 7 kondygnacji, rola Accountable Person). **Stan wejścia przepisów w życie sprawdź na gov.uk**, źródła są niespójne.

---

## 3. Struktura strony grupy (01)

### Mapa stron

```
/                         Home
/services/                Full-scope services (krótko, linkuje do 02–05)
/report-a-fault/          Zgłoszenie awarii (formularz + telefon)
/request-a-quote/         Wycena (formularz)
/projects/                Realizacje (case studies)
/projects/<slug>/         Pojedyncze case study
/for-lift-companies/      Oferta trade / podwykonawstwo
/who-we-work-with/        FM, managing agents, właściciele budynków
/about/                   O grupie, 4 spółki, akredytacje, zespół
/guides/                  Content o compliance (LOLER, BS EN 81-80, BSA)
/contact/
/careers/                 (w stopce, wg design systemu)
/privacy/ · /cookies/
```

Przy 5 linkach w `NavBar`: **Services · Projects · For lift companies · About · Contact** + telefon + amber CTA „Report a fault”.

### Home: sekcje i komponenty

| # | Sekcja | Komponenty z design systemu | Treść |
| --- | --- | --- | --- |
| 0 | Pasek grupy | `GroupBar` (01 current) | 5 stron portfolio |
| 1 | Nawigacja | `NavBar` + `SiteMark` (tylko logo) | telefon `tel:`, „Report a fault” |
| 2 | Hero | `Hero` `display-xl`, zdjęcie `szyb-prowadnice`, `FloorIndicator` 01 | H1 z „lift repair” + Londyn; primary „Request a quote”, secondary „Report a fault” |
| 3 | Cztery specjalizacje | `SectionHeading` + 4× `ServiceCard` (02–05) | Główna nawigacja portfolio, karty linkują do domen 02–05 |
| 4 | Jak pracujemy | 3× `FloorIndicator` (01 Diagnose → 02 Repair → 03 Test) | Proces, bez wymyślonych liczb |
| 5 | Dla kogo | `SectionHeading` + lista/karty | Lift service firms · FM · managing agents · building owners |
| 6 | All makes | **nowy komponent: pas marek (tekstowy)** | Otis, Schindler, KONE, TK Elevator, Stannah… (bez logotypów marek, tylko nazwy) |
| 7 | Realizacje | 3× `ProjectCard` (1 tall + 2) | Typ budynku · dzielnica |
| 8 | Akredytacje | **nowy komponent: pas akredytacji** | LEIA, CHAS, SafeContractor, Constructionline, ISO, po potwierdzeniu |
| 9 | Zgłoś awarię | `Field` ×≤5, `Button` amber | Adres budynku, kontakt, opis usterki |
| 10 | Stopka | `Footer` | 1. kolumna „Group websites” (5 stron) |

### Braki w design systemie do uzupełnienia przy budowie

- **Pas akredytacji / logotypów zaufania**: brak komponentu, a u konkurencji to standard.
- **Pas „All makes”**: najprościej tekstowy, krojem `data` albo `body-strong`.
- **Menu mobilne**: `NavBar` chowa linki poniżej 720px, ale menu dostarcza konsument.
- **Brak SVG logo i favicony**: README design systemu wymienia to jako brak.
- **Zdjęcia**: w systemie jest 5 zdjęć; potrzeba więcej realizacji do case studies.
- **Nazwy spółek 02–05 i domeny**: wciąż do potwierdzenia, a od nich zależą linki w `GroupBar`.

### Pytania do klienta przed pisaniem treści

1. Prawdziwe nazwy spółek i domeny stron 02–05.
2. Numer telefonu i godziny pogotowia (24/7 czy w godzinach pracy?).
3. Akredytacje i członkostwa (lista z §1).
4. Obsługiwane marki i typy dźwigów.
5. 3–6 realizacji: typ budynku, dzielnica, zakres prac, zgoda na publikację.
6. Czy obsługują firmy serwisowe jako podwykonawca (strona „For lift companies”)?
7. Zasięg: tylko Greater London czy też M25 / Home Counties?
8. Liczby (lata doświadczenia, liczba obsługiwanych dźwigów): tylko podane przez klienta.

---

## Źródła

- [VM Elevators: London lift services](https://vmelevators.com/locations/london-lift-services) · [compliance](https://vmelevators.com/codes-and-compliance/) · [UK lift maintenance regulations](https://vmelevators.com/lift-articles/lift-regulations-2016-for-passenger-lifts-and-escalators/) · [case study NDA London](https://vmelevators.com/casestudies/nda-london/)
- [Murray Lift Group](https://murrayliftgroup.co.uk/) · [lift repair service](https://murrayliftgroup.co.uk/services/lift-repair-service)
- [Apex Lifts: modernisation](https://www.apexlifts.com/services/lift-modernisation/) · [accreditations](https://www.apexlifts.com/our-people/hsqe/accreditations/)
- [Tower Lifts: lift repair company London](https://www.tower-lifts.co.uk/?p=12541) · [5 signs your lift needs repairs](https://www.tower-lifts.co.uk/5-signs-your-lift-needs-repairs/)
- [Stannah London & South East](https://www.stannahlifts.co.uk/locations/lift-services-london-and-south-east-dartford) · [refurbishment & modernisation](https://www.stannahlifts.co.uk/services/lift-refurbishment-and-modernisation) · [LOLER guide](https://www.stannahlifts.co.uk/news/loler-supplementary-lift-testing-thorough-lift-examination)
- [Dainton Lift Services](https://www.020.co.uk/london/124268/http://www.daintonliftservices.co.uk/) · [Apex (construction.co.uk)](https://www.construction.co.uk/c/404980/apex-lift-escalator-engineers) · [Deltron Lifts](https://deltronlifts.co.uk/importance-of-lift-maintenance-london/)
- [Shorts Lifts](https://www.shorts-lifts.co.uk/?p=1051) · [Elevator World: UK specialist business](https://elevatorworld.com/news/daily-news/u-k-lift-industry-experts-form-new-specialist-business/)
- [LEIA: in-service inspection of lifting equipment](https://www.leia.co.uk/wp-content/uploads/upload_image/file/Lift%20Owner%20News%20-%20In-service%20inspection%20of%20lifting%20equipment.pdf) · [Designing Buildings: LOLER](https://designingbuildings.co.uk/wiki/LOLER) · [Otis: LOLER inspections](https://blog.otis.com/en-gb/loler-inspections)
- [UK Standards: rectifying faults in lifts](https://ukstandards.org.uk/en/nos-finder/SEMEM347/rectifying-faults-in-lifts) · [fault diagnosis on lifts](https://ukstandards.org.uk/en/nos-finder/SEMEM344/carrying-out-fault-diagnosis-on-lifts)
- [Propertymark: Building Safety Act](https://www.propertymark.co.uk/resource/building-act-guidance-on-managing-high-rise-safety.html) · [Macfarlanes: BSA and managing agents](https://www.macfarlanes.com/insights/102l652/building-safety-act-management-obligations-and-managing-agents) · [Hillingdon briefing 2025](https://modgov.hillingdon.gov.uk/documents/s63257/Building%20Safety%20Briefing%20Note%20-%20Property%20Highways%20and%20Transport%20Select%20Committee%20-%20March%202025.pdf)
- [City of London: IMEM controller replacement contract](https://ukspending.com/contracts/34632) · [CHAS](https://www.chas.co.uk/?p=25708)
