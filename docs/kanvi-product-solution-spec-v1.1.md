# Kanvi.dk --- Product & Solution Specification

## v1.1 – Behavioral Contract

**Version:** 1.1\
**Dato:** September 2026\
**Status:** Produkt- og arkitekturgrundlag med eksplicit adfærdskontrakt

Denne revision indarbejder beslutninger om delvise besvarelser,
resultatadgang, autosave, arrangøradgang, statustilladelser og dataintegritet.
Den erstatter v1.0 som arbejdsgrundlag. Produktets scope er fortsat datoafstemninger.

Normative regler beskriver systemets løfter. Tekniske anbefalinger er
markeret særskilt og kan ændres, hvis løfterne stadig opfyldes.
Resterende afklaringer står i afsnit 38.

> **Produktløfte:** Find ud af det sammen.

------------------------------------------------------------------------

## 1. Vision

Kanvi skal være den nemmeste og mest indbydende måde for mennesker at
blive enige om, **hvornår noget skal ske**.

Produktet starter med datoafstemninger, men arkitekturen skal gøre det
muligt senere at besvare bredere spørgsmål som:

-   Hvor skal vi spise?
-   Hvad skal vi lave?
-   Hvem kan hjælpe?
-   Hvilken mulighed foretrækker vi?

Det må **ikke** gøre v1 mere kompleks.

### North Star

> En førstegangsbruger skal kunne gå fra `kanvi.dk` til et delt
> afstemningslink på **under 30 sekunder**.

### Produktprincipper

1.  Ingen konto kræves for at oprette den første afstemning.
2.  Ingen konto kræves for at svare.
3.  Mobil er den primære oplevelse.
4.  En skærm har én tydelig primær handling.
5.  Brug almindeligt dansk frem for softwaresprog.
6.  Autosave foretrækkes frem for Gem-knapper.
7.  Resultatet skal kunne forstås på få sekunder.
8.  Deling er en kernefunktion --- ikke en eftertanke.
9.  Vi bygger fleksibilitet i datamodellen, men ikke unødvendige
    features i UI.
10. Kanvi træffer ikke beslutningen for gruppen. Kanvi gør beslutningen
    tydelig.

### Hvad Kanvi ikke er i v1

Kanvi v1 er ikke:

-   en kalenderklient
-   Calendly
-   projektstyring
-   eventmanagement
-   en virksomhedsplatform
-   en social platform

Kalenderintegration, organisationer, betaling, AI og avancerede
workflows kommer kun senere, hvis reel brug viser behovet.

------------------------------------------------------------------------

# 2. Kanvi i ét diagram

``` mermaid
flowchart LR
    A["Jeg vil finde en dag"] --> B["Skriv hvad vi skal"]
    B --> C["Vælg mulige datoer"]
    C --> D["Opret afstemning"]
    D --> E["Del link"]
    E --> F["Venner / familie / hold"]
    F --> G["Kan / Måske / Kan ikke"]
    G --> H["Kanvi samler svar"]
    H --> I["Bedste muligheder bliver tydelige"]
    I --> J["Arrangøren vælger"]
    J --> K["Datoen er fundet ✓"]
```

Det er hele kerneproduktet.

Alt i v1 skal enten gøre dette flow hurtigere, tryggere eller mere
forståeligt.

------------------------------------------------------------------------

# 3. Målgrupper

Den vigtigste bruger er ikke en projektleder med kalenderintegrationer.

Det er en almindelig person, der skriver:

> Hvornår kan vi holde fødselsdag?

Typiske situationer:

  Situation    Spørgsmål
  ------------ -----------------------------------------
  Familie      Hvornår kan alle komme til fødselsdag?
  Venner       Hvornår skal vi spise sammen?
  Sport        Hvornår kan vi spille?
  Forening     Hvornår kan vi holde bestyrelsesmøde?
  Forældre     Hvornår kan vi holde klassearrangement?
  Naboer       Hvornår holder vi sommerfest?
  Lille team   Hvornår kan vi mødes?

------------------------------------------------------------------------

# 4. Den primære brugerrejse

``` mermaid
journey
    title Fra idé til fælles dato
    section Opret
      Åbner Kanvi: 5: Arrangør
      Skriver hvad gruppen skal: 5: Arrangør
      Vælger datoer: 5: Arrangør
      Opretter: 5: Arrangør
    section Del
      Kopierer eller deler link: 5: Arrangør
    section Svar
      Åbner link: 5: Deltager
      Skriver navn: 5: Deltager
      Vælger Kan/Måske/Kan ikke: 5: Deltager
    section Beslut
      Ser samlet resultat: 5: Arrangør
      Vælger dato: 5: Arrangør
```

Der bør ikke eksistere et unødvendigt trin mellem nogen af disse
handlinger.

------------------------------------------------------------------------

# 5. Opret-flow

## Trin 1 --- Hvad skal vi?

``` text
┌─────────────────────────────────┐
│ kan vi?                         │
│                                 │
│ Find en dag, der passer alle    │
│                                 │
│ Hvad skal vi finde en dag til?  │
│ ┌─────────────────────────────┐ │
│ │ Sommerfest med naboerne     │ │
│ └─────────────────────────────┘ │
│                                 │
│ [ Find nogle datoer → ]         │
│                                 │
│ Ingen konto nødvendig           │
└─────────────────────────────────┘
```

### UX

Ingen formular med:

-   titel
-   beskrivelse
-   lokation
-   e-mail
-   navn
-   kategori

Kun spørgsmålet.

Yderligere oplysninger kan tilføjes senere.

------------------------------------------------------------------------

## Trin 2 --- Hvornår kunne det være?

``` text
┌─────────────────────────────────┐
│ ← Sommerfest med naboerne       │
│                                 │
│ Hvornår kunne det være?         │
│ Vælg mindst to datoer           │
│                                 │
│          Oktober 2026           │
│ ma  ti  on  to  fr  lø  sø      │
│              1   2   3   4      │
│  5   6   7   8  [9] [10] 11    │
│ 12  13  14  15 [16] 17  18     │
│                                 │
│ Valgt                           │
│ ┌─────────────────────────────┐ │
│ │ Fre. 9. oktober          ×  │ │
│ │ Lør. 10. oktober         ×  │ │
│ │ Fre. 16. oktober         ×  │ │
│ └─────────────────────────────┘ │
│                                 │
│ [ Opret afstemning → ]          │
└─────────────────────────────────┘
```

