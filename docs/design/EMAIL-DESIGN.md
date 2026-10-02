# Kanvis fælles maildesign

Brugerens mailbrief er kilde til mailsystemet. Efterfølgende godkendt justering:
den primære knap bruger Kanvis mørke grønne #138448 med hvid tekst, så handlinger
er visuelt konsistente med platformen. Briefets neutrale baggrunde bevares.

## Designkontrakt

- Centreret hvidt kort, maks. 600 px, på #F7F7F5. Radius 16 px.
- 16 px ydre sidemargin. Indholdspadding 40 px på desktop og 24 px ved højst
  600 px viewport. Logo og indhold adskilles altid af 32 px.
- Endeligt v2-logo med bobler og spørgsmålstegn, transparent PNG, vist i 120 px.
- Systemfonte. Overskrift 28/25 px, vægt 700, linjehøjde 1,2, #20201E.
  Brødtekst 16 px / 1,6, #555550. Footer 13 px / 1,5, #858580.
- Præcis én primær handling: #138448 med hvid tekst, 16 px, mindst 48 px høj,
  10 px radius. Korte, handlingsorienterede tekster.
- Valgfri informationsboks: #F7F7F5, 12 px radius, 18 × 20 px padding.
- Kort tekst, individuel preheader, venlig hilsen og fælles diskret footer.
- Transaktionsmails har ingen afmeldingslinks. Marketing er ikke omfattet af
  disse mailklasser og skal have egen samtykke- og afmeldingshåndtering.

## Filer og brug

`app/Mail/KanviMail.php` er den fælles Mailable-base. Hver mailtype angiver sit
indhold med almindelige tekstfelter. `emails/message.blade.php` sammensætter
komponenterne og arver fra `emails/layouts/base.blade.php`. Alle har samme
ren-tekst-version i `emails/message-text.blade.php`.

Komponenter i `resources/views/components/email/`:

```blade
<x-email.logo />
<x-email.heading>Du er inviteret 🎉</x-email.heading>
<x-email.info-box>Sofies fødselsdag</x-email.info-box>
<x-email.button :href="$url">Se invitationen</x-email.button>
<x-email.footer :reason="$reason" />
```

| Mailklasse | Formål | Tilkobling |
| --- | --- | --- |
| `AdminRecoveryMail` | Arrangøradgang / bekræftelse af recovery-mail | Eksisterende `SendAdminRecovery`-job |
| `MagicLinkMail` | Loginlink | Klar til kommende loginflow |
| `VerifyEmailMail` | Mailverificering | Klar til kommende kontoflow |
| `WelcomeMail` | Velkomst | Klar til kommende kontoflow |
| `InvitationMail` | Invitation | Klar til invitationsflow |
| `ResponseConfirmationMail` | RSVP-/svarbekræftelse | Klar til flow med modtageradresse |
| `EventReminderMail` | Påmindelse om arrangement | Klar til påmindelsesflow |
| `PasswordResetMail` | Nulstilling af adgangskode | Klar til kommende kontoflow |
| `OrganizerMessageMail` | Transaktionsbesked til arrangør | Klar til konkrete systemhændelser |

De otte nye typer er implementerede, renderbare mailklasser, ikke nye login-,
konto- eller arrangementsfunktioner. Kanvis nuværende gæsteflow får ikke nye
obligatoriske mailfelter eller automatiske udsendelser af denne ændring.

Eksempel på integration, når invitationen og modtageren findes:

```php
Mail::to($recipient)->send(new InvitationMail(
    eventTitle: $eventTitle,
    url: $publicInvitationUrl,
    organizerName: $organizerName, // null giver neutral tekst uden opdigtet navn
    eventDetails: [$formattedDateAndTime, $location],
));
```

Handlingens URL skal være absolut HTTP(S); produktion bruger HTTPS. Kaldende
flow ejer autorisation, signering, udløb, modtagervalg og eventuel køhåndtering.
Udløbstekster skal afspejle tokenets faktiske udløb og angive tidszone ved klokkeslæt.
`ResponseConfirmationMail` må først sendes efter en gemt besvarelse. En påmindelse
må kun sendes til relevante deltagere med et fastlagt arrangement.

