# Opsæt deployadgang til Kanvi

**Opdatering 30. september 2026:** Adgang og første installation er gennemført.
Automatisk deploy fra `main` er nu aktiveret, og kerneforløbet er kontrolleret
i produktion. Trinene nedenfor er den oprindelige opsætningsvejledning; de skal
ikke køres igen. Aktuel status findes i [deploydokumentationen](GITHUB-ACTIONS-DEPLOYMENT.md).

Denne vejledning opsætter en særskilt SSH-nøgle og GitHub-secrets til det
allerede forberedte workflow. Serverinstallationen og aktivering af deploy
venter fortsat. `kanvi` er allerede føjet til serverens SSH AllowUsers-liste.

Status 2026-09-30: Mathi har gennemført vejledningen. Login fra både Mac og
[GitHub Actions](https://github.com/mkumarathurai/kanvi.dk/actions/runs/36688253842)
er verificeret. `SSH_KNOWN_HOSTS` blev korrigeret under testen; deploy er fortsat
deaktiveret. Der er ingen grund til at oprette en ny nøgle eller gentage guiden.

Kør terminalkommandoerne på din Mac, ikke i sessionen med prompten
`mathi@silanthi`. Åbn en ny lokal terminal. Stop ved fejl, før du går videre.
Adgangskoder og private nøgler skal ikke sendes i chatten.

## 1. Opret en særskilt nøgle på din Mac

```bash
ssh-keygen -t ed25519 -C "github-actions-kanvi" -f "$HOME/.ssh/kanvi_github_actions" -N ""
```

Hvis filen allerede findes, skal du ikke overskrive den. Genbrug den eksisterende
Kanvi-deploynøgle, hvis det er den, du tidligere har lavet. Nøglen har ingen
passphrase, så Actions kan bruge den uden en interaktiv prompt.

Kopiér den offentlige nøgle:

```bash
pbcopy < "$HOME/.ssh/kanvi_github_actions.pub"
```

I CloudPanel: åbn **kanvi.dk → Settings → Site User Settings**. Kontrollér,
at brugeren er **kanvi**. Tilføj den kopierede linje under **SSH Keys**, og gem.
Bevar eventuelle eksisterende nøgler. Opret ikke en ekstra sitebruger.

## 2. Kontrollér login og placeringen

Kør på din Mac:

```bash
ssh -i "$HOME/.ssh/kanvi_github_actions" -o IdentitiesOnly=yes -o BatchMode=yes -l kanvi silanthi 'whoami; test -d /home/kanvi/htdocs/kanvi.dk && printf "Site-mappen findes\n"'
```

Forvent `kanvi` og `Site-mappen findes`. Hvis login fejler, er nøglen endnu ikke
godkendt korrekt. Hvis mappen ikke findes, skal siteplaceringen afklares i
CloudPanel, før `DEPLOY_PATH` sættes nedenfor.

## 3. Opret GitHub-miljøet

Åbn <https://github.com/mkumarathurai/kanvi.dk/settings/environments>.

Vælg **New environment**, indtast **production**, og opret miljøet. Under
**Deployment branches and tags** vælger du **Selected branches and tags** og
tilføjer en branch-regel for **main**. Hvis miljøindstillingerne ikke er
tilgængelige, skal GitHub-adgang/abonnement afklares før næste trin.

## 4. Gem secrets direkte fra din Mac

Kontrollér GitHub CLI-login:

```bash
gh auth status
```

Hvis du ikke er logget ind, kør `gh auth login`. Kør derefter:

```bash
gh secret set SSH_HOST --repo mkumarathurai/kanvi.dk --env production --body '95.217.229.228'
gh secret set SSH_PORT --repo mkumarathurai/kanvi.dk --env production --body '2222'
gh secret set SSH_USERNAME --repo mkumarathurai/kanvi.dk --env production --body 'kanvi'
gh secret set SSH_PRIVATE_KEY --repo mkumarathurai/kanvi.dk --env production < "$HOME/.ssh/kanvi_github_actions"
gh secret set SSH_KNOWN_HOSTS --repo mkumarathurai/kanvi.dk --env production --body '[95.217.229.228]:2222 ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIMEdoxoptFkBgpbWWOyhQIHcs2q9k5XBCudQw2gHx8N7'
```

Den private nøgle læses direkte fra filen og vises ikke i terminalen. Filen med
`.pub` hører til CloudPanel; filen uden `.pub` hører til `SSH_PRIVATE_KEY`.
Værtsnøglen i sidste kommando er offentlig og blev hentet fra serveren gennem
den eksisterende betroede SSH-forbindelse den 30. september 2026. Ved senere
serverflytning eller nøgleskift skal den verificeres og opdateres.

Secrets kan også oprettes manuelt under **production → Environment secrets →
Add secret** med de samme navne. De fem secrets skal høre til miljøet `production`.

## 5. Gem variabler, og kontrollér navnene

Når site-mappen er bekræftet i trin 2:

```bash
gh variable set DEPLOY_PATH --repo mkumarathurai/kanvi.dk --body '/home/kanvi/htdocs/kanvi.dk'
gh variable set DEPLOY_ENABLED --repo mkumarathurai/kanvi.dk --body 'false'
gh secret list --repo mkumarathurai/kanvi.dk --env production
gh variable list --repo mkumarathurai/kanvi.dk
```

Forvent fem secret-navne samt de to variabler. `secret list` viser ikke værdierne.
Send gerne resultatet af loginprøven og disse lister i chatten, så opsætningen
kan kontrolleres. Deploy skal stadig være `false`.

## Hvad mangler før første deploy?

Adgangsopsætningen installerer ikke Kanvi. Når installationen genoptages, skal
vi klargøre database og backup, produktionsmiljø, fælles storage, release-mappe,
CloudPanel-webrod og køarbejder. PHP-FPM skal også håndtere releaseskift korrekt;
CloudPanel anbefaler reload ved skift af `current`-symlink. Den konkrete
FPM-service og nødvendige rettigheder skal afklares før aktivering.

Derefter aktiveres deploy, første Actions-kørsel kontrolleres, og det faktiske
site testes. Detaljerne står i [deploydokumentationen](GITHUB-ACTIONS-DEPLOYMENT.md).

Kilder:
- [CloudPanel: Site User Settings og SSH Keys](https://www.cloudpanel.io/docs/v2/frontend-area/settings/)
- [GitHub: Environment secrets](https://docs.github.com/en/actions/how-tos/write-workflows/choose-what-workflows-do/use-secrets)
- [GitHub CLI: gh secret set](https://cli.github.com/manual/gh_secret_set)
- [CloudPanel: PHP-FPM ved releaseskift](https://www.cloudpanel.io/docs/v2/dploy/installation/)