### Regler

-   Minimum 2 muligheder.
-   Multi-select direkte i kalenderen.
-   Valgte datoer skal være synlige uden at brugeren skal huske dem.
-   Dato kan fjernes med ét tryk.
-   Tidspunkt er **ikke nødvendigt i første flow**.
-   Tid kan senere tilføjes som en udvidelse.

------------------------------------------------------------------------

# 6. Poll oprettes

Når brugeren trykker **Opret afstemning**, skal der ske meget lidt
visuelt --- men flere ting teknisk.

``` mermaid
sequenceDiagram
    actor A as Arrangør
    participant UI as Kanvi UI
    participant APP as Laravel
    participant DB as Database

    A->>UI: Opret afstemning
    UI->>APP: title + selected dates
    APP->>DB: Create Poll
    APP->>DB: Create PollOptions
    APP->>DB: Create AdminAccess token
    DB-->>APP: Poll created
    APP-->>UI: public URL + admin session
    UI-->>A: Del din afstemning
```

Brugeren skal ikke se denne kompleksitet.

------------------------------------------------------------------------

# 7. Deleskærmen

``` text
┌─────────────────────────────────┐
│ ✓ Din afstemning er klar        │
│                                 │
│ Sommerfest med naboerne         │
│ 3 mulige datoer                 │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ kanvi.dk/p/K7mQ2x          │ │
│ └─────────────────────────────┘ │
│                                 │
│ [ Kopier link ]                 │
│                                 │
│ [ Del… ]                        │
│                                 │
│ ─────────────────────────────── │
│                                 │
│ Denne browser kan administrere  │
│ afstemningen.                   │
│                                 │
│ Vil du være sikker på ikke at   │
│ miste adgangen? Send            │
│ administrationslinket til       │
│ din mail.                      │
│                                 │
│ [ Din mailadresse             ] │
│ [ Send administrationslink ]    │
│                                 │
│ Ikke nødvendigt                 │
└─────────────────────────────────┘
```

**Kopier link** er den primære handling.

E-mail er sekundær og må ikke stå mellem oprettelse og deling.

Browseradgang etableres som del af oprettelsen. Arrangøren skal også kunne
finde og gemme sit administrationslink; det må aldrig bruges som delingslink.

Forklaringen om tab af adgang skal være tilgængelig på deleskærmen:

> Hvis du sletter browserdata og hverken har gemt administrationslinket
> eller registreret en mailadresse, kan vi ikke genskabe din adgang.

------------------------------------------------------------------------

# 8. Deltagerflow

``` mermaid
flowchart TD
    A["Modtager Kanvi-link"] --> B["Åbner poll"]
    B --> C["Ser titel + datoer"]
    C --> D["Skriver navn"]
    D --> E["Svarer på datoerne"]
    E --> F["Autosave"]
    F --> G["✓ Gemt"]
    G --> H["Se resultat"]
    H --> I{"Vil personen ændre svar?"}
    I -- Ja --> E
    I -- Nej --> J["Færdig"]
```

Ingen:

-   konto
-   e-mail
-   password
-   onboarding
-   app-installation

------------------------------------------------------------------------

# 9. Deltagerens skærm

``` text
┌─────────────────────────────────┐
│ kan vi?                         │
│                                 │
│ Sommerfest med naboerne 🎉      │
│                                 │
│ Vælg hvad der passer dig        │
│                                 │
│ Dit navn                        │
│ [ Mathi                       ] │
│                                 │
│ Fre. 9. oktober                 │
│ [ Kan ] [ Måske ] [ Kan ikke ]  │
│                                 │
│ Lør. 10. oktober                │
│ [ Kan ] [ Måske ] [ Kan ikke ]  │
│                                 │
│ Fre. 16. oktober                │
│ [ Kan ] [ Måske ] [ Kan ikke ]  │
│                                 │
│             ✓ Gemt              │
└─────────────────────────────────┘
```

## Autosave: lokal tilstand og bekræftet tilstand

Hvert valg starter lagring umiddelbart. UI'et viser det lokale valg med det
samme, men skelner altid mellem:

- **Lokal tilstand:** Det navn og de svar, brugeren aktuelt har indtastet.
- **Bekræftet tilstand:** Den version, serveren har bekræftet som gemt.

En deltager tæller som en person, der har svaret, så snart navn og mindst
ét svar er gemt på serveren. Navn alene eller et lokalt, ubekræftet valg
udløser hverken deltageroptælling eller adgang til resultater.

### Tilstandsmaskine

``` mermaid
stateDiagram-v2
    [*] --> Idle
    Idle --> Saving: Gyldigt navn + første valg
    Saving --> Saving: Nyt lokalt valg
    Saving --> Saved: ACK bekræfter seneste lokale version
    Saving --> Error: Lagring fejler eller kan ikke bekræftes
    Error --> Saving: Automatisk eller manuelt genforsøg
    Saved --> Saving: Nyt lokalt valg eller navneændring
```

| State | UI | Betydning |
| --- | --- | --- |
| `idle` | Ingen gemt-kvittering | Ingen lagring er bekræftet endnu |
| `saving` | Gemmer… | Seneste lokale version afventer bekræftelse |
| `saved` | ✓ Gemt | Alle aktuelle lokale ændringer er serverbekræftede |
| `error` | Konkret fejl og mulighed for genforsøg | Seneste lokale version er ikke bekræftet |

“✓ Gemt” må kun vises efter server-ACK for den seneste lokale version.
Et gammelt ACK må ikke overskrive lokale valg, sænke den bekræftede version
eller få en nyere, ubekræftet ændring til at fremstå gemt. Det samme gælder
forsinkede fejl fra requests, som allerede er overhalet af en nyere bekræftelse.

### Rækkefølge, revisionsnumre og last-write-wins

Mutationer identificeres med client-side revisionsnumre/request IDs.
Serverens ACK identificerer den accepterede mutation/version.

