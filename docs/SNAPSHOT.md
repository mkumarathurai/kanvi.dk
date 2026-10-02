# Kanvi session snapshot

Project: Kanvi · Branch: `main` · HEAD: `ab4ff89` · Recorded: 2026-10-02 23:55 CEST.

`ab4ff89` is pushed and live. This file is uncommitted; committing it adds one
more commit after `ab4ff89`.

Technical handover only. Project knowledge is in the Knowledge Base overview at
`02-Projekter/Mathi ApS/Kanvi`. Outstanding work is on the KAN board:
https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575

## 1. Where we are

Kanvi is live at https://kanvi.dk and launches in week 41 once the KAN board is
done. This session published the editorial illustrations: Mathi generated
them in ChatGPT one at a time, each was checked (labels, weekdays, counts)
before it went in, and production now serves all seventeen from the prompt
list. A brief-versus-page check found one image no prompt ever covered — the
chat comparison for the date poll article — so its prompt was written and
Mathi generated it too. KAN-11, KAN-24 and KAN-25 are closed; every article
has every image its brief asks for. Late in the session a Facebook share of
kanvi.dk showed no image: the homepage had no Open Graph tags at all and the
listings had no image. KAN-26 fixed that and is live; it closes once Mathi
re-scrapes the homepage in Facebook's Sharing Debugger. KAN-16 (Outlook,
dark-mode logo) stays parked by Mathi.

## 2. What was done

- `b15b851` · Three comparison illustrations (venner, bestyrelser,
  find-en-dato). Venner prompt had wrong weekdays; the image was right.
- `9dc5464` · Foreninger prompt weekdays fixed (4 May 2026 is a Monday).
- `f41af4d` … `1f31f2e` · Fourteen ChatGPT illustrations, one commit each,
  across polterabend, familien, foreninger, bestyrelser, find-en-dato,
  julefrokost, hvor-mange-datoer and ingen-dato-passer-alle. Each commit bumps
  the image map in `tests/Feature/ArticlesTest.php` (written red first).
- `3aff293` · `docs/design/CLASS-ARTICLE-IMAGES.md`: every entry records its
  prompt and post-generation corrections; the planned section became
  **Shared style and lessons**.
- Pushed `29670ea..3aff293`; CI run 37059241683 green; production serves
  `3aff293`.
- `756dfd3` · Missing prompt for `gruppechat-vs-datoafstemning.webp`
  (Feb 2026 dates, weekdays verified).
- `c8b60e2` · That image on /datoafstemning. Pushed; CI run 37063998035
  green; production serves `c8b60e2`.
- Jira: KAN-24 and KAN-25 → Done (comments 15248, 15249); KAN-11 → Done
  (comment 15253).
- `75bb778` · KAN-26: homepage and the four listings get `og:*` share
  images (`/deling/forside`, `/deling/{guides,til,hjaelp,artikler}`) from
  `PollPreview`; homepage gains canonical. CI run 37064942608 green; verified
  as `facebookexternalhit`. KAN-26 comment 15254, still open.
- `99b49ea` · KAN-26 follow-up: Mathi found the drawn homepage card bland, so
  the homepage now shares a real screenshot of its hero
  (`public/images/share/kanvi-forside.png`, 1440 px capture scaled to
  1200x630); `/deling/forside` is gone. CI run 37065575581 green.
