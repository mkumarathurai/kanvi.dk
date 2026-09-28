# Administration af afstemninger

Arrangøren åbner administration fra deleskærmen eller afstemningen. Alle requests
kræver browserens adminadgang til netop denne poll. Adgangen kontrolleres både i
HTTP-laget og igen under mutationens transaktion, inklusive udløb og tilbagekaldelse.

## Handlinger

| Handling | Tilladte statusser | Resultat |
| --- | --- | --- |
| Tilføj dato | open | Ny aktiv mulighed; eksisterende deltagere har ikke svaret på den |
| Fjern dato | open | Soft delete; svarhistorik bevares, mindst to aktive datoer består |
| Vælg endelig dato | open | finalized, final_option_id og finalized_at sættes |
| Genåbn | finalized, closed | open, begge finaliseringsfelter ryddes; svar bevares |
| Luk | open, finalized | closed; eventuel endelig dato bevares |

Arkivering er endnu ikke eksponeret. Arkiverede polls kan ikke administreres her.
Fjernelse af en dato med svar kræver en eksplicit bekræftelse. Kontrollen sker
på serveren ved skrivning, så et nyt svar efter sidevisningen også udløser kravet.
En genoprettet dato får en ny identitet og arver ingen historiske svar.

## Samtidighed og integritet

Hver formular medsender `management_version`. `AdminPollMutation` låser poll-rækken,
kontrollerer adgang, version og status, udfører domænereglerne, øger versionen og
skriver audit i én transaktion. En forældet formular afvises med 409 for JSON og
en forklarende fejl på den opdaterede administrationsside for HTML.

Deltagersvar tager samme poll-lås. Dermed placeres et samtidigt svar enten før
finalisering/lukning, eller afvises efter overgangen. To fjernelser kan ikke
bryde minimumsgrænsen. SQLite anvender IMMEDIATE-transaktioner; integrationstestene
bruger uafhængige PHP-processer. Produktionsdatabasen er endnu ikke valgt/testet.

Et endeligt valg skal være en aktiv mulighed fra samme poll. Det valideres i
action-laget; en sammensat foreign key beskytter også samme-poll-reglen i databasen.
Minimum to aktive muligheder og soft-delete-regler håndhæves under låsning i
actions. Direkte SQL-skrivninger uden om actions er ikke en understøttet skrivevej.

Audit registrerer handling, admin-adgangens ID, før/efter-status, endeligt valg,
version samt relevante muligheds-ID'er og antal bevarede svar. Der lagres ingen
rå adgangstokens, deltagernavne eller individuelle svar i audit. Auditfejl ruller
hele mutationen tilbage. Der er endnu ingen auditvisning i UI'et.

## Offentlig visning

Foreløbigt implementeringsvalg til produktgennemgang: Den endelige dato er synlig
for alle med det offentlige link. Resultattotaler, navne og individuelle svar kræver
fortsat serverkontrolleret adminadgang eller et gemt svar på en aktiv mulighed.
Lukning uden endeligt valg viser lukket-status. Genåbning fjerner det endelige valg.

Resultater opdateres efter egne svar eller via “Opdatér”; der er ingen live push.
En opdatering, som opdager nye/fjernede muligheder, beder deltageren genindlæse.