Last-write-wins gælder det seneste lokale valg i samme redigeringsforløb,
ikke den request, der tilfældigvis ankommer sidst. Både server og klient
skal håndtere requests, der ankommer ude af rækkefølge:

1. Brugeren vælger `Kan → Måske → Kan` med stigende revisioner.
2. UI'et viser straks det sidste `Kan` og en afventende gemmestatus.
3. Et gammelt ACK kan ikke gøre den aktuelle version til `saved`.
4. En gammel skrivning eller retry kan ikke overskrive en nyere accepteret
   revision i databasen.
5. Når serveren har bekræftet sidste revision, vises “✓ Gemt”.

Revisionskontrol og skrivning skal ske atomisk. Et request ID alene
forhindrer ikke forældede skrivninger. Genforsøg skal være idempotente;
et tabt ACK må ikke medføre en ekstra deltager eller et ekstra svar.
Revisionsnumre giver aldrig adgang i sig selv; hver mutation autoriseres.

**Teknisk anbefaling:** Brug et identificeret redigeringsforløb og stigende
revisioner pr. redigeret svar samt særskilt revision for navneændringer.
Backend skal bevare accepteret revision og genkende gentagne mutationer.
Implementeringen bruger et tilfældigt editor-ID pr. sideindlæsning og
stigende revisioner pr. felt. Serveren registrerer den højeste accepterede
revision og en hash af dens værdi i samme transaktion som ændringen.

**Implementeringsvalg for flere faner:** Senest servergemte nye ændring
vinder. Et genforsøg med en allerede behandlet eller lavere revision skriver
ikke igen, heller ikke hvis en anden fane har ændret svaret siden. Kvitteringen
indeholder den aktuelle serverværdi. Hvis der ikke er et nyere lokalt valg,
viser UI'et denne værdi og oplyser, at et nyere svar fra en anden fane er hentet.
Kun ændrede felter sendes; redigering af én dato må ikke skrive andre datoer over.
Valget er standarden i denne implementering og kan ændres efter produktfeedback.

### Fejl og genforsøg

Ved netværksfejl bevarer UI'et det lokale valg og viser eksempelvis:

> Kunne ikke gemme · prøver igen…

Der forsøges automatisk igen med backoff, og brugeren kan vælge manuelt
at prøve igen. Genforsøg må ikke genintroducere et overhalet lokalt valg.
Teksten “prøver igen…” bruges kun, mens et genforsøg faktisk er planlagt
eller i gang. Valideringsfejl og afvist adgang kræver en konkret besked;
de skal ikke gentages uendeligt som netværksfejl.

Hvis afstemningen lukkes eller finaliseres under lagring, afviser serveren
skrivningen. UI'et forklarer, at ændringen ikke blev gemt, og viser den
aktuelle afstemningsstatus. Kun et bekræftet svar indgår i resultatet.

Der er ingen stor **Indsend svar**-knap. Manuel retry er en fejlhandling,
ikke et ekstra trin i det normale deltagerflow.

------------------------------------------------------------------------

# 10. Resultatet

Resultatet må ikke ligne et regneark.

Det vigtigste spørgsmål er:

> **Hvilken dag passer bedst?**

``` text
┌─────────────────────────────────┐
│ Sommerfest med naboerne         │
│                                 │
│ 15 har svaret                   │
│ 3 mangler at svare på alle datoer│
│                                 │
│ ★ Lør. 10. oktober              │
│                                 │
│ 12 kan                          │
│  2 måske                        │
│  1 kan ikke                     │
│                                 │
│ ███████████████████░            │
│                                 │
│ Fre. 9. oktober                 │
│ 10 kan · 3 måske · 2 kan ikke   │
│                                 │
│ Fre. 16. oktober                │
│ 8 kan · 4 måske · 3 kan ikke    │
│                                 │
│ [ Vælg endelig dato ]           │
└─────────────────────────────────┘
```

### Optælling og synlighed

- “Har svaret” tæller deltagere med et gemt navn og mindst ét servergemt
  svar på en aktiv dato. En deltager tælles kun én gang.
- En deltager er ufuldstændig, hvis mindst én aktiv dato er ubesvaret.
- Eksempel: “15 har svaret · 3 mangler at svare på alle datoer”. De 3 er
  en delmængde af de 15, ikke yderligere deltagere.
- Hver dato har separate antal for `can`, `maybe`, `cannot` og ubesvaret.
  Ubesvaret beregnes blandt de optalte deltagere og er aldrig `cannot`.
- Fjernede datoer indgår ikke i ranking eller aktiv besvarelsesgrad.
- Hvis en deltager kun havde svar på nu fjernede datoer, bevares deltageren
  og historikken, men personen tæller først igen efter svar på en aktiv dato.
- En ny dato gør eksisterende optalte deltagere ufuldstændige, indtil de
  svarer på den.
- V1 viser både totaler, deltagernavne og individuelle svar. Visning af
  individuelle svar må gerne være en sekundær detaljevisning; hovedresultatet
  skal fortsat kunne aflæses hurtigt.
- Der er ingen indstilling til skjulte individuelle svar i MVP.

### Adgang til resultater

`show_results_after_response = true` betyder, at deltagerens resultater
låses op efter første serverbekræftede svar med et gemt navn. Det kræver
ikke en komplet besvarelse. Arrangøren kan se resultater via admin-adgang.

Kontrollen skal håndhæves på serveren; resultatdata må ikke blot skjules
visuelt, mens de allerede sendes til en browser uden adgang. En returnerende
deltager genkendes via sin gyldige deltageradgang, ikke via navnet.

Statusmatrixens “Se resultat” beskriver, om status tillader læsning; det
ophæver ikke adgangskravet. Adgang for nye besøgende efter finalisering
eller lukning kræver den afklaring, der står i afsnit 38.

### Scoring

Kanvi vælger **ikke** datoen.

Systemet rangerer muligheder deterministisk:

1.  flest `can`
2.  derefter flest `maybe`
3.  derefter færrest `cannot`

Ved fuldstændigt tie vises begge som lige gode.

Arrangøren vælger den endelige dato.

------------------------------------------------------------------------

# 11. Poll lifecycle

