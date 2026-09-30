# Publicering af artikler

De ti leverede artikler er implementeret på det lokale site den 29. september
2026. Ændringen er ikke i sig selv en deployment til produktion.

Forsiden linker til alle ti sider. `/guides` samler dem, mens `/til` samler de syv
situationssider. Navigation og footer linker til oversigterne.

## Redigering

- `docs/indhold/*.md` bevarer de oprindelige oplæg, SEO-noter og billedbriefs.
- `resources/content/articles/*.md` indeholder den offentlige artikeltekst.
- `config/articles.php` definerer URL, titel, SEO-title, description og kategori.
- `app/Content/Articles.php` renderer Markdown på serveren med overskriftsankre
  og knapper på selvstændige oprettelseslinks.

Hovedoverskriften kommer fra konfigurationen. Brug H2 og H3 i artikelteksten.
Et selvstændigt Markdown-link til `/opret` bliver til en primær knap.

## Redaktionelle tilpasninger

Den offentlige tekst lover kun funktioner, der findes i produktet:

- Klokkeslæt aftales i gruppen; der er endnu ikke særskilte tidsmuligheder.
- Hele weekender kræver svar på de relevante enkeltdage; der er ingen datointervaller.
- Svarfrister kommunikeres i beskeden til gruppen; afstemningen lukker ikke automatisk.
- En neutral titel beskytter ikke adgang. Alle med det offentlige link kan åbne
  afstemningen, og linkpreview kan vise titlen.
- FAQ-links peger på `/faq`, som blev udgivet 30. september 2026. Forsidens korte
  FAQ er bevaret og linker videre til den fulde side.

Doodles produktoversigt er linket som kilde i sammenligningsartiklen.

## SEO og billeder

Siderne har én H1, individuelle metadata, canonical URL, Open Graph-tekst,
`WebPage`- og `BreadcrumbList`-data samt server-renderet hovedindhold.
`/sitemap.xml` indeholder forsiden, de tre oversigter, de ti artikler, `/faq`,
`/privatliv` og de syv hjælpesider: 23 adresser i alt.
`public/robots.txt` henviser til sitemap på produktionsdomænet.

Canonical og sitemap bruger `APP_URL`, som skal være `https://kanvi.dk` i
produktion. Private afstemninger og administrationssider er ikke med i sitemap;
deres eksisterende noindex-beskyttelse er bevaret.

De planlagte screenshots og artikel-specifikke `og:image` mangler fortsat.
Klasseartiklen har nu illustrationer til brugsscenarier og beskedtråd kontra
overblik; de øvrige illustrationer mangler. Billedbriefs er bevaret i oplæggene, men vises ikke på
de offentlige sider. Tilføj de rigtige produktbilleder med dimensioner og alt-tekst
senere; brug ikke fiktive UI-screenshots som dokumentation for produktet.

## Kontrol

`tests/Feature/ArticlesTest.php` kontrollerer alle ti sider, metadata, canonical,
overskrifter, CTA'er, interne links, oversigter, sitemap og fravær af redaktionelle
noter i den offentlige tekst.

## Class event article — 2026-09-29

The tenth article is available locally at `/til/klassearrangement`. Its source
and four image briefs are preserved in `klassearrangement.md`. The scenarios and
chat comparison illustrations now have WebP variants (390, 720 and 1440 px),
intrinsic dimensions, alt text and lazy loading. The hero, result screenshot and
article-specific Open Graph image remain pending. Prompts and asset paths are in
`docs/design/CLASS-ARTICLE-IMAGES.md`.
Public copy explains manual deadlines/reminders, date-only options and the
convention of one response per family. The existing homepage FAQ is used instead
of an unresolved `/faq` link. No new dependency or product feature was added.

Verification: the article tests failed before implementation (missing route,
index link and sitemap entry), then passed with 336 assertions. The full PHP
suite passed: 108 tests, 1,063 assertions; JavaScript: 25 tests. Pint, Vite build
and `git diff --check` passed. A bounded credential-pattern scan of the six task
files found no matches. Safari walkthrough: guide index → class article →
primary CTA → creation screen. Desktop and a 390 px iframe preview were visually
checked. Browser console output, CI, real mobile devices and production were
not verified during that article-text check. The shared project overview was
updated and read back. Subsequent commits, CI and the explicit decision to defer
server installation are recorded in `docs/DEPLOYMENT.md`.
