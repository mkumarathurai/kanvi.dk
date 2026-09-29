# Kanvi Design System v1

**Status:** Approved visual direction for implementation\
**Brand direction:** Soft Nordic\
**Logo direction:** Dynamisk overlap + `kanvi?` (v2)

## 1. Design intent

Kanvi should feel warm, human and immediately understandable without
becoming childish or overly playful. The visual system combines Soft &
Friendly with Nordic simplicity.

The product UI is part of the brand. A user should recognize Kanvi from
the availability controls, warm cream surfaces, dark navy typography,
green actions and the green/yellow/red response language.

### Core principles

-   Mobile first.
-   One obvious primary action per screen.
-   Large touch targets and low cognitive load.
-   Ordinary Danish rather than software terminology.
-   Warm and friendly, but suitable for families, friends, associations,
    boards and teams.
-   Avoid generic stock imagery as a primary identity device.
-   Use small hand-drawn accents sparingly; the interface remains clean.
-   Do not add visual complexity merely to make a screen look designed.

## 2. Logo

The approved concept is **Dynamisk overlap + kanvi?**. The supplied v2 assets
and [BRAND.md](BRAND.md) supersede the original logo reference. Preserve the
green question mark and its yellow accent in the full wordmark.

Three overlapping forms represent different people, possibilities and
the common area they can find together. The logo deliberately does not
contain a calendar: Kanvi may later help groups agree on more than
dates.

Files:

-   `public/brand/kanvi-mark.svg` --- symbol only.
-   `public/brand/kanvi-logo.svg` --- light-background wordmark.
-   `public/brand/kanvi-logo-dark.svg` --- dark-background wordmark.
-   `public/brand/kanvi-wordmark.svg` --- wordmark without the symbol.
-   `public/brand/kanvi-app-icon.svg` --- app icon on cream.
-   `docs/design/kanvi-logo-reference.png` --- visual reference from
    concept exploration.

The SVGs in this package are implementation-ready vector interpretations
of the approved concept. Preserve the three-form composition and color
relationship when refining geometry.

## 3. Color tokens

  Token         Value       Use
  ------------- ----------- ------------------------------------
  Navy          `#0B2540`   Primary text, dark surfaces
  Green         `#16A34A`   Brand, primary CTA, "Kan"
  Mint          `#86EFAC`   Secondary brand form, soft accents
  Cream         `#FFFDF8`   Main page background
  Yellow        `#FBBF24`   "Måske", highlights
  Red           `#EF4444`   "Kan ikke", errors
  Soft green    `#DCFCE7`   Selected/positive backgrounds
  Soft yellow   `#FEF3C7`   Maybe backgrounds
  Soft red      `#FEE2E2`   Negative/error backgrounds
  Muted         `#64748B`   Secondary text
  Border        `#E7E5DF`   Subtle separators

Do not introduce additional brand colors unless a real UI requirement
demands it.

## 4. Typography

Use a clean rounded sans-serif. Prefer **Inter** as the implementation
default because it is robust and easy to ship. If the final brand
wordmark is custom-drawn later, keep product typography independent from
the logo.

-   H1: 44--56px desktop / 34--40px mobile, 700--750 weight, tight
    tracking.
-   H2: 28--36px, 700.
-   H3: 20--24px, 650--700.
-   Body: 16--18px, 400--500.
-   Small/meta: 13--14px, 450--550.
-   Buttons: 16px, 650.

Line height should remain generous. Avoid long centered body copy.

## 5. Layout and surfaces

-   Main max width: `1180px`.
-   Focused form/content width: about `760px`.
-   Mobile horizontal padding: `20px`.
-   Desktop horizontal padding: `32px`.
-   Card radius: `22px`.
-   Form control radius: `16px`.
-   Control height: about `52px`.
-   Minimum touch target: `44px`.
-   Cards use subtle borders and very soft shadows.
-   Avoid nested cards unless hierarchy genuinely requires them.

## 6. Core components

### Primary button

Green background, white text, generous horizontal padding, 52px height,
16px radius. Use one primary CTA per view whenever possible.

### Inputs

Cream/white surface, navy text, subtle neutral border. Focus uses green
border/ring. Labels remain visible; placeholders do not replace labels.

### Availability control

Every date has three clear choices:

-   **Kan** --- green.
-   **Måske** --- yellow.
-   **Kan ikke** --- red.

Unanswered is a fourth visual state and must remain neutral. Never
interpret an unanswered date as "Kan ikke".

### Autosave

There is no unnecessary submit button in the participant response flow.

States:

-   `idle`
-   `saving` → "Gemmer..."
-   `saved` → "✓ Gemt"
-   `error` → "Kunne ikke gemme · prøver igen..."

Only show "Gemt" after server acknowledgement of the latest local
revision. A stale request must never overwrite the visible state of a
newer choice.

### Results