``` mermaid
stateDiagram-v2
    [*] --> Open: Poll oprettet
    Open --> Open: Deltagere svarer
    Open --> Open: Arrangør ændrer muligheder
    Open --> Finalized: Endelig dato vælges
    Finalized --> Open: Genåbn
    Finalized --> Closed: Luk
    Closed --> Open: Genåbn
    Open --> Closed: Luk uden valg
    Closed --> Archived: Retention / arkivering
```

V1-status:

``` text
open
finalized
closed
archived
```

### Permissionsmatrix

Adminhandlinger kræver gyldig admin-adgang uanset status. Ændring af egne
svar kræver deltageradgang. Nye deltagere og alle svarmutationer kræver `open`.

| Handling | Open | Finalized | Closed | Archived |
| --- | :---: | :---: | :---: | :---: |
| Nye deltagere | ✓ | – | – | – |
| Ændre eksisterende svar | ✓ | – | – | – |
| Se resultat | ✓ | ✓ | ✓ | – / begrænset, se afsnit 38 |
| Tilføje dato | ✓ | – | – | – |
| Fjerne dato | ✓ | – | – | – |
| Vælge endelig dato | ✓ | – | – | – |
| Genåbne | – | ✓ | ✓ | – |
| Lukke | ✓ | ✓ | – | – |
| Arkivere | – | – | ✓ | – |

### Overgange

- `open → finalized`: Sæt en aktiv `final_option_id` fra samme poll og
  `finalized_at`. Svar og muligheder bliver skrivebeskyttede.
- `open → closed`: Luk uden endeligt valg.
- `finalized → closed`: Luk og bevar den valgte dato og tidspunktet for valget.
- `finalized → open` og `closed → open`: Ryd både `final_option_id` og
  `finalized_at`. Bevar deltagere og svar. Afstemningen har igen intet endeligt valg.
- `closed → archived`: Arkivér efter retention-politikken. Ingen genåbning i v1.

En genåbning kræver mindst to aktive muligheder. En åben afstemning kan
aldrig samtidig have en endelig dato. Overgange og skrivehandlinger skal
valideres atomisk, så parallelle requests ikke omgår statusreglerne.

------------------------------------------------------------------------

# 12. Domænemodel

Kerneobjektet er **Poll**, ikke Event.

Et event er resultatet af beslutningen. Kanvi eksisterer før
beslutningen.

``` mermaid
classDiagram
    class Poll {
        ULID id
        string public_id
        string type
        string title
        string status
        string timezone
        ULID final_option_id
    }

    class PollOption {
        ULID id
        ULID poll_id
        string kind
        date date_value
        int sort_order
    }

    class Participant {
        ULID id
        ULID poll_id
        string display_name
        string edit_token_hash
    }

    class Response {
        ULID id
        ULID participant_id
        ULID poll_option_id
        enum value
    }

    class AdminAccess {
        ULID id
        ULID poll_id
        string email
        string token_hash
    }

    Poll "1" --> "*" PollOption
    Poll "1" --> "*" Participant
    Poll "1" --> "*" AdminAccess
    Participant "1" --> "*" Response
    PollOption "1" --> "*" Response
```

------------------------------------------------------------------------

# 13. ER-model

``` mermaid
erDiagram
    POLLS ||--|{ POLL_OPTIONS : contains
    POLLS ||--o{ PARTICIPANTS : has
    POLLS ||--|{ POLL_ADMIN_ACCESS : controls
    PARTICIPANTS ||--o{ RESPONSES : submits
    POLL_OPTIONS ||--o{ RESPONSES : receives

    POLLS {
        ulid id PK
        string public_id UK
        string type
        string title
        string status
        string timezone
        string locale
        ulid final_option_id FK
        timestamp finalized_at
        timestamp closes_at
        timestamp created_at
        timestamp updated_at
    }

    POLL_OPTIONS {
        ulid id PK
        ulid poll_id FK
        string kind
        date date_value
        string label
        int sort_order
        timestamp deleted_at
    }

    PARTICIPANTS {
        ulid id PK
        ulid poll_id FK
        string display_name
        string edit_token_hash
        timestamp created_at
        timestamp updated_at
    }

    RESPONSES {
        ulid id PK
        ulid participant_id FK
        ulid poll_option_id FK
        string value
        timestamp created_at
        timestamp updated_at
    }

    POLL_ADMIN_ACCESS {
        ulid id PK
        ulid poll_id FK
        string email
        string token_hash
        timestamp last_used_at
        timestamp expires_at
        timestamp revoked_at
    }
```

------------------------------------------------------------------------

# 14. Databasen

## `polls`

  Felt                          Type                 Bemærkning
  ----------------------------- -------------------- --------------------------------
  id                            ULID                 Primær nøgle
  public_id                     string               Kort, tilfældigt offentligt ID
  type                          string               `date` i v1
  title                         string               Spørgsmålet/arrangementet
  description                   text nullable        Senere/valgfrit
  status                        string               open/finalized/closed/archived
  timezone                      string               Fx Europe/Copenhagen
  locale                        string               Fx da
  visibility                    string               private_link som default
  show_results_after_response   bool                 True i v1; efter første servergemte svar
  final_option_id               ULID nullable        Valgt mulighed
  closes_at                     timestamp nullable
  finalized_at                  timestamp nullable
  timestamps
  deleted_at                    timestamp nullable

## `poll_options`

  Felt         Type
  ------------ --------------------
  id           ULID
  poll_id      ULID FK
  kind         string
  date_value   date
  label        string nullable
  sort_order   integer
  timestamps
  deleted_at   timestamp nullable

## `participants`

  Felt              Type
  ----------------- --------------------
  id                ULID
  poll_id           ULID FK
  display_name      string
  edit_token_hash   string
  timestamps
  deleted_at        timestamp nullable

Navnet identificerer **ikke** personen. To deltagere må gerne hedde det
samme.

## `responses`

  Felt             Type
  ---------------- -------------
  id               ULID
  participant_id   ULID FK
  poll_option_id   ULID FK
  value            enum/string
  timestamps

Constraint:

``` text
UNIQUE(participant_id, poll_option_id)
```

`value`:

``` text
can
maybe
cannot
```

Manglende svar repræsenteres som ingen række og som `null` i visningen ---
ikke `cannot`. En eksisterende svarrække har altid en af de tre gyldige værdier.

