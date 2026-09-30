# Kanvi

Find ud af det sammen. Konto-fri datoafstemninger bygget med Laravel 12,
PHP 8.4, Livewire 4 (inklusive Alpine) og Tailwind CSS 4.

Session handover: [technical snapshot](docs/SNAPSHOT.md). Start a new session
with [AGENTS.md](AGENTS.md), which links to the shared Knowledge Base overview.

## Produktgrundlag

[Product & Solution Specification v1.1 – Behavioral Contract](docs/kanvi-product-solution-spec-v1.1.md)
er arbejdsgrundlaget. De seks beslutninger er indarbejdet i de relevante
afsnit, med permissionsmatrix, acceptkriterier og særskilt markerede åbne spørgsmål.
Den oprindelige v1.0-fil i Downloads er ikke ændret.

[SEO- og indholdsstrategien](docs/seo-og-indholdsstrategi.md) samler positionering,
planlagte sider, artikelidéer og prioritering frem mod launch. De første ti artikler
er implementeret med en oversigt på `/guides` og links fra forsiden.
Se [publicering og redigering af artikler](docs/indhold/PUBLICERING.md).

## Implementeret

Forside → titel → multiselect-kalender → gennemgang → oprettelse → deling → deltagersvar → resultat → valg af endelig dato.

- Ingen konto eller arrangørnavn kræves.
- Datoer kan vælges på tværs af måneder og fjernes med ét tryk.
- `CreatePoll` validerer og opretter poll, muligheder og admin-adgang i én transaktion.
- Minimum to forskellige, gyldige datoer håndhæves i action-laget.
- Offentlige links og administrationslinks har uafhængige tilfældige IDs/tokens.
- Admin-token hashes med SHA-256; tokenet har 256 bits tilfældig entropi.
- Browseradgangen kontrolleres mod databaseposten på hver beskyttet visning,
  inklusive udløb, tilbagekaldelse og hvilken poll adgangen tilhører.
- Kopier link, native deling hvor understøttet og privat administrationslink.
- Kopier-knapper bruger Clipboard API med en synkron fallback på lokal HTTP.
  Hvis browseren afviser begge metoder, markeres linket til manuel kopiering.
- Offentlige polls har `noindex, nofollow`, `no-store` og `no-referrer`.

- Navn og Kan / Måske / Kan ikke uden konto, med redigering i samme browser.
- Deltagerflow med introduktion, svar, serverbekræftet kvittering og separat resultat.
  “Fortsæt” navigerer efter autosave; delvise svar er fortsat gyldige.
- Autosave med lokal og serverbekræftet tilstand, revisioner, backoff og manuel retry.
- Idempotent første svar og genforsøg; gamle mutationer kan ikke skrive et nyere svar over.
- Databaseconstraints håndhæver samme poll, gyldige svarværdier og ét svar pr. dato/deltager.
- Resultater låses op efter første gemte svar og viser totaler, ubesvarede svar,
  deltagernavne og ranking med delt førsteplads ved lighed.

- Arrangøren kan tilføje/fjerne datoer, vælge endelig dato, genåbne og lukke.
- Svar på fjernede datoer bevares som historik; fjernelse kræver bekræftelse,
  når datoen har svar. Mindst to aktive datoer bevares.
- Statusregler, versionskontrol, låsning og audit håndhæves i samme transaktion.
- Genåbning rydder det endelige valg og bevarer svarene.
- En databaseconstraint sikrer, at endelig dato tilhører samme poll.

- Soft Nordic-design med de leverede SVG-assets, tokens, lokal Inter og fælles komponenter.
- Verificeret mail-recovery med engangslinks, rate limits og krypterede køjobs.
- Open Graph-tags og PNG-preview med polltitel og brandmark, uden private svar.

Mail-flowet er implementeret og testet, men faktisk afsendelse kræver valg og
konfiguration af mailtjeneste. Afstemninger slettes tolv måneder efter sidste
aktivitet; se [ADR 0001](docs/adr/0001-poll-retention.md). Sletningen kræver, at
serveren kører Laravels scheduler.
[Selvhostet Umami](docs/analytics.md) er tilføjet til offentlige sider i produktion;
private flows spores ikke, og produktets funnel-events afventer implementation.

## Lokalt

```sh
composer install
cp .env.example .env
php artisan key:generate
# Opret filen database/database.sqlite, hvis den ikke allerede findes.
php artisan migrate
npm install
npm run build
php artisan serve
```