Results become available after the participant's first server-confirmed
response. Show aggregate totals and individual participant responses.
Highlight the strongest date, but Kanvi does not automatically choose
the final date.

Ranking:

1.  Most `Kan`
2.  Most `Måske`
3.  Fewest `Kan ikke`

Exact ties remain ties.

## 7. Participant experience

The participant flow should feel faster than filling out a form:

1.  Open shared link.
2.  Understand what the poll is about.
3.  Enter name.
4.  Tap Kan / Måske / Kan ikke for dates.
5.  Choices autosave immediately.
6.  Results become available after first confirmed response.
7.  Participant can return and edit while the poll is open.

A participant counts as having answered when a display name exists and
at least one response has been server-confirmed.

Partial responses are valid. Consider copy such as "Du har svaret på 2
af 3 datoer."

Do not show other people's detailed responses before the participant has
made a first confirmed choice.

## 8. Creation flow

Keep creation under roughly 30 seconds:

**Hvad skal vi? → Vælg datoer → Tjek → Opret → Del**

Do not put an "Indstillinger" step in the primary creation flow. Good
defaults should handle normal use. Organizer settings can be available
after creation.

No account is required to create the first poll.

## 9. Organizer/admin

Public poll access and organizer access are separate.

After creation, make clear that the current browser can administer the
poll. Offer email as recovery, not as a prerequisite.

Organizer actions include:

-   Add/remove dates while open.
-   Finalize a date.
-   Reopen.
-   Close.
-   Share/copy link.

Removing an option that already has responses requires a warning and
should use soft deletion/archive semantics.

## 10. Status behavior

  Action               Open   Finalized   Closed   Archived
  ------------------ ------ ----------- -------- ----------
  New participants      Yes          No       No         No
  Edit responses        Yes          No       No         No
  View result           Yes         Yes      Yes    Limited
  Add/remove dates      Yes          No       No         No
  Finalize date         Yes          No       No         No
  Reopen                 No         Yes      Yes         No
  Close                 Yes         Yes       No         No

Reopening clears `final_option_id` and `finalized_at`; existing
responses remain.

## 11. Language

Kanvi talks like people, not software.

Prefer:

-   "Hvad skal vi?"
-   "Hvornår kunne det være?"
-   "Del linket"
-   "Hvad passer dig?"
-   "Hvilken dag passer bedst?"
-   "Så er dagen fundet ✓"

Avoid technical terms such as configure, participants, submit
availability and analytics in user-facing Danish.

## 12. Reference images

Reference images communicate visual intent, not exact pixel
specifications.

-   `kanvi-master-reference.png` — master reference for the homepage and core screens.
-   `kanvi-creation-reference.png` — creation and sharing references.
-   `kanvi-home-reference.png` — original exploration.
-   `kanvi-home-full-reference.png` — complete homepage section reference.
-   `kanvi-participant-reference.png`
-   `kanvi-logo-reference.png`

Where a reference image conflicts with this document, **this document
wins**. In particular, older mockups may contain a submit button or
expose other participants too early; those details have since been
superseded.

## 13. Rules for Codex

1.  Treat this file and `design-tokens.json` as source of truth.
2.  Reuse components rather than styling each screen independently.
3.  Do not infer new brand colors from reference-image antialiasing.
4.  Do not recreate the logo from a PNG; use the SVG assets.
5.  Build mobile behavior first, then expand responsively.
6.  Preserve semantic response states; color alone must not carry
    meaning.
7.  Meet WCAG-friendly contrast and keyboard/focus behavior.
8.  Keep animations short and functional.
9.  If implementation needs a design decision not covered here, choose
    the simplest behavior consistent with the principles and document
    the decision.
10. Do not add features visible in concept art unless they exist in the
    product specification.

## 14. Suggested implementation mapping

For Laravel 12 + Livewire + Tailwind v4:

-   Map brand tokens into Tailwind/CSS variables.
-   Create shared Blade/Livewire primitives for button, input, card,
    autosave indicator and availability choice.
-   Keep poll behavior in domain/actions rather than visual components.
-   Use SVG assets directly from `public/brand`.
-   Keep public poll pages `noindex`.

### Suggested component names

-   `x-kanvi.button`
-   `x-kanvi.input`
-   `x-kanvi.card`
-   `x-kanvi.logo`
-   `x-kanvi.availability-choice`
-   `x-kanvi.autosave-status`
-   `x-kanvi.result-bar`
-   `x-kanvi.participant-row`

------------------------------------------------------------------------

**Implementation instruction for Codex**

> Implement Kanvi according to `docs/design/DESIGN-SYSTEM.md` and
> `design-tokens.json`. The PNG files in `docs/design/` show visual
> intent. The written behavioral rules override inconsistencies in older
> mockups. Use the supplied SVG logo assets rather than recreating the
> logo. Keep the implementation mobile-first, component-based and
> faithful to the Soft Nordic direction.