Deltager og mulighed skal tilhøre samme poll. To uafhængige foreign keys
på deres IDs er ikke tilstrækkelige til at garantere denne regel. Den
konkrete databasebeskyttelse, herunder eventuelle ekstra nøgler/felter,
fastlægges med implementeringen og skal supplere domænevalideringen.

Datatabellerne her beskriver domænedata; de er ikke en fuldstændig migration.
Implementeringen skal også understøtte atomisk revisionskontrol og
idempotens for autosave samt audit af kritiske adminhandlinger.

## `poll_admin_access`

  Felt           Type
  -------------- --------------------
  id             ULID
  poll_id        ULID FK
  email          string nullable
  token_hash     string
  last_used_at   timestamp nullable
  expires_at     timestamp nullable
  revoked_at     timestamp nullable
  timestamps

Admin-token lagres **aldrig i klartekst**.

------------------------------------------------------------------------

# 15. Fremtidssikring uden overengineering

V1 understøtter:

``` text
Poll.type = date
PollOption.kind = date
```

Men modellen tillader senere:

``` text
date
date_time
text
place
choice
```

Eksempel:

``` mermaid
flowchart LR
    P["Poll"] --> T{"type"}
    T --> D["date"]
    T -. senere .-> DT["date_time"]
    T -. senere .-> PL["place"]
    T -. senere .-> C["choice"]
```

Vi implementerer **kun `date` nu**.

------------------------------------------------------------------------

# 16. Teknisk arkitektur

Anbefalet stack:

-   Laravel 12
-   PHP 8.4
-   Livewire
-   Alpine.js
-   Tailwind CSS v4
-   PostgreSQL eller MySQL
-   Redis valgfrit til queue/cache
-   almindelig Laravel queue til mail/jobs

Kanvi behøver ikke en separat SPA for at føles som en app.

``` mermaid
flowchart TB
    U["Browser / mobil"] --> LW["Laravel + Livewire"]
    LW --> APP["Application layer"]
    APP --> DOMAIN["Domain"]
    APP --> DB[("Database")]
    APP --> Q["Queue"]
    Q --> MAIL["Mail provider"]
    APP --> EVENTS["Domain events"]
    EVENTS --> ANALYTICS["Privacy-first analytics"]
```

### Lag

``` text
UI
↓
Application / Use Cases
↓
Domain
↓
Persistence / Infrastructure
```

Eksempler på use cases:

``` text
CreatePoll
AddPollOption
RemovePollOption
CreateParticipant
SubmitResponse
FinalizePoll
ReopenPoll
ClosePoll
SendAdminLink
```

Domain events:

``` text
PollCreated
ParticipantResponded
PollFinalized
```

------------------------------------------------------------------------

# 17. Routes

``` text
GET   /                         Landing page
GET   /opret                    Create flow

POST  /polls                    Create poll

GET   /p/{public_id}            Public poll
POST  /p/{public_id}/responses  Create/update response

GET   /admin/{token}            Admin access
POST  /admin/{token}/options    Add option
DELETE /admin/{token}/options/{id}
POST  /admin/{token}/finalize
POST  /admin/{token}/reopen
POST  /admin/{token}/close
```

Den konkrete Livewire-implementation kan reducere behovet for
eksplicitte POST-routes, men domænehandlingerne bør stadig eksistere som
selvstændige application actions.

------------------------------------------------------------------------

# 18. Public URL vs. admin adgang

Det er vigtigt, at de to ting aldrig blandes sammen.

``` mermaid
flowchart TD
    P["Poll"] --> PUBLIC["Public ID"]
    P --> ADMIN["Admin token"]

    PUBLIC --> URL1["kanvi.dk/p/K7mQ2x"]
    ADMIN --> URL2["kanvi.dk/admin/{secure-token}"]

    URL1 --> VOTE["Se + svar"]
    URL2 --> MANAGE["Rediger + afslut"]
```

Hvis nogen får det offentlige link, får de **ikke**
administrationsrettigheder.

------------------------------------------------------------------------

# 19. Guest-first identitet

Kanvi skal fungere uden konto.

``` mermaid
flowchart TD
    A["Ny arrangør"] --> B["Opret poll"]
    B --> C["Guest admin token"]
    C --> D{"Tilføjer mail?"}
    D -- Nej --> E["Fortsætter som guest"]
    D -- Ja --> F["Recovery via mail"]
    F -. senere .-> G["Kan knyttes til brugerprofil"]
```

`polls.user_id` må derfor ikke være påkrævet.

En senere konto skal kunne **overtage eksisterende polls**, ikke være en
forudsætning for dem.

### Tre veje til arrangøradgang

1. **Den aktuelle browser:** Oprettelsen etablerer admin-adgang med det samme.
2. **Administrationslink:** Besiddelse af et gyldigt admin-link giver adgang.
   Linket er en adgangshemmelighed og må ikke deles med deltagere som poll-link.
3. **Mail-recovery:** Arrangøren kan valgfrit registrere sin mail og få
   administrationslinket sendt dertil. Mailen er recovery, ikke et kontokrav.

Registrering eller ændring af recovery-mail kræver eksisterende admin-adgang.
En mailadresse indtastet på den offentlige poll giver ikke ejerskab.
Adgang via mail kræver besiddelse af linket, ikke blot kendskab til adressen.

Hvis browseradgangen er væk, administrationslinket ikke er gemt og ingen
recovery-mail er registreret, kan Kanvi ikke genskabe adgangen.

Tokens hashes i databasen. Autorisation kontrolleres på hver adminhandling,
og udløb eller tilbagekaldelse skal også håndhæves for etableret browseradgang.

------------------------------------------------------------------------

# 20. Deltageridentitet

Når en person svarer første gang:

``` mermaid
sequenceDiagram
    actor P as Deltager
    participant UI as Browser
    participant APP as Kanvi
    participant DB as Database

    P->>UI: Skriver "Mathi"
    P->>UI: Vælger Kan
    UI->>APP: Navn + svar
    APP->>DB: Create Participant
    APP->>DB: Store hashed edit token
    APP->>DB: Upsert Response
    APP-->>UI: Edit token/session
    UI-->>P: ✓ Gemt
```