Åbn `http://127.0.0.1:8000`. Alternativt kan projektet køres via Laravel Herd.
`composer run dev` starter Laravel, kø, logvisning og Vite til løbende udvikling.

SQLite er lokal udviklings- og testdatabase. Valget mellem PostgreSQL og MySQL
for drift er endnu ikke truffet. Databaseconstraints og samtidighedstests skal
også verificeres på den valgte database, når de tilhørende slices implementeres.

## Verifikation

```sh
php artisan test
vendor/bin/pint --test
npm run build
npm test
```

Integrationstests dækker oprettelse fra Livewire, minimum/distinkte datoer,
ugyldige datoer, rollback, HTML-escaping, offentlig/admin-adskillelse,
adgang til den rigtige poll, udløb, tilbagekaldelse og slettede polls.
Svar-tests dækker desuden idempotens, forældede revisioner, flere faner,
resultatadgang, optælling, ranking og databaseconstraints. En integrationstest
kører syv samtidige PHP-processer mod en isoleret SQLite-database.
Administrationstests dækker hele permissionsmatrixen, forældede formularer,
bekræftelse, audit-rollback og konkurrerende fjernelser/finalisering/svar.
JavaScript-tests kontrollerer lokal tilstand, ACK, backoff og fejltilstande,
herunder lukning mens et svar afventer kvittering.

## Implementeringsvalg i første slice

- Titel: højst 140 tegn. Datoer: højst 60 muligheder. Dette er foreløbige
  inputgrænser, ikke nye vedtagne produktkrav.
- Kun `date` implementeres. Datoer lagres som kalenderdatoer uden tidskonvertering.
- Admin-linket veksles til browseradgang og omdirigerer til en URL uden token.
  Linkets tokenhash er den vedvarende adgangsnøgle i `poll_admin_access`.
  Browserens session indeholder adgangens ID og en krypteret kopi af tokenet,
  så arrangøren kan genfinde sit link. Det ligger aldrig i klartekst i sessionlageret.
- Laravels lagring af den seneste URL springer `/admin/*` over, så rå admin-tokens
  ikke lækker ind i databasebaserede sessioner via navigationshistorikken.
- Server-/proxylogs skal tilsvarende undlade admin-tokens ved driftsopsætning.
- Mail-recovery aktiveres kun med en eksplicit konfigureret leveringsmailer; log-mail er afvist.

## Deltageradgang og autosave

En krypteret HttpOnly-cookie pr. poll indeholder et tilfældigt edit-token.
Tokenets SHA-256-hash er identiteten i databasen; navne er ikke unikke.
Cookien oprettes, når den åbne afstemning vises, men deltageren oprettes først
sammen med første svar. Dermed kan tabte kvitteringer gentages uden dubletter.
Browserdata skal bevares for at redigere; der er ikke deltager-recovery i v1.
Cookien udløber sammen med afstemningens opbevaringsvindue på tolv måneder, så
redigeringsadgang ikke overlever de data, den giver adgang til.

Se [autosave-protokollen](docs/autosave.md) for felter, revisioner, adgang og fejl.
SQLite bruger `IMMEDIATE`-transaktioner med ventetid ved låsning. På MySQL/PostgreSQL
låses poll-rækken; svar, status- og datohandlinger tager samme lås.
Driftsdatabasen er fortsat ikke valgt eller testet i denne opsætning.

## Næste trin

Konfiguration af mailtjeneste og offentlig origin til delingspreview. Statussen
`archived` returnerer fortsat 404, men den er ikke en del af opbevaringspolitikken,
og ingenting sætter den.

Den endelige dato vises foreløbigt til alle med det offentlige link. Totaler og
individuelle svar kræver fortsat deltager- eller adminadgang. Det er et eksplicit
implementeringsvalg til produktgennemgang, ikke en ny godkendt produktbeslutning.
Se [administrationskontrakten](docs/administration.md).

Frameworkreferencer: [Laravel 12](https://laravel.com/docs/12.x/installation)
og [Livewire 4](https://livewire.laravel.com/docs/4.x/components).

## Design og recovery

Se [designsystemet](docs/design/DESIGN-SYSTEM.md), [implementeringsvalgene](docs/design/IMPLEMENTATION.md)
og [recovery og delingspreview](docs/recovery-and-sharing.md). PHP skal have GD med
FreeType. Preview-font og rasteriseret SVG-symbol er inkluderet; Imagick behøves
kun, hvis symbolets PNG skal genopbygges.
