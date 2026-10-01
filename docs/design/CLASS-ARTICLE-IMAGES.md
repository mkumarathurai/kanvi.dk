# Article illustrations

Editorial illustrations for the articles, generated with the Claude app's
built-in image generation tool. These are illustrations, not screenshots of
product behavior; product UI is always a real screenshot. WebP files are
resized/encoded variants of the generated originals; all in-image Danish labels
are checked visually before publishing.

The two class article illustrations below were generated 2026-09-29. The
**Planned prompts** section holds the prompts for the remaining articles,
written 2026-10-01 from the image briefs in `docs/indhold/`; move an entry up
here with its asset paths once it is generated and published.

## Scenarios

Use case: illustration-story.
Asset type: Wide editorial illustration for the Danish Kanvi class-event planning article, 16:9 horizontal.
Primary request: Four warm, restrained hand-drawn illustrated scenes on a warm cream #FFFDF8 background, in a balanced 2-by-2 arrangement. Scene 1: school class party with modest bunting and a small party hat. Scene 2: parent council, three adults talking around a small table. Scene 3: summer party with sunshine and a simple picnic table. Scene 4: shared excursion, a friendly small bus.
Style: Soft Nordic editorial illustration, fine dark navy #0B2540 outlines, flat green #16A34A and mint #86EFAC with restrained yellow #FBBF24 accents. Human and calm, generous whitespace, no gradients, no photorealism, no heavy card boxes.
Text: Only four short Danish labels beneath the scenes, exactly "Klassefest", "Forældreråd", "Sommerfest", "Fælles tur". Large crisp rounded sans-serif labels. No heading or logo; the website will render the heading as accessible text.
Constraints: Equal importance to all four scenes; no app screenshots or invented product UI; no additional text, watermark or fabricated logo. Polished website-ready artwork.

Files: `public/images/articles/klassearrangement-brugsscenarier.webp`, with `-720` and `-390` variants.

## Chat comparison

Use case: infographic-diagram.
Asset type: Horizontal 16:9 editorial comparison illustration for Kanvi's Danish guide to class events.
Primary request: A clear two-panel comparison, warm cream background #FFFDF8 and generous whitespace, fine hand-drawn navy #0B2540 outlines with restrained green #16A34A, mint #86EFAC, yellow #FBBF24. Flat Soft Nordic editorial artwork with calm rounded sans-serif Danish type. This is an illustrative diagram, NOT a fabricated app screenshot.
Left panel labeled exactly "Klassechatten": six overlapping simple message slips with these exact short Danish phrases: "Vi kan den 7.", "Ikke den 7.", "14. måske.", "Er det med børn?", "Vi vender tilbage.", "Hvor er det oprindelige opslag?".
Right panel labeled exactly "Kanvi": four neatly aligned date rows with clearly readable fictional totals, using clean horizontal green tally bars, the second bar longest and softly highlighted. Row labels exactly "6. november", "7. november", "13. november", "14. november"; corresponding totals exactly "14 kan", "19 kan", "11 kan", "17 kan". Small caption "Eksempel" below the right panel.
Constraints: No app chrome, no browser or phone, no calendar grid, no redesigned logo, no extra text, no watermark. Do not embed a main heading: the website supplies "Mindre beskedjagt. Mere overblik." as accessible text. Make all wording legible at 720px display width. Two panels equally sized.

Files: `public/images/articles/klassechat-vs-kanvi.webp`, with `-720` and `-390` variants.

## Planned prompts — written 2026-10-01, not generated yet

Shared style for every prompt below (repeat it verbatim after the primary
request): Style: Soft Nordic editorial illustration, warm cream background
#FFFDF8, fine hand-drawn dark navy #0B2540 outlines, flat green #16A34A and
mint #86EFAC with restrained yellow #FBBF24 accents. Human and calm, generous
whitespace, no gradients, no photorealism, no heavy card boxes. Calm rounded
sans-serif Danish type. Constraints: no app chrome, no browser or phone frame,
no redesigned logo, no watermark, no extra text beyond the labels listed. Do
not embed a main heading; the website supplies it as accessible text. Make all
wording legible at 720 px display width. Horizontal 16:9.