Et browser-token gør det muligt at rette egne svar, mens afstemningen er `open`.
Første oprettelse af deltager og svar skal være atomisk og kunne gentages
uden at oprette dubletter ved retry. Deltageroptælling og resultatadgang
opdateres først efter serverbekræftelsen.

Vi bruger **ikke browser fingerprinting som identitet**.

------------------------------------------------------------------------

# 21. Ændring af muligheder

Alle ændringer af muligheder kræver admin-adgang og status `open`.

### Ny dato efter folk har svaret

Tilladt. Den nye dato indgår straks i resultatet som ubesvaret for
allerede optalte deltagere og i beregningen af ufuldstændige besvarelser.

Eksisterende deltagere får:

``` text
Ny dato → ubesvaret
```

### Fjern dato med svar

Ikke destruktivt delete. Fjernelse afvises, hvis der derefter ville være
færre end to aktive muligheder. Reglen gælder også ved samtidige fjernelser.
En bekræftelse af advarslen tilsidesætter ikke minimumskravet.

``` mermaid
flowchart TD
    A["Arrangør fjerner dato"] --> B{"Har datoen svar?"}
    B -- Nej --> C["Soft delete"]
    B -- Ja --> D["Vis advarsel"]
    D --> E{"Bekræft?"}
    E -- Nej --> F["Behold dato"]
    E -- Ja --> C
```

------------------------------------------------------------------------

# 22. Finalisering

Når arrangøren vælger en dato:

``` mermaid
sequenceDiagram
    actor A as Arrangør
    participant K as Kanvi
    participant DB as Database

    A->>K: Vælg lørdag 10. oktober
    K->>DB: Set final_option_id
    K->>DB: status = finalized
    K->>DB: finalized_at = now
    DB-->>K: OK
    K-->>A: Datoen er valgt ✓
```

Efter finalisering:

-   resultatet er stadig læsbart
-   både nye deltagere og ændring af eksisterende svar stoppes
-   datoer kan ikke tilføjes eller fjernes
-   arrangøren kan genåbne; det rydder `final_option_id` og `finalized_at`
-   genåbning bevarer deltagere og svar
-   senere kan deltagere notificeres

------------------------------------------------------------------------

# 23. Privacy og sikkerhed

## Princip

> Indsaml kun data, Kanvi faktisk har brug for.

### Krav

-   Uforudsigelige public IDs.
-   Admin-tokens hashes i databasen.
-   Edit-tokens hashes i databasen.
-   Rate limiting på poll creation.
-   Rate limiting på responses.
-   Rate limiting på mail.
-   Output escaping af titel og deltagernavne.
-   Ingen brugerleveret HTML.
-   `noindex` på polls som default.
-   Ingen browser fingerprinting som identitet.
-   Privacy-first analytics.
-   Retention-politik før offentlig launch.

### Search engines

``` text
Public poll
→ accessible by link
→ robots: noindex, nofollow
```

SEO kommer fra offentlige landingssider --- ikke brugernes polls.

------------------------------------------------------------------------

# 24. Deling = distribution

``` mermaid
flowchart LR
    A["Mathi opretter poll"] --> B["Deler til 12 personer"]
    B --> C["12 mennesker bruger Kanvi"]
    C --> D["Nogle husker Kanvi"]
    D --> E["En opretter sin egen poll"]
    E --> F["Ny gruppe møder Kanvi"]
```

Det er produktets naturlige viral loop.

Deleskærmen skal derfor være blandt de mest gennemarbejdede skærme i
hele produktet.

Prioritet:

1.  Kopier link
2.  Native Share på mobil
3.  SMS / WhatsApp / e-mail efter behov
4.  QR senere

------------------------------------------------------------------------

# 25. Open Graph

Et Kanvi-link skal se godt ud i Messenger, SMS-apps, Slack osv.

Eksempel:

``` text
┌────────────────────────────────────┐
│             kan vi?                │
│                                    │
│ Sommerfest med naboerne            │
│                                    │
│ Find en dag, der passer gruppen.   │
│                                    │
│                                    │
│ Svar på Kanvi →                    │
└────────────────────────────────────┘
```

Vi indsamler ikke arrangørens navn og må derfor ikke opfinde en afsender.
Previewet bruger titel, “Find en dag, der passer gruppen.” og “Svar på Kanvi →”.
Deltagernavne, individuelle svar og adgangstokens indgår aldrig i previewet.

Vi skal være varsomme med at vise private detaljer i previews.

------------------------------------------------------------------------

# 26. SEO

Polls:

``` text
NOINDEX
```

Marketing/use-case-sider:

``` text
/doodle-alternativ
/datoafstemning
/find-en-dato
/planlaeg-familiefest
/planlaeg-klassefest
/bestyrelsesmoede
/forening
```

Disse sider skal ikke bare være SEO-tekst.

De skal føre direkte til handling:

> Hvad skal I finde en dag til?

------------------------------------------------------------------------

# 27. MVP scope

## Med i v1

-   Landing page
-   Titel/spørgsmål
-   Multiselect datokalender
-   Guest poll creation
-   Public poll URL
-   Navn på deltager
-   Kan / Måske / Kan ikke
-   Autosave
-   Edit af egne svar
-   Resultatvisning
-   Ranking
-   Admin token
-   Tilføj/fjern dato
-   Finalize
-   Reopen
-   Close
-   Copy link
-   Native share
-   Open Graph
-   Mail recovery for arrangør
-   Rate limiting
-   noindex
-   basal analytics
-   audit af kritiske adminhandlinger

## Ikke i v1

-   Google Calendar
-   Apple Calendar sync
-   Outlook
-   abonnement
-   annoncer
-   teams
-   organisationer
-   recurring polls
-   AI
-   native apps
-   chat
-   kommentarer
-   avancerede temaer
-   andre poll-typer

------------------------------------------------------------------------

# 28. MVP-systemoversigt

