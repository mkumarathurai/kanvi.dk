# Mail-recovery og delingspreview

## Recovery

Arrangøren kan fra deleskærmen indtaste en mailadresse. Eksisterende gyldig
adminadgang til samme poll kræves. Adressen er først registreret efter besiddelse
og eksplicit bekræftelse af et mail-link. Offentlig indtastning giver aldrig ejerskab.

Implementeringsvalg: mail-linket har 256 bits entropi, hashes i databasen, udløber
efter 30 minutter og kan bruges én gang. Et GET veksler det til en krypteret
sessionsværdi og omdirigerer til en URL uden token. Først CSRF-beskyttet POST
forbruger det, så almindelige mailscannere ikke tager adgangen. En ny browser
får et nyt permanent admin-link og egen adminadgang; tidligere browseradgang
og kopierede admin-links bevares. Udløbstid kan aldrig forlænges ved recovery.

Nyeste registreringslink erstatter ældre ubrugte registreringslinks til samme poll.
En bekræftet ny adresse opdaterer alle aktive adminadgange til pollen. Den gamle
adresse beholdes indtil bekræftelse; derefter er gamle recovery-links til den
adresse ugyldige. Flere offentlige genforsøg invaliderer ikke hinanden.

Fra “Mistet arrangøradgangen?” på den offentlige poll kan en bekræftet adresse
anmode om et nyt link. Svaret er ens, uanset om adressen findes. Grænserne er
5 requests/minut/IP og 3/time/mailadresse (normaliseret og hashed i cache-nøglen).
Dette begrænser også gentagne registreringer. Arkiverede/slettede polls og
udløbet/tilbagekaldt adminadgang kan ikke bruges som recovery-grundlag.

Alle adgangsændringer og forbrug af link sker under poll-lås i en transaktion
med audit. Linket kontrolleres igen ved afsendelse og ved POST-bekræftelse.
Recovery opretter en separat adgang; tilbagekaldelse gælder den konkrete adgang,
ikke automatisk alle andre gyldige administrationslinks til afstemningen.

## Afsendelse

Mail sættes i kø efter commit med Laravels ShouldBeEncrypted. Rå token findes
hverken i recovery-tabellen, ukrypteret session eller køpayload. Transportfejl
redigeres til en generisk fejl, så adgangslinks ikke kopieres til fejllogs.
UI'et siger “lagt klar til afsendelse”; det er ikke en leveringskvittering.
Tre forsøg med backoff 30/120 sekunder bruges, mens linket stadig er gyldigt.

**Produktion sender gennem Resend.** Serverens miljø sætter
`KANVI_RECOVERY_MAILER=resend` og `RESEND_API_KEY`; nøglen findes kun dér. Den
30. september 2026 blev en rigtig recovery-mail leveret, og engangslinket gav
adgang én gang og blev afvist anden gang. Om mailen landede i indbakken eller i
spam, er ikke registreret. Resend opbevarer data i USA; det står på
privatlivssiden, og valget er bekræftet.

Lokalt er mail slået fra. Log-/array-/failover-mailere må ikke bruges til recovery
i drift, og `RecoveryMail::enabled()` afviser dem. Sæt APP_URL til den korrekte
HTTPS-origin: mail-links og social metadata bruger den frem for requestens
Host-header. Start en worker med `php artisan queue:work --tries=3`. Undlad rå
tokens i proxy-/adgangslogs for /admin/* og /adgang/link/*.

Recovery-links og mailadresser slettes sammen med afstemningen tolv måneder efter
sidste aktivitet; se [ADR 0001](adr/0001-poll-retention.md).

## Open Graph

Kun offentlige poll-sider har pollens Open Graph-tags. Titel escapes, og URL og
billede er absolutte canonical URLs. Der bruges ingen deltagernavne, individuelle
svar, mails eller hemmelige links. Billedet er en 1200×630 PNG med den leverede
brandmark, polltitel og den vedtagne tekst. Lange titler brydes og skaleres ned.
Metadata indeholder den fulde titel; PNG-fonten dækker Latin, herunder dansk,
men garanterer ikke emoji eller andre skriftsystemer.

Slettede og arkiverede polls giver 404. Poll og billede har noindex/no-store.
Eksterne tjenester kan alligevel cache tidligere previews. Messenger/Slack kan
først afprøves ende til ende, når en offentligt tilgængelig origin er sat op.

Referencer: [Laravel-køer](https://laravel.com/docs/12.x/queues#encrypted-jobs),
[Laravel-mail](https://laravel.com/docs/12.x/mail), [Open Graph](https://ogp.me/).
