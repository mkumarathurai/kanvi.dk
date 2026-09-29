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
| Gmail desktop | Afventer testdestination og klientkontrol |
| Gmail mobil | Afventer testdestination og klientkontrol |
| Apple Mail på macOS | Afventer klientkontrol |
| Outlook (inkl. klassisk Windows) | Afventer testdestination og klientkontrol |
| iPhone Mail | Afventer testdestination og klientkontrol |

Browserrendering og automatiserede tests dokumenterer ikke kompatibilitet i de
fem mailklienter. Ved klientkontrol testes invitation, login og arrangøradgang
med billeder til/fra samt øvrige varianters tekst og knap. Kontrollér preheader,
logo, 16 px mobilmargin, læsbarhed, lange titler, CTA, footer og automatisk dark
mode. Brug testlinks uden adgang til virkelige afstemninger.

Tekniske referencer: [Laravels Mailable-API](https://api.laravel.com/docs/12.x/Illuminate/Mail/Mailable.html)
og [Microsoft om Outlooks Word-rendering](https://learn.microsoft.com/en-us/troubleshoot/outlook/user-interface/formatting-lost-when-editing-the-htmlbody-property).
