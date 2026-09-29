# Designimplementation

Den leverede DESIGN-SYSTEM.md og design-tokens.json er kilden. Referencebillederne
opbevares uændret som visuel reference. De fem v2-SVG-assets ligger i public/brand.
Logoets opdaterede brandregler findes i BRAND.md.

Systemmails følger den særskilte mailbrief i [EMAIL-DESIGN.md](EMAIL-DESIGN.md).
De deler Blade-layout, komponenter og PNG-logo; dokumentet angiver også hvilke
mailflows der er tilkoblet, og hvilke klienttests der stadig mangler.

Mathis foretrukne projektsignatur bruges i den fælles sidefooter, centreret på
to linjer: “Made with ❤️” og “© [aktuelt år] Mathi Kumarathurai. All rights
reserved.” Årstallet beregnes automatisk.

- Inter indlæses lokalt fra @fontsource-variable/inter via Vite. Ingen eksterne
  fontkald på deltagersider med adgangscookies.
- Caveat indlæses også lokalt, kun til de korte håndskrevne noter på forsiden.
  Kalenderillustrationen er en dekorativ SVG, som bruger de samme farvetokens.
- Den medfølgende CSS-tokenfil bruges direkte; Tailwind-navne og komponenter
  henviser til den. Maksimal sidebredde er 1180 px og fokuseret indhold 760 px.
  Kalender og valgte datoer står ved siden af hinanden på desktop og stables
  på mobil inden for den fokuserede indholdsbredde.
- Knapper, inputs, logo, kort, svarvalg, autosave og resultatbjælker deler Blade-
  komponenter. Øvrige formularer bruger de samme CSS-primitiver.
- Logo-SVG indsættes fra den betroede assetfil, så ordmærket bruger sidens Inter.
  Symbolet er også favicon. V2-filerne er kopieret uændret fra logo-pakken.
  Logoets wrapper skjuler kun den ekstra transparente højremargin, så symbol
  og tekst bevarer deres hidtidige visuelle størrelse. Geometri, proportioner,
  farveforhold og det nye spørgsmålstegn er bevaret.
- Hvid tekst på den rene brandgrønne #16A34A opfylder ikke 4,5:1 ved normal
  tekststørrelse. Primær handling bruger derfor 75% grøn + 25% navy, cirka
  #138448 (4,74:1 mod hvid). Det er en funktionel kontrastnuance af tokens,
  ikke en ny brandfarve. Fejltekst bruger tilsvarende rød blandet med navy.
- Svar har semantiske baggrunde, symboler og labels. Valg markeres desuden med
  navy kant, aria-pressed og “Valgt”. Ubesvaret har neutral tekst, ingen valgt knap.
- Forsidens hero-afstemning er et statisk eksempel, eksplicit mærket “Et eksempel”.
  De fiktive navne er ikke deltagerdata. Ingen kontologin, priser, deadlines,
  avataruploads eller kommentarfunktioner fra konceptbillederne er indført.
- Mobil bruger én kolonne. Kalenderen udnytter hele bredden på de mindste
  skærme; kontrolhøjder er mindst 44 px. Reduced motion fjerner transitions.
- Oprettelsen viser valgte datoer til gennemgang lige før “Opret afstemning”.
  Der tilføjes ikke en indstillingsside eller ekstra submit-knap til svarflowet.
- `/opret` har en særskilt titelskærm og fælles trinindikator: Titel → Datoer →
  Overblik → Deling. En titel fra forsiden fortsætter direkte til datoerne.
- Deltagerflowet har introduktion → svar → gemt-bekræftelse → resultat.
  “Fortsæt” navigerer og sender ikke svar. Den åbnes kun efter ACK af seneste
  lokale version; delvise svar er stadig gyldige. “Se resultatet” åbnes efter
  første gemte svar. Tilbage til svar bevarer lokale valg, og ugemte ændringer
  vises også på resultatskærmen. Genbesøg starter på egne svar; lukkede polls
  starter på resultat/status med den eksisterende adgangskontrol.
- Resultatet viser både rangordnede datoer og en vandret scrollbar tabel med
  deltagernes individuelle svar. Begge bruger samme adgangskontrol. Initialer
  markerer navne; der indføres ikke profilbilleder eller en uploadfunktion.
- PNG-previewet læser farver fra design-tokens.json. Inter og den rasteriserede
  SVG-mark ligger i resources, så produktion kun behøver GD til rendering.

## Genopbygning af preview-assets

```sh
node scripts/prepare-preview-font.mjs
php scripts/prepare-preview-mark.php
```

Den første kommando konverterer den medfølgende Inter WOFF2 til TTF og kopierer
SIL OFL-licensen. Den anden rasteriserer originalsymbolet uden geometriændringer
og kræver Imagick ved genopbygning. De afledte assets er med i projektet.


## Forsidens øvrige sektioner

Reference: `kanvi-home-full-reference.png` (leveret 29. september 2026).
Den skrevne designspec og logo v2 er fortsat source of truth.

- Seks anledningskort åbner `/opret` med en redigerbar titel. Der oprettes
  først en afstemning, når brugeren vælger datoer og bekræfter oprettelsen.
- Delingssektionen forklarer linkdeling. Kanalikonerne er illustrationer;
  den rigtige deling sker efter oprettelse.
- Prøveafstemningen har isolerede lokale valg, fem fiktive deltagere og
  et resultat, der opdateres ved ændringer. Den bruger ingen lagring eller API.
  Ubesvarede datoer bliver ikke talt som “Kan ikke”.
- FAQ bruger native `details` og server-renderede svar. Den afsluttende CTA
  går til oprettelsen. Den personlige footer-signatur er bevaret.
- `home-sections.css` bruger design tokens. Mobil viser anledninger i to
  kolonner og de øvrige sektioner i én kolonne. Prøvevalg har mindst 44 px højde,
  tastaturfokus, visuel markering og `aria-pressed`.
- `public/images/home/organizer.png` er en genereret dekorativ illustration
  med transparent baggrund, lazy loading og eksplicitte dimensioner.
  Anledningsillustrationerne er små inline SVG'er i brandpaletten.
- Verifikation: 103 PHP-tests / 727 assertions, 25 JavaScript-tests, Vite-build
  og Pint. Safari er brugt til desktop samt separate viewports på 390 og 320 px.

### Afstand mellem sektioner

Forsiden bruger `--home-section-spacing`: 112 px fra 700 px skærmbredde,
80 px på mobil. Samme afstand bruges før trinoversigten og mellem de øvrige
hovedsektioner. Indvendig padding i farvede sektioner er separat fra afstanden.
“Prøv selv” har en diskret mint/cream-baggrund med afrundede hjørner, uden at
ændre indholdets kolonner eller vandrette placering.
