# Designimplementation

Den leverede DESIGN-SYSTEM.md og design-tokens.json er kilden. Referencebillederne
opbevares uændret som visuel reference. De fem v2-SVG-assets ligger i public/brand.
Logoets opdaterede brandregler findes i BRAND.md.

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
- Forsidens afstemning er et statisk eksempel, eksplicit mærket “Et eksempel”.
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
