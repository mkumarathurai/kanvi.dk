# Deltagersvar og autosave

Implementerer adfærdskontrakten i specifikation v1.1. Der kræves ingen konto.
Den lokale UI-tilstand og den sidste serverbekræftede tilstand holdes adskilt.

## Identitet og første svar

`GET /p/{public_id}` udsteder ved behov en krypteret HttpOnly-cookie på pollens
URL-sti. Tokenet har 256 bits tilfældig entropi og er ikke tilgængeligt for JavaScript.
Der oprettes endnu ingen deltager. `SubmitResponse` finder identiteten via
`(poll_id, sha256(edit_token))` og opretter navn og første svar i én transaktion.
Tokenet fra cookien er nødvendigt ved enhver skrivning; et indsendt participant-ID
giver ingen adgang. To personer kan have samme navn.

## Mutation

`POST /p/{public_id}/responses` bruger Laravels CSRF-beskyttelse, sessioncookie og
deltagercookie. JSON-kald sender `Accept: application/json` og `X-CSRF-TOKEN`.

```json
{
  "editor_id": "32 hexadecimal characters, random per page load",
  "changes": [
    { "field": "name", "value": "Mathi", "revision": 1 },
    { "field": "poll option ULID", "value": "can", "revision": 2 }
  ]
}
```

Navn sendes ved første svar og ved senere navneændring. Øvrige felter sendes kun,
når de ændres. Fravær af et felt betyder ingen ændring; ubesvaret er ingen række.
Et svar kan ikke nulstilles til ubesvaret via denne version af UI'et.

Serveren låser poll-rækken, verificerer `open` samt aktive muligheder fra samme poll
og validerer hele batchen før commit. SQLite bruger en IMMEDIATE-transaktion,
fordi SQLite ikke har rækkelåsning via `SELECT ... FOR UPDATE`.

For hvert `(participant_id, editor_id, field_key)` lagres højeste revision og hash
af værdien. En højere revision accepteres. Lavere revisioner og identiske genforsøg
ændrer ingen data. Samme revision med en anden værdi afvises. Revisionen giver aldrig
autorisation. Alle skrivninger og revisionsregistreringer committes atomisk.

Svaret identificerer hver behandlet request-revision og giver den aktuelle
servertilstand for deltageren:

```json
{
  "acknowledged": [{ "field": "poll option ULID", "revision": 2 }],
  "participant": { "name": "Mathi", "answers": { "poll option ULID": "can" } }
}
```

`acknowledged` betyder behandlet, ikke nødvendigvis skrevet igen. Servertilstanden
er autoritativ, også når et genforsøg er overhalet af en anden fane.

## Klientens løfte

Valget vises straks. Klienten holder kun én netværksrequest aktiv og samler nyere
ændringer, mens den afventer svar. Netværkstimeout kan efterlade en request under
behandling på serveren; derfor er serverens revisionskontrol stadig nødvendig.

Et ACK rydder kun et lokalt ventende felt, hvis revisionen stadig matcher den
sendte revision. Nyere lokale ændringer bevares og sendes bagefter. “✓ Gemt” vises
først, når ingen lokale ændringer afventer, og servertilstanden er kendt.

Senest servergemte nye ændring vinder mellem faner. Allerede behandlede genforsøg
skriver aldrig igen. Klienten tager imod serverens nyere værdi, hvis feltet ikke
har en nyere lokal ændring, og viser besked ved en forskel. Andre faner opdateres
ikke live via push; genindlæsning eller næste gemmekvittering henter egen tilstand.

## Fejl

Netværksfejl, timeout, 408, 429 og 5xx genforsøges efter 1, 2, 4, 8, 16 og derefter
maksimalt 30 sekunder. Manuel retry er tilgængelig. Retry sender de nyeste ventende
felter med deres eksisterende revisioner. Lokale valg bevares ved alle fejl.

Validerings- og adgangsfejl gentages ikke automatisk. Lukket poll giver 409 og
skrivebeskytter UI'et. Der vises aldrig “Gemt” for den afviste ændring.
Hvis siden forlades med ugemte ændringer, anmodes browseren om at advare brugeren;
udkast er ikke garanteret bevaret efter reload eller tab af browserprocessen.

## Resultatadgang og optælling

`GET /p/{public_id}/results` autoriserer hver request via admin-adgang eller en
deltager med mindst ét gemt svar på en aktiv mulighed. Resultater sendes ikke
skjult i HTML til gæster uden adgang. Efter første gemte svar henter UI'et resultatet.
Resultater genindlæses efter egne gemte ændringer og med knappen “Opdatér”.

Kun aktive deltagere med aktive svar tælles. Ubesvarede datoer tælles særskilt.
Ranking følger flest `can`, flest `maybe`, færrest `cannot`; fuldstændigt lige
muligheder fremhæves begge. Ingen endelig dato vælges automatisk.

Resultatopdatering henter også status og endelig dato. Ændrede datomuligheder
udløser en besked om at genindlæse. Hvis afstemningen er lukket, stoppes retry,
og lokale ændringer uden ACK vises som ubekræftede. Et efterfølgende ACK kan
stadig bekræfte præcis den indsendte revision, hvis den blev gemt før lukningen.
Svar og administrationshandlinger tager samme poll-lås.