Where a panel shows date rows with totals, use four neatly aligned rows with
clean horizontal green tally bars, one row softly highlighted as best, and a
small caption "Eksempel" below the panel.

### venner — gruppechat vs. Kanvi

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "Vi skal snart ses!": six overlapping simple message
slips with these exact Danish phrases: "Hvad med fredag?", "Kan ikke 😭",
"Lørdag?", "Har børnene.", "Næste uge?", "Jeg vender tilbage.".
Right panel labeled exactly "Kanvi": four date rows, labels exactly
"Fre. 7. november", "Lør. 8. november", "Fre. 14. november", "Lør. 15. november";
totals exactly "5 kan", "7 kan", "4 kan", "6 kan"; second row highlighted.

File: `public/images/articles/venner-gruppechat-vs-kanvi.webp`
Alt: `Sammenligning af gruppechat og datoafstemning med venner`

### polterabend — stor gruppe

Use case: illustration-story. Eighteen small, varied, friendly hand-drawn
people gathered loosely around one central poll card. The card shows four date
rows with short green tally bars and no readable totals. Conveys: many people,
few dates, one decision. No other text.

File: `public/images/articles/stor-gruppe-polterabend.webp`
Alt: `Stor gruppe der finder en fælles dato til polterabend`

### polterabend — chat vs. Kanvi

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "Polterabend-chatten": six overlapping message slips
with these exact phrases: "Jeg kan 9. og 16.", "Ikke 16.", "Hvad med 23.?",
"Jeg kan måske.", "Skal vi ikke tage 30.?", "Vent, er 9. stadig i spil?".
Right panel labeled exactly "Kanvi": four date rows, labels exactly
"Lør. 9. maj", "Lør. 16. maj", "Lør. 23. maj", "Lør. 30. maj"; totals exactly
"8 kan", "6 kan", "11 kan", "7 kan"; third row highlighted.

File: `public/images/articles/polterabend-chat-vs-kanvi.webp`
Alt: `Sammenligning af gruppechat og datoafstemning til polterabend`

### familien — familiechat vs. Kanvi

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "Familiechatten": five overlapping message slips
with these exact phrases: "Kan I den 8.?", "Vi kan ikke.", "Hvad med 14.?",
"Skal lige høre børnene.", "Jeg troede det var den 15.?".
Right panel labeled exactly "Kanvi": four date rows, labels exactly
"Søn. 8. marts", "Lør. 14. marts", "Søn. 15. marts", "Lør. 21. marts"; totals
exactly "6 kan", "9 kan", "7 kan", "8 kan"; second row highlighted.

File: `public/images/articles/familie-chat-vs-kanvi.webp`
Alt: `Sammenligning af familiechat og datoafstemning`

### foreninger — brugsscenarier

Use case: illustration-story. Four small cards of equal importance in a
balanced 2-by-2 arrangement, each with a small hand-drawn calendar icon and one
label beneath, exactly "Bestyrelsesmøde", "Arbejdsdag", "Sommerfest",
"Udvalgsmøde". Cards only — no scenes, no people.

File: `public/images/articles/forening-brugsscenarier.webp`
Alt: `Eksempler på datoafstemninger til aktiviteter i en forening`

### foreninger — beskeder vs. Kanvi

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "Hvem kan hvornår?": three message slips styled as a
mail, a chat bubble and an SMS, with these exact phrases: "Jeg kan den 4.",
"Ikke den 4.", "Måske den 11.".
Right panel labeled exactly "Kanvi": four date rows, labels exactly
"Tir. 4. maj", "Tor. 6. maj", "Tir. 11. maj", "Tor. 13. maj"; totals exactly
"7 kan", "5 kan", "9 kan", "6 kan"; third row highlighted.

