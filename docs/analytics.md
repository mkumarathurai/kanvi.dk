# Umami

Kanvi bruger Mathis selvhostede Umami-installation:

- Script: `https://stats.mathi.dev/script.js`
- Website-ID: `fa2c9fe6-7537-4afb-835c-f47f75a9d546`
- Domæner: `kanvi.dk` og `www.kanvi.dk`

Scriptet indlæses med `defer` i det fælles sidelayouts `<head>`, når
`APP_ENV=production` og siden ikke er markeret privat. Lokale besøg og tests
indlæser ikke scriptet. Umamis domænefilter afgrænser desuden indsamlingen
til de to produktionsdomæner.

Forside, oprettelsesside og kommende offentlige indholdssider med samme layout
kan dermed måles. Afstemninger, resultater, deleskærme, administration og
recovery-sider indlæser ikke trackeren. Mailtemplates er separate og indeholder
ingen trackingkode. Browseren sender ingen manuelle events eller formularfelter.

Query-parametre og URL-fragmenter er udeladt med `data-exclude-search` og
`data-exclude-hash`. Scriptanmodningen bruger `referrerpolicy="no-referrer"`.
De eksisterende private routes har også `Referrer-Policy: no-referrer`, så
navigering tilbage til en offentlig side ikke medsender en privat URL som
browserreferrer.

Indstillingerne følger [Umamis tracker-konfiguration](https://docs.umami.is/docs/tracker-configuration).

Featuretests dækker aktivering på offentlige sider i produktion, fravær lokalt
og fravær på private sider. Faktisk modtagelse i Umami skal verificeres efter
deployment med et besøg på produktionsdomænet.

## Funnel-events

Serveren sender tre events til Umamis `/api/send`: `poll-created`,
`first-response` og `final-date-chosen`. De lægges i kø efter commit fra
`CreatePoll`, `SubmitResponse` (kun første deltager på en afstemning) og
`FinalizePoll`, og kun i produktion. Payloaden er fast: website-ID, hostname,
URL `/`, sprog og eventnavn. Intet afstemnings-ID, titel, navn, token eller
besøgendes IP forlader serveren. Beslutningen og begrundelsen står i
[ADR 0003](adr/0003-server-side-funnel-events.md).

Umami ignorerer anmodninger uden en browserlignende `User-Agent` og svarer
alligevel med succes, så modtagelsen kan kun bevises ved at se eventet i Umami
efter en gennemgang i produktion.