- `ab4ff89` · Homepage title is now "Find en dag, der passer alle – Kanvi?"
  (Mathi's wording; en dash, question mark echoes the logo). Also og:title.
  CI run 37066155990 green.
- KB overview: status entry, ChatGPT decision, next steps.

## 3. Unfinished

- Nothing half-done in code. Working tree holds only this snapshot.
- **KAN-26**: open until Mathi re-scrapes https://kanvi.dk/ in
  https://developers.facebook.com/tools/debug/ and sees the image; then close.
- **KAN-16**: Outlook round and the Gmail-app dark-mode logo question,
  parked by Mathi.
- Source PNGs sit in `~/Downloads/kanvi/`. Edited originals (`6-fixed.png`,
  `10-fixed.png`, `11-cropped.png`) were in the session scratchpad and are
  gone; the published WebPs are the record.

## 4. Next steps

1. Commit and push this snapshot (a push deploys; docs only):

   ```sh
   git add docs/SNAPSHOT.md && git commit -m "Save the session snapshot" -m "Refs: KAN-11"
   git push origin main
   ```

2. Close KAN-26 once Mathi confirms the Sharing Debugger shows the image.

3. KAN-16 when Mathi has Outlook installed: one round per the plan in
   `docs/design/EMAIL-DESIGN.md`, and his decision on the dark-mode logo.

4. Launch in week 41 once the board is done: Mathi announces; submit the
   sitemap in Search Console.

5. Check battery after any change:

   ```sh
   php artisan test --compact
   vendor/bin/pint --test
   npm test
   npm run build
   git diff --check
   ```

## 5. Waiting on Mathi

- Re-scrape kanvi.dk in Facebook's Sharing Debugger (KAN-26).
- Outlook installed, plus the dark-mode logo decision (KAN-16).
- The launch day within week 41.

## 6. Decisions made, and why

- **Mathi switched illustration generation to ChatGPT, one image per
  conversation** (2026-10-02). Supersedes the 2026-10-01 Claude-app decision;
  recorded in the KB overview.
- **Headlines and long sentences go in page text, not in the image** (working
  choice, not a Mathi decision). ChatGPT malformed or misspelled them; briefs
  list them as "Overskrift"/"Tekst", which the articles already render as bold
  text next to the image.
- **Small defects were fixed in the original rather than regenerated** when
  the edit removes pixels only (extra "r" cut out, extra row painted over with
  copied background, headline strip cropped). No text was drawn in.
- **Accepted as-is**: 17 people where the prompt said 18 (polterabend), 21
  rows in an unlabeled-count list (find-en-dato), roughly 13–14 people at the
  "14 kan" table. None is a label stating that exact count.
- **The homepage share image is a static screenshot**, not a drawn card
  (Mathi, 2026-10-02). Retake it when the hero changes: headless Chrome at
  1440x756, device scale 2, then scale to 1200x630.
- Captions ("Illustreret eksempel: …") are added under every image that shows
  totals, ordered highest first, matching the class article.
- **PageSpeed's render-blocking Livewire warning is left alone** (Mathi,
  2026-10-02). Lighthouse 12 mobile: home 95 (FCP 2.0 s, LCP 2.7 s), /til/venner
  100. Brotli and year-long caching are already on. If revisited: bundle the
  Livewire ESM into `app.js` with `@livewireScriptConfig` (module scripts do not
  block); the script cannot simply be dropped because the homepage demo poll
  uses the Alpine that ships with Livewire.

## 7. Dead ends and corrections

- A ChatGPT request for several images returns one contact sheet; tiles are
  ~420 px and unusable. One image per conversation.
- Segmented tally bars get miscounted; ask for smooth bars proportional to the
  totals. Asking ChatGPT to fix a single letter failed twice ("Bestyrrelses-
  møde"); fix it locally instead.
- The 2026-10-01 prompt list missed a brief image. Before declaring images
  complete, compare every `\`*.webp\`` name in `docs/indhold/<slug>.md` with
  `resources/content/articles/<slug>.md`.
- Chrome DevTools MCP cannot save screenshots to the scratchpad (outside its
  workspace roots); take them inline.
- Earlier dead ends still hold: `git log docs/SNAPSHOT.md` (`c9854b9`,
  `5601f29`).

## 8. Traps in this repository

- **A push is a deployment.** CI runs on every branch; only `main` deploys.
- The image test asserts exact per-article counts and real 390/720 variants.
- Chrome logs an "issue" (not error) "Lazy-loaded images should have explicit
  dimensions" on every article page, including untouched ones; images do
  carry width, height and aspect-ratio. Pre-existing, not investigated.
- Product UI must be a real screenshot; never a generated one
  (`docs/indhold/PUBLICERING.md`).
- Funnel events fire only in production; retention, recovery-mail and secrets
  rules unchanged — see `AGENTS.md` and the previous snapshot.

## 9. How to check this snapshot still holds

```sh
cd /Users/mathi/www/sites/kanvi.dk
git status --short
git log --oneline -4
git log --oneline @{u}..HEAD
curl -sS https://kanvi.dk/deploy-revision.txt
gh run list --limit 2
```

## 10. Verification evidence

Run 2026-10-02 on `c8b60e2`:

- `php artisan test --compact` on `75bb778`: 155 passed, 1 skipped (2083
  assertions).
  `npm test`: 38 passed, 0 failed. Pint passed, build passed,
  `git diff --check` clean.
- CI runs 37059241683 (`3aff293`) and 37063998035 (`c8b60e2`) green;
  production serves `c8b60e2`.
- Production: the ten changed pages each show 4 images; every srcset variant
  returns HTTP 200 and all 40 load in Chrome.
- Brief check: every `.webp` named in `docs/indhold/<slug>.md` appears in
  `resources/content/articles/<slug>.md`.
- Walked through in Chrome at 1280 px (venner) and 390 px mobile
  (hvor-mange-datoer, datoafstemning): images render, no horizontal
  overflow; console has no errors or warnings.

**Not verified:** keyboard navigation and contrast of the new images (images
carry alt text only), Lighthouse, KAN-16 Outlook.

## 11. Skills for the next session

- `snapshot` to resume from this file.
- `tdd` for any code change; Chrome DevTools MCP for production
  walkthroughs; Atlassian connector for KAN-16.