File: `public/images/articles/forening-beskeder-vs-kanvi.webp`
Alt: `Datoer fra mail og beskeder samlet i en Kanvi-afstemning`

### bestyrelser — mail vs. Kanvi

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "Mailtråden": a stack of five overlapping mail slips
whose subject lines read exactly "Mødedato", "SV: Mødedato", "SV: SV: Mødedato",
"VS: Mødedato", "SV: SV: SV: Mødedato".
Right panel labeled exactly "Kanvi": four date rows, labels exactly
"Tir. 3. februar", "Tor. 5. februar", "Tir. 10. februar", "Tor. 12. februar";
totals exactly "4 kan", "6 kan", "5 kan", "6 kan"; second row highlighted.

File: `public/images/articles/bestyrelsesmoede-mail-vs-kanvi.webp`
Alt: `Sammenligning af mailtråd og datoafstemning til bestyrelsesmøde`

### bestyrelser — flere situationer i foreningen

Use case: infographic-diagram. Three small poll cards labeled exactly
"Bestyrelsesmøde", "Arbejdsdag", "Sommerfest", each with a tiny calendar icon
and three short tally bars, connected by soft hand-drawn arrows that all point
into one larger calm overview card with four date rows and a small green check.
No readable numbers anywhere.

File: `public/images/articles/forening-find-dato.webp`
Alt: `Datoafstemninger til bestyrelsesmøde og aktiviteter i en forening`

### find-en-dato — få valgmuligheder

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "20 datoer 😵": one long, cramped, slightly askew
list of twenty thin unreadable date lines.
Right panel labeled exactly "4 gode muligheder 👍": four clear, calm date rows
with short green tally bars and no readable totals.

File: `public/images/articles/antal-datoer-afstemning.webp`
Alt: `Få konkrete datoer gør det lettere at finde en fælles dato`

### find-en-dato — chat vs. overblik

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "I gruppechatten": twelve small speech bubbles;
five legible with these exact phrases: "Jeg kan den 8.", "Hvad med 15.?",
"15 går ikke 😕", "Er 9. stadig en mulighed?", "Jeg vender tilbage."; the other
seven tiny and unreadable.
Right panel labeled exactly "I Kanvi": four date rows, labels exactly
"Fre. 8. maj", "Lør. 9. maj", "Fre. 15. maj", "Lør. 16. maj"; totals exactly
"6 kan", "8 kan", "5 kan", "7 kan"; second row highlighted.

File: `public/images/articles/find-dato-chat-vs-kanvi.webp`
Alt: `Forskel på at finde en dato i gruppechat og med Kanvi`

### julefrokost — fredag eller lørdag

Use case: infographic-diagram. Two equal halves.
Left half labeled exactly "Fredag 🍻", right half labeled exactly "Lørdag 🎄".
Beneath each label, two small hand-drawn calendar cards with short green tally
bars suggesting votes, slightly longer bars on the right half. No readable
numbers. Small caption "Eksempel" bottom right.

File: `public/images/articles/julefrokost-fredag-eller-loerdag.webp`
Alt: `Afstemning om julefrokost fredag eller lørdag`

### julefrokost — chat vs. Kanvi

Use case: infographic-diagram. Two equal panels.
Left panel labeled exactly "Julefrokost i gruppechatten": six overlapping
message slips with these exact phrases: "3. december?", "Kan ikke.", "10.?",
"Måske.", "17.?", "Der har vi allerede noget.".
Right panel labeled exactly "Julefrokost i Kanvi": four date rows, labels
exactly "Fre. 4. december", "Lør. 5. december", "Fre. 11. december",
"Lør. 12. december"; totals exactly "7 kan", "9 kan", "6 kan", "8 kan";
second row highlighted.

File: `public/images/articles/julefrokost-chat-vs-kanvi.webp`
Alt: `Planlægning af julefrokost i gruppechat sammenlignet med Kanvi`