HTML-tekster escapes af Blade. Ren tekst bevarer navne, danske tegn og URL'ers
`&` uden HTML-entiteter. Arrangørmailens engangslink, udløb og adgangsadvarsel
bevares. Den eksisterende krypterede kø- og recovery-logik er uændret.

## Logo og drift

`public/images/email/kanvi-logo.png` er en 360 px bred PNG til visning i 120 px.
Originalens geometri, farver, Inter 750 og spørgsmålstegn er bevaret; kun ekstra
transparent margin er fjernet. SVG-kildefilen ændres ikke.

Genopbygning:

```sh
npm ci
node scripts/prepare-email-logo.mjs
```

`fontkit` omsætter den eksisterende variable Inter-font ved vægt 750 til konturer;
`resvg` rasteriserer. Begge er buildafhængigheder. PNG'en er med i repositoryet,
så mailafsendelse kræver hverken Node eller Imagick i produktion.

`APP_URL` skal være den kanoniske HTTPS-adresse i produktion; logoet indlæses fra
`APP_URL/images/email/kanvi-logo.png`. Brug en offentligt tilgængelig asset uden
login eller browserchallenge. Logoet har alt-teksten Kanvi ved blokerede billeder.
`MAIL_FROM_NAME` skal være Kanvi, og afsenderadresse og transport skal konfigureres
til domænet. Denne ændring aktiverer ikke maillevering eller en ny mailudbyder.

## Produktionsopsætning: Resend

Mathi valgte Resend den 30. september 2026. Afsendelse direkte fra serveren er
valgt fra, fordi recovery-mailen er arrangørens eneste vej tilbage og ikke må
ende i spam. `resend/resend-php` er en afhængighed af projektet, så transporten
kan bygges; mails bliver først sendt, når miljøet herunder er sat.

Mail er slået fra, indtil `KANVI_RECOVERY_MAILER` peger på en rigtig transport.
`RecoveryMail::enabled()` afviser `log` med vilje, så adgangsgivende mails aldrig
havner i en logfil.

### Det der skal sættes

I produktionsmiljøets `.env` (`DEPLOY_PATH/shared/.env` på serveren):

```
MAIL_MAILER=resend
RESEND_API_KEY=<nøglen fra Resend>
MAIL_FROM_ADDRESS=kanvi@kanvi.dk
MAIL_FROM_NAME=Kanvi
KANVI_RECOVERY_MAILER=resend
```

Nøglen hører hjemme i serverens miljøfil. Den må ikke i repositoryet, i Jira
eller i knowledge basen.

### DNS på kanvi.dk

Resend oplyser de præcise værdier, når domænet tilføjes i deres kontrolpanel.
Der skal tilføjes tre slags records: SPF, DKIM og DMARC. Uden dem bliver mailen
sorteret som spam hos præcis de modtagere, der har mest brug for at få den.

### Kontrol efter opsætning

1. Bekræft at domænet står som verificeret i Resend.
2. Registrer en mailadresse på en fiktiv afstemning i produktion.
3. Bekræft at mailen ankommer, at engangslinket giver arrangøradgang, og at det
   samme link afvises anden gang.
4. Kontroller at køarbejderen behandler jobbet, og at der ikke ligger fejlede jobs.

## Rendering og kontrol

Layoutet bruger præsentationstabeller, HTML-bredder, bgcolor og inline styles.
Media query reducerer padding og overskrift på mobil; grundlayoutet kan stadig
læses uden den. Outlook får en betinget 600 px wrapper, 96 DPI og knap-padding.
Afrundede hjørner kan blive firkantede i klassisk Outlook. Light mode er angivet;
der er ingen specialbygget dark mode. Mailklienter kan stadig ændre farver.

Generér lokale eksempler:

```sh
php artisan kanvi:preview-emails
```

Kommandoen skriver HTML, TXT, EML, logo, indeks og en responsiv sammenligning til
`storage/app/private/mail-previews/`. Den bruger altid en separat in-memory
transport, fiktive modtagere og `example.test`-handlingslinks. Den sender ingen
mails, læser ikke produktionsdata og opretter ingen offentlig previewroute.
HTML-previews bruger en lokal kopi af logoet. EML bruger den konfigurerede
`APP_URL` til logoet ligesom rigtige mails.