``` mermaid
flowchart LR
    subgraph Public
        LAND["Landing"]
        CREATE["Create"]
        POLL["Poll"]
        RESULT["Result"]
    end

    subgraph Admin
        MANAGE["Manage poll"]
        FINAL["Finalize"]
    end

    subgraph Backend
        ACTIONS["Application Actions"]
        DOMAIN["Poll Domain"]
        DATABASE[("DB")]
        QUEUE["Queue"]
    end

    LAND --> CREATE
    CREATE --> ACTIONS
    POLL --> ACTIONS
    POLL --> RESULT
    MANAGE --> ACTIONS
    FINAL --> ACTIONS
    ACTIONS --> DOMAIN
    DOMAIN --> DATABASE
    ACTIONS --> QUEUE
```

------------------------------------------------------------------------

# 29. Edge cases

  Situation                        Adfærd
  -------------------------------- ---------------------------------------
  To deltagere hedder Mathi        Tilladt. Token/ID er identitet
  Samme browser vender tilbage     Egne svar kan redigeres
  Ny dato tilføjes                 Eksisterende deltagere står ubesvaret
  Dato med svar fjernes            Advarsel + soft delete
  To datoer står lige              Begge vises som lige gode
  Ingen dato passer alle           Bedste kompromis vises
  Admin mister link                Browseradgang eller mail-recovery; uden alle adgangsveje kan adgang ikke genskabes
  Public link deles bredt          Uforudsigeligt ID + rate limits
  Poll finaliseres                 Nye deltagere og alle svarændringer stoppes
  Arrangør fortryder               Genåbning bevarer svar og rydder endeligt valg
  Deltager springer en dato over   Ubesvaret, ikke "Kan ikke"

------------------------------------------------------------------------

# 30. Foreslået implementeringsrækkefølge

``` mermaid
flowchart TD
    T1["1. Tracer bullet<br/>Landing → datoer → public poll"]
    T2["2. Participant + responses"]
    T3["3. Autosave + edit"]
    T4["4. Results + ranking"]
    T5["5. Admin"]
    T6["6. Finalize / reopen"]
    T7["7. Sharing + OG"]
    T8["8. Mail recovery"]
    T9["9. Rate limiting + retention + operationel hardening"]
    T10["10. Analytics + SEO launch"]

    T1 --> T2 --> T3 --> T4 --> T5 --> T6 --> T7 --> T8 --> T9 --> T10
```

### Tværgående fundament fra første slice

Sikkerhed og integritet er ikke en afsluttende ticket. Hver slice skal
etablere og verificere de invariants, den berører:

- Public/admin-separation og uforudsigelige offentlige IDs.
- Hashing af admin-tokens samt autorisation af handlinger.
- Mindst to aktive muligheder og atomisk oprettelse.
- Hashing af edit-tokens, ejerskab og same-poll-regler, når deltagersvar introduceres.
- Unikke svar, idempotens og revisionskontrol sammen med autosave.
- Statusregler og atomiske overgange sammen med finalisering/genåbning.

Trin 5 bygger administrationsoplevelsen oven på eksisterende adgangskontrol.
Rate limiting, retention og operationel hardening kan færdiggøres senere,
men skal være på plads før offentlig lancering.

## Tracer bullet

Første vertikale slice skal kunne:

``` text
kanvi.dk
   ↓
Skriv titel
   ↓
Vælg datoer
   ↓
Opret
   ↓
Åbn public link
```

Ingen resultater, mail eller udbygget administrations-UI er nødvendig for
at bevise første slice. Browserens admin-adgang og de relevante
sikkerheds- og integritetsregler er allerede en del af fundamentet.

------------------------------------------------------------------------

# 31. Foreslået kodeorganisation

``` text
app/
├── Domain/
│   └── Polls/
│       ├── Models/
│       ├── Enums/
│       ├── Services/
│       └── Events/
│
├── Actions/
│   └── Polls/
│       ├── CreatePoll.php
│       ├── AddPollOption.php
│       ├── SubmitResponse.php
│       ├── FinalizePoll.php
│       └── ReopenPoll.php
│
├── Livewire/
│   ├── CreatePoll/
│   ├── Poll/
│   └── Admin/
│
└── Jobs/
    └── Polls/
```

Det er en retning, ikke et krav om enterprise-arkitektur.

Hvis en action kun består af fem klare linjer, skal vi ikke opfinde
abstraktioner for abstraktionernes skyld.

------------------------------------------------------------------------

# 32. Centrale invariants

Disse regler bør håndhæves i domænet --- ikke kun i UI.

``` text
Poll oprettes med mindst 2 aktive options.

En åben poll må aldrig have færre end 2 aktive options.

Response.value ∈ {can, maybe, cannot}

Participant må højst have ét response per option.

Response er kun gyldigt, hvis participant.poll_id === poll_option.poll_id.

Kun open tillader nye deltagere, svarændringer og ændringer af options.

final_option_id skal tilhøre samme poll og være en aktiv option.

open indebærer final_option_id = null og finalized_at = null.

finalized indebærer final_option_id != null og finalized_at != null.

Genåbning rydder final_option_id og finalized_at og bevarer svar.

Kun aktiv option kan finaliseres.

Public ID giver aldrig adminrettigheder.

Admin token og edit token må aldrig lagres i klartekst i databasen.

En forældet autosave-mutation må ikke overskrive en nyere accepteret revision.

“Gemt” kræver serverbekræftelse af den seneste lokale version.
```

Reglerne håndhæves i application/domain-laget og så langt ned i databasen
som praktisk muligt. Foreign keys, unique constraints og relevante checks
suppleres med transaktioner og låsning/atomiske betingelser for regler på
tværs af rækker. UI-validering alene opfylder ikke kontrakten.

------------------------------------------------------------------------

# 33. Analytics

Vi måler friktion og produktværdi.

### Funnel

``` mermaid
flowchart LR
    A["Landing"] --> B["Start create"]
    B --> C["Datoer valgt"]
    C --> D["Poll created"]
    D --> E["Link shared"]
    E --> F["Første participant"]
    F --> G["Flere responses"]
    G --> H["Poll finalized"]
```

Vigtige metrics:

-   median tid: landing → poll created
-   create completion rate
-   share rate
-   participants per poll
-   participant completion rate
-   polls med mindst 2 deltagere
-   finalize rate
-   tid til første svar
-   tid til endelig beslutning
-   participant → future creator conversion

North Star er ikke bare antal pageviews.

En stærkere kandidat er:

> **Antal polls hvor mindst to personer har svaret.**

Det repræsenterer faktisk fælles brug.

------------------------------------------------------------------------

# 34. Fremtidig retning

Hvis Kanvi får traction, kan produktet vokse uden at ændre sin grundidé.

``` mermaid
mindmap
  root((Kanvi))
    Hvornår
      Dato
      Tidspunkt
      Kalender
    Hvor
      Restaurant
      Sted
      Destination
    Hvad
      Aktivitet
      Valg
      Afstemning
    Hvem
      Kan hjælpe
      Kan køre
      Kan deltage
    Grupper
      Familie
      Hold
      Forening
      Team
```

Men dette er **ikke MVP-roadmap**.

Det viser blot, hvorfor vi ikke bør bygge datamodellen som `events` +
`event_dates`.

------------------------------------------------------------------------

# 35. Definition of Done --- v1

Kanvi v1 er klar, når:

> En person uden instruktion kan åbne Kanvi på sin telefon, beskrive
> hvad gruppen skal, vælge nogle datoer og dele et link på under 30
> sekunder.

Og:

> En modtager kan åbne linket, forstå opgaven, svare uden konto og se at
> svaret er gemt.

Og:

> Arrangøren kan forstå resultatet og vælge den endelige dato uden
> forklaring.

Hvis vi opnår det, har vi et produkt.

Alt andet kan komme bagefter.

------------------------------------------------------------------------

# 36. Produktets mentale model

Kanvi skal føles sådan:

``` text
          IKKE                           KANVI

   "Opret et event"              "Hvad skal vi?"

   "Konfigurer poll"             "Hvornår kunne det være?"

   "Inviter participants"        "Del linket"

   "Submit availability"         "Hvad passer dig?"

   "View poll analytics"         "Hvilken dag passer bedst?"

   "Finalize event date"         "Så er dagen fundet ✓"
```

Det er sandsynligvis den vigtigste UX-regel i hele projektet:

> **Kanvi skal tale som mennesker --- ikke som software.**


------------------------------------------------------------------------

# 37. Verificerbar adfærd for v1.1

Disse scenarier er acceptkriterier for de slices, der implementerer adfærden.

| Situation | Forventet adfærd |
| --- | --- |
| Navn er indtastet, men intet svar er gemt | Deltageren tæller ikke, og resultater er endnu låst |
| Navn og første svar er servergemt | Deltageren tæller én gang, og resultater låses op |
| En optalt deltager har kun svaret på én af tre datoer | De øvrige datoer er ubesvarede; deltageren tæller som ufuldstændig |
| En ny dato tilføjes | Eksisterende optalte deltagere mangler nu et svar; ingen får automatisk `cannot` |
| Hurtige valg giver ombyttet request- eller ACK-rækkefølge | Sidste lokale valg vinder inden for redigeringsforløbet; gammel trafik kan ikke overskrive server eller UI |
| Server gemmer, men ACK går tabt | Retry skaber ingen dubletter og kan bekræfte den allerede accepterede mutation |
| Netværket fejler | Lokalt valg bevares, ingen falsk “Gemt”, backoff og manuel retry er tilgængelige |
| Finalisering konkurrerer med en svarmutation | Skrivningen serialiseres før overgangen eller afvises; ingen svarændring accepteres efter finalisering |
| To requests forsøger at fjerne hver sin dato | Minimum to aktive muligheder bevares også ved samtidighed |
| Et svar kombinerer deltager og mulighed fra forskellige polls | Skrivningen afvises uden delvise ændringer |
| Offentligt link bruges til en adminhandling | Adgang afvises |
| Finalized eller closed genåbnes | Status bliver open, endeligt valg og tidspunkt ryddes, svar bevares |
| En fjernet eller fremmed mulighed forsøges finaliseret | Handlingen afvises |
| Adminadgang er udløbet eller tilbagekaldt | Adminhandlinger afvises også fra en tidligere autoriseret browser |

------------------------------------------------------------------------

# 38. Afklaringer: begge afgjort 30. september 2026

Punkterne herunder stod som åbne afklaringer, der ikke måtte få tilfældige
defaults i koden. Mathi har afgjort dem begge den 30. september 2026. De står
tilbage her med beslutningen, så det kan ses, hvad der var åbent, og hvad der
blev valgt.

1. **Resultatadgang efter finalisering/lukning: afklaret 30. september 2026.**
   Spørgsmålet var, om en besøgende, der aldrig nåede at svare, skal kunne se
   endelig dato, totaler eller individuelle svar, når afstemningen er lukket.
   Mathi har bekræftet den kørende adfærd: den valgte endelige dato er offentlig
   for alle med det offentlige link, også efter lukning, mens totaler,
   deltagernavne og individuelle svar fortsat kræver deltager- eller adminadgang.
   Lukning uden et endeligt valg viser kun lukket-status. Begrundelsen er, at et
   delt link skal kunne fortælle gruppen, hvornår det bliver, uden at afsløre hvem
   der svarede hvad. Se [ADR 0002](adr/0002-result-access-after-finalization.md).
2. **Arkivering og retention: afklaret 30. september 2026.** Mathi har besluttet,
   at en afstemning slettes tolv måneder efter sidste aktivitet sammen med
   deltagernavne, svar, revisioner, adminadgang, auditspor og recovery-links.
   Sletningen er endelig, og der er ikke et arkivtrin. Sidste aktivitet er
   oprettelse, et gemt svar, en arrangørhandling eller en recovery-anmodning.
   Deltagerens cookie udløber i samme vindue. Punktet er dermed ikke længere
   en åben afklaring. Se [ADR 0001](adr/0001-poll-retention.md).

### Præciseringer i denne revision til produktgennemgang

For at gøre reglerne indbyrdes sammenhængende er følgende fortolkninger
skrevet eksplicit i v1.1. De supplerer de seks vedtagne hovedbeslutninger:

- Deltageroptælling beregnes ud fra svar på aktive datoer; fjernet historik
  alene tæller ikke som en aktuel besvarelse.
- Lukning efter finalisering bevarer den valgte dato, men enhver genåbning
  rydder den, også når den foregående status var `closed`.
- Resultatadgang kræver serverkontrol; statusmatrixen alene giver ikke adgang.