### Verificeret 29. september 2026

- 14 nye tests: samtlige mailtyper gennem mailtransport med HTML og tekst,
  én CTA, korrekte links, escaping, PNG, preheader og recovery-information.
- Hele PHP-suiten: 97 tests / 687 assertions.
- Safari-browserpreview af invitation ved 640, 390 og 320 px: læsbart indhold,
  korrekt PNG, mobilpadding og knap uden vandret overflow.
- Alle 10 varianter leveret til lokal Mailpit via SMTP på 127.0.0.1:1025.
  Mailpit bekræftede 10 modtagne mails og ingen SMTP-afvisninger. Lokal `.env`
  bruger SMTP, `KANVI_RECOVERY_MAILER=smtp` og `APP_URL=http://kanvi.dk.test`.
  Købaserede recovery-mails kræver en kørende `php artisan queue:work`.

### Mailklienter – udestående accepttest

| Klient | Status |
| --- | --- |
| Gmail desktop | Bestået 2026-10-02. Lys visning med billeder bestået 2026-10-01 (preheader, logo, titel, grøn CTA og footer korrekte). Billeder fra og mørkt tema kontrolleret 2026-10-02 sammen med Mathi mod recovery-mailen fra 30/9 (template uændret siden). Uden billeder: logoet falder tilbage til alt-teksten "Kanvi", titelboks, grøn CTA og footer intakte og læsbare. Mørkt tema: Gmail web omfarver ikke mailindholdet, så mailen renderer som i lys visning. Kontoindstillinger (billeder og tema) gendannet og efterprøvet efter testen |
| Gmail mobil (iOS-appen) | Bestået 2026-10-02 på Mathis iPhone mod recovery-mailen fra 30/9 (template uændret siden). Lys visning: logo, overskrift, titelboks, grøn CTA, 16 px-margin og footer korrekte, ingen vandret overflow. Mørkt tema: appen inverterer mailen (modsat Gmail web og Apple Mail) — tekst, bokse, CTA og footer inverterer pænt og er læsbare, men logoets mørkeblå ordmærke har lav kontrast på den mørke baggrund (se åbent punkt nedenfor). Billeder fra: dækkes af Gmail web-beviset efter Mathis beslutning 2026-10-02; samme konto-indstilling og motor. Tema gendannet efter testen |
| Apple Mail på macOS | Bestået 2026-10-02 sammen med Mathi mod recovery-mailen fra 30/9 (template uændret siden). Lys visning med billeder: logo, titelboks, grøn CTA og footer korrekte. Blokeret eksternt indhold: logoet falder tilbage til alt-teksten "Kanvi" i en pladsholderramme; resten intakt og læsbar. Mørk systemvisning: Apple Mail omfarver ikke mailindholdet, mailen beholder sit lyse kort. Preheaderen vises korrekt i listevisningen. Privatlivsindstillingen ("Beskyt mailaktivitet") gendannet efter testen |
| Outlook (inkl. klassisk Windows) | Afventer testdestination og klientkontrol |
| iPhone Mail | Afventer testdestination og klientkontrol |

Åbent punkt fra Gmail-app-testen 2026-10-02: i appens mørke tema har logoets
mørkeblå ordmærke lav kontrast mod den inverterede baggrund. Afsender kan ikke
slå inverteringen fra. Mulig afhjælpning: en logo-PNG med lys kant eller en
hvid plade bag logoet i skabelonen. Afventer Mathis beslutning; ingen ændring
er lavet.

Browserrendering og automatiserede tests dokumenterer ikke kompatibilitet i de
fem mailklienter. Ved klientkontrol testes invitation, login og arrangøradgang
med billeder til/fra samt øvrige varianters tekst og knap. Kontrollér preheader,
logo, 16 px mobilmargin, læsbarhed, lange titler, CTA, footer og automatisk dark
mode. Brug testlinks uden adgang til virkelige afstemninger.

Tekniske referencer: [Laravels Mailable-API](https://api.laravel.com/docs/12.x/Illuminate/Mail/Mailable.html)
og [Microsoft om Outlooks Word-rendering](https://learn.microsoft.com/en-us/troubleshoot/outlook/user-interface/formatting-lost-when-editing-the-htmlbody-property).
