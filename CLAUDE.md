# Shajaretna — شجرتنا

This file holds AI-agent-specific working notes for this codebase: coding standards, algorithm internals, and current project status. For what the project is, the tech stack, and how to install/run it, see [README.md](README.md).

## Database Schema

### users
- id
- first_name, second_name, third_name, fourth_name
- mobile (unique, required)
- created_by (nullable → users.id) — nullable because first user has no reference
- created_at, updated_at

### people
- id
- name_ar (required)
- gender enum('male','female') (required)
- photo (nullable)
- birth_year (nullable)
- death_year (nullable)
- is_alive (nullable — null=unknown, true=alive, false=dead)
- created_by (required → users.id)
- created_at, updated_at

### marriages
- id
- husband_id (required → people.id)
- wife_id (required → people.id)
- created_by (required → users.id)
- created_at, updated_at

### parent_child
- id
- parent_id (required → people.id)
- child_id (required → people.id)
- parent_type enum('father','mother') (required)
- created_by (required → users.id)
- created_at, updated_at

## Key Rules
- Paternal-first: system works with fathers only, maternal line is bonus
- Missing data never breaks the system — algorithm stops gracefully
- utf8mb4 encoding everywhere
- All tables have created_by audit trail

## Current Status
- Laravel project created ✅
- MySQL database "shajaretna" created ✅
- .env configured ✅ (SESSION_DRIVER=file — no sessions table)
- Custom migrations created and run ✅ (users → people → marriages → parent_child)
- Eloquent models created ✅ (User, Person, Marriage, ParentChild)
- KinshipCalculator service created ✅ — 16/16 tests passing
- Test data seeded ✅ (4-generation family, 14 people)
- Authentication ✅ — mobile + 4-name, combined login/register flow
- UI built ✅ — all Livewire pages working and verified in browser
- Family tree visual ✅ — pan/drag/zoom, rectangular nodes, side panel, mobile drawer
- Drag-to-link ✅ — drag a node onto another to create parent/marriage links with full validation
- Kinship labels ✅ — 3rd person, both directions, marriage-extended, double marriage-extended, half-sibling, 16/16 tests
- Tree design settings ✅ — every visual number (node/font/gap/line sizes) live-editable via the ⚙
  panel, with a single global scale multiplier; persisted to localStorage
- Polygamy visual clarity ✅ — each co-wife gets her own mother-line color and vertical bus height,
  a married-in spouse (no father of their own) gets a dashed border + small row offset, and edge
  routing is anchored to avoid overlapping/clipping through sibling nodes
- Patrilineal toggle fixed ✅ — "عائلة واحدة" button (men-only lineage view) previously crashed
  silently on any family with daughters; now filters correctly

## Coding Standards
- No single-letter or two-letter variable names (no $da, $db, $pA, $pB etc.)
- Use full descriptive names: $depthFromA, $pathFromA, $bIsMale, $personCache
- PHP built-in functions must be prefixed with \ when inside a namespace (e.g. \count(), \array_shift())
- `empty()` is a language construct — use === [] instead
- JS: all magic numbers live in named frozen constants (NODE, GAP, COLOR); no inline literals
- JS: logic extracted into named functions; no inline style objects in SVG builders

## KinshipCalculator — How It Works
- Located at: app/Services/KinshipCalculator.php
- Algorithm: BFS upward from both people → find Lowest Common Ancestor (LCA)
- Path structure: each step = ['id' => int, 'via' => ParentType enum]
- All labels are 3rd person possessive ("أبوه" not "أبوك"; "عمته" not "عمتك")
- `$p = $aMale ? 'ه' : 'ها'` — suffix switches on A's gender
- `lcaViaFather` = pathFromA[nUp-2] (SECOND-TO-LAST step, not last) — determines عم vs خال
  - pathFromA[0]['via']=Father → عم/عمة; pathFromA[0]['via']=Mother → خال/خالة
- Ta marbuta (ة) must become ت before possessive suffix: "عمت" + "ه" = "عمته"
- `toConstructState(label, referenceMale)` — strips trailing possessive suffix and restores ة for construct-state chains (e.g. "ابن عم زوجها" not "ابن عمه زوجها")
- `siblingLabel()` uses `$p` (not hardcoded 'ه') so "اختها من أبيها" is correct for female A
- Marriage-extended kinship (4 cases): (1) direct marriage, (2) B married to A's blood relative, (3) A married to B's blood relative, (4) double: A's spouse blood-related to B's spouse
  - Case 4 label structure: [B's role to B's spouse] + [B's spouse relative to A's spouse, construct state] + [A's spouse relative to A]
  - Example: Said→Nadia = "زوجة ابن أخي زوجته"; Nadia→Said = "زوج عمة زوجها"
- Half-sibling detection: both must have mothers AND they differ → "أخوه من أبيه"
- KinshipResult.arabicLabels = [labelAB, labelBA] (both directions if different)
- Test command: php artisan kinship:test (16/16 tests passing)

## Design System
- Palette: ground #131D2B · parchment #E8DFD0 · gold #C8A63E · jade #2E5A4B · muted #6B829E
- Custom tokens defined in resources/css/app.css via @theme — use as bg-ground, text-parchment, text-gold, etc.
- RTL: html dir=rtl is set globally; use logical properties (ps-/pe-, ms-/me-) not left/right
- Component classes: .btn-primary, .btn-ghost, .input-field, .select-field, .card, .pill-male, .pill-female

## Livewire Components
- app/Livewire/PersonList.php → / (people list with live search)
- app/Livewire/PersonForm.php → /people/create (add person; WithFileUploads but photo upload paused)
- app/Livewire/ParentLink.php → /people/{person}/parents (link father/mother)
- app/Livewire/KinshipLookup.php → /kinship (select two people → Arabic label result)
- app/Livewire/MarriageManager.php → /marriages (add/delete marriages, grouped by husband)
- app/Livewire/FamilyTree.php → /tree (interactive SVG family tree; linkAsParent + linkAsSpouse Livewire methods)
- Views in resources/views/livewire/
- Layout in resources/views/layouts/app.blade.php (all pages except /tree)
- Layout in resources/views/layouts/tree.blade.php (full-width, no max-w, flex-column body)

## Family Tree Page (/tree)
All rendering is client-side JS inside an IIFE in `resources/views/livewire/family-tree.blade.php`.
PHP only passes PEOPLE / PC / MARRIAGES as JSON via @json.

### Patrilineal Toggle ("عائلة واحدة" / "الكل" button)
Switches the tree between the full family and a men-only لineage view — hides every woman, all
marriages, and all mother-links, keeping only father→son chains. `togglePatrilinealMode()` flips
`patrilinealMode` and calls `rebuildLayout()` + `render()` + `fitTreeToViewport()`.
- **Bug fixed:** `activePC` (the active parent_child rows for the current mode) used to filter only
  by relation type (`type === 'father'`), not by the child's gender — so a father→daughter link was
  still included even in patrilineal mode. That daughter's id then showed up as a "child" in
  `fatherChildrenOf`, but she had no entry of her own there (she'd been filtered out of
  `renderPeople`), so the generation-assignment BFS crashed reading `fatherChildrenOf[herId].forEach`.
  The crash was silent to the user — it happened after the button's own label had already flipped,
  so clicking looked like it did nothing (label changed, tree never actually re-rendered).
- Fix: `activePC` in patrilineal mode also requires `renderPeopleIds.has(pc.child_id)` — i.e. the
  child must have survived the gender filter too, not just be a `'father'`-type relation.

### Design Config & Constants (Section 2)
Every tunable visual number (node size, fonts, gaps, link widths, ring/arc sizes) lives in
`DEFAULT_TREE_CONFIG` — a single flat object — instead of being hardcoded at call sites.
- `TREE_CONFIG` = the live, mutable copy of `DEFAULT_TREE_CONFIG`, loaded from `localStorage`
  (key `shajaretna.treeDesignConfig.v1`) on init, edited live via the ⚙ settings panel (Section 17)
- `TREE_CONFIG.scale` = a single global multiplier (default `1`) applied on top of every size
  value — colors, opacity, and dash-pattern *ratios* are exempt
- `applyTreeConfig()` recomputes `NODE` / `GAP` / `LINE` / `EFFECT` / `FIT_PADDING` /
  `ROOT_X_GAP` / `LINK_SNAP_RADIUS` from `TREE_CONFIG * scale` — call this, then
  `rebuildLayout()` + `render()`, any time `TREE_CONFIG` changes
- `NODE` = `{ width, height, cornerRadius, fontSizeName1/2, nameLineOffset, nameLineGap,
  genderPipRadius/Inset, marriedInBorderDashArray, nameLine2OpacityFactor, genderPipOpacityFactor }`
- `GAP` = `{ couple, sibling, level, margin, topPad, marriedInDrop, motherBusStep }`
- `LINE` = `{ fatherWidth/Highlight, motherWidth/Highlight/DashArray/Opacity, marriageWidth,
  fatherBusYRatio, motherBusYRatio, motherOffsetRatio, marriageArcCurveRatio,
  dimmedOpacity, fatherOpacity, marriageOpacity }`
- `EFFECT` = `{ dropRingPadding/StrokeWidth, glowRingPadding/StrokeWidth, marriageArcMinHeight,
  canvasBottomMargin, nodeBorderDefault/OnPath/Dimmed/Selected/DropTarget, dropTargetScale,
  glowRingOpacity, dimmedNodeOpacity }`
- `COLOR` = unchanged, not scale-affected (see Design System above)
- `ZOOM_STEP_FACTOR` — multiplier per +/- zoom button click, from `TREE_CONFIG.zoomStepFactor`
- **No inline literals in rendering code:** every ratio/opacity/scale-factor used by the render
  functions is a named `TREE_CONFIG` entry, even ones not exposed in the settings panel UI (e.g.
  `dimmedEdgeOpacity`, `nameLine2OpacityFactor`) — kept for readability, not because they're meant
  to be user-tunable. The one exception is `directionSign()`'s own `1 : -1` — that's the
  definition of the sign convention itself, not a call-site magic number, and things like
  `NODE.width / 2` for center↔edge conversions, which are geometry, not a design choice
- Defaults as of this writing: `nodeWidth:190, nodeHeight:88, nodeCornerRadius:12`; `gapCouple:210,
  gapSibling:90, gapLevel:185, gapMargin:110, gapTopPad:120`; `fatherLineWidth:2.5, motherLineWidth:2.5,
  marriageLineWidth:2.5`; `motherDashLength:3, motherDashGap:6, motherOpacity:0.55` (previously an
  almost-invisible `0.5 7` dash at `0.45` opacity — bumped after user feedback that it was too faint
  to see without zooming in a lot)
- **Important:** `onConfigChanged()` deliberately does NOT call `fitTreeToViewport()` — that function
  auto-zooms to fill the viewport, which would silently cancel out any size change (uniform scaling
  is invisible after a fit-to-viewport). Settings changes re-render at the current pan/zoom so the
  user can actually see the effect; they hit "⌖" manually to re-fit afterward.
- `FIT_PADDING = TREE_CONFIG.fitPadding` (default `1`, not affected by `scale`) — zoom-to-fit padding factor
- `ROOT_X_GAP = GAP.sibling * 3` — gap between structural root subtrees
- `LINK_SNAP_RADIUS = NODE.width / 2` — dragged center must be inside target rect

### Layout Algorithm (Section 5)
- Paternal-first recursive subtree with couple-center model
- `structuralRoots` = people with no father AND not a marriage-only wife AND not an attached husband
- Marriage-only wives (no father of their own) placed to the right of their husband at `GAP.couple` spacing
- `attachedHusbandOf[wifeId] = husbandId` — a husband with no father of his own, married to a wife
  who DOES have a father, is attached beside her instead of placed as an unrelated independent root.
  Before this, his only anchor to the tree was the marriage itself, so he'd land wherever the next
  free root slot was — often visually far from his wife and kids, making the mother-child line
  between her and their children stretch across the whole canvas. `subtreeWidth()` reserves
  `GAP.couple + subtreeWidth(attachedHusbandId)` next to her; `placeSubtree()` places his entire
  subtree edge-to-edge beside her (not center-to-center) so his own width can never overlap her.
- Married-in husbands aligned to their wife's generation (regardless of whether wife has a father) —
  unaffected by the above; generation (row) and X-position (column) are assigned independently
- **Married-in visual cue:** `isMarriedInSpouse(id)` = `isMarriageOnlyWife(id) || attachedHusbandIds.has(id)`
  — anyone positioned purely by marriage rather than blood descent gets a small `GAP.marriedInDrop`
  (default 24px) downward nudge on their own box only (never their children, who still land on the
  normal generation row) plus a gold dashed border (`COLOR.edgeMarriage`, `NODE.marriedInBorderDashArray`)
  in `nodeAppearanceFor()`'s default branch — makes it visually obvious at a glance that e.g. a wife
  sitting beside her husband's siblings isn't one of them
- **Couple spacing consistency:** the attached-husband edge-to-edge gap is `GAP.couple - NODE.width`,
  not `GAP.couple` — `GAP.couple` is a center-to-center convention everywhere else in this file, so
  reusing it as a raw edge gap here made an attached couple sit a whole extra `NODE.width` farther
  apart than a normal marriage-only-wife couple. `subtreeWidth()`'s reservation for this case is
  `GAP.couple + subtreeWidth(attachedHusbandId)` to match (the `NODE.width` terms cancel out)
- `subtreeWidth()` memoised; `placeSubtree()` recursive
- Fallback: unplaced people positioned at `nextRootX`
- `positionOverrides` map: drag overrides applied on top of `basePositions`

### SVG Setup (Section 10)
- SVG element: `width:100%; height:100%` via CSS — no viewBox, no JS setAttribute for dimensions
- Do NOT set width/height/viewBox on the SVG — that creates a second clip region
- `treeGroup` = `<g id="tree-group">` — pan/zoom applied via CSS transform on this element

### Edge Rendering (Sections 11–12)
- Father edges: orthogonal bus style — stem down from parent → horizontal bus → drops to each child
  - Base bus drawn first in normal color; per-child highlighted overlay drawn on top
  - This prevents the bus glowing toward non-path siblings
- Mother edges: offset dashed lines (offset ±15% of NODE.width to avoid overlap)
  - The mother's own exit point (`startX`) is always offset the SAME fixed direction — consistent
    for every one of her children, since it's just sitting beside the father-edge's centered stem
  - The entry point at each child (`endX`) instead follows `directionSign(parent.x, child.x)` —
    a fixed entry offset made the line double back on itself whenever a child ended up on the
    opposite side of its mother than the offset assumed (common for a second wife, since children
    are laid out centered on the FATHER, not on each individual wife — see عائشة خليل, who sits
    between her two sons). Only the entry side needed to adapt; the exit side never did.
  - `motherEdgeColorOf[wifeId]` (built in `rebuildLayout()`, cycling `MOTHER_LINE_PALETTE`) gives
    each wife of a polygamous husband her own mother-line color, in marriage order — a husband
    with only one wife is left out of the map, so her mother-links stay the plain default `edgeMother`
  - `LINE.fatherBusYRatio` (0.38) / `LINE.motherBusYRatio` (0.62) — used to sit at the exact same
    height, so for a couple's shared children the mother's dashed line was drawn directly on top of
    the father's solid line for the whole horizontal stretch. Now deliberately different heights.
  - `motherBusIndexOf[wifeId]` + `GAP.motherBusStep` — co-wives sit on the exact same row, so their
    individual mother-bus lines used to ALSO collide with each other (not just with the father's),
    since every wife's busY came from the same `motherBusYRatio` formula. Each wife now gets an
    extra `GAP.motherBusStep` (20px default) added per her marriage-order index, so any number of
    co-wives' mother-lines stack at clearly distinct heights instead of overlapping.
  - **Both busY formulas are anchored to the CHILDREN's row, not the parent's.** A parent can be a
    married-in spouse nudged down by `GAP.marriedInDrop` (see attachedHusbandOf), and co-wives stack
    `GAP.motherBusStep` on top of that — anchoring to the parent's own (shiftable) position let those
    add up enough to push a bus line below the children's own top edge, visually cutting through a
    child's (or sibling's) box. Children are never nudged, so their row is a stable anchor:
    `busY = childTopEdge - max(GAP.minClearanceAboveChild, ...)` — the `max()` floor guarantees a
    minimum clearance no matter how many co-wife steps or drops would otherwise stack up before it.
- Marriage arcs: quadratic Bézier curve above the couple

### Kinship Path Highlighting (Section 7)
- Directed upward-only BFS (child→parent only) — prevents routing through shared descendants
- `findAncestralHighlightPath(aId, bId)`: handles direct ancestor, direct descendant, and LCA cases
- `pathEdgeKeys`: sorted pair strings ("12-34") for O(1) edge lookup
- `buildPathHighlights()` called ONCE per `render()` — result passed as params to all renderers
  (previously called N+1 times; nodeAppearanceFor no longer runs its own BFS)

### Interaction State (Section 9)
- `selectedPersonA`, `selectedPersonB` — kinship selection
- `wasDraggingNode` — suppresses click-after-drag
- `dragHoverTargetId` — node currently under the dragged node (cleared on pointerup/cancel)

### Node Dragging (Section 11)
- `pointerdown` on nodeGroup → `stopPropagation()` prevents canvas pan
- `window.addEventListener('pointermove/pointerup/pointercancel')` with `cleanupDragListeners()`
- `pointercancel` handler: clears `dragHoverTargetId`, deletes `positionOverrides[personId]`, re-renders
- Drop target visual feedback: node scales 1.15× + dark green fill + bright green border + pulsing outer ring (`.ft-drop-ring` CSS animation)

### Drag-to-Link Validation (Section 12)
Menu shows when dragged node center lands within `LINK_SNAP_RADIUS` (56px) of another node center.
All checks run client-side; server-side guards in FamilyTree.php repeat the key ones.

Active validations in `buildLinkOptions`:
| Check | Rule |
|-------|------|
| `alreadyHasParent(childId, type)` | Child already has a father or mother |
| `isAncestorOf(targetId, draggedId)` | Would create circular ancestry |
| `coWives = shareHusband(A, B)` | Co-wives of the same man can't parent each other |
| `isSpouseOfAncestor(childId, parentId)` | Descendant of X cannot parent X's wife (e.g. son cannot be step-mother's father) |
| `isSiblingOfParent(parentId, childId)` | Sibling of child's existing parent cannot be another parent (incest — e.g. aunt cannot be nephew's mother) |
| `wifeIds.has(potentialWifeId)` | A woman already married cannot be offered as a new wife (polygamy is male-only) |
| `isAlreadyMarried(A, B)` | Already married to each other |
| `isInDirectAncestralLine(A, B)` | Marriage between blood relatives |

### Pan & Zoom (Section 15)
- All pan/zoom use `pointerdown/pointermove/pointerup/pointercancel` (not mousedown)
- `viewport.setPointerCapture(event.pointerId)` for clean pan capture
- Guard: `if (event.target.closest('button')) return` — prevents zoom buttons triggering pan
- `fitTreeToViewport()`: scales to fit with `FIT_PADDING`, centers in viewport

### Side Panel
- Selection chips A (green) and B (orange) with clear buttons
- Kinship result: primary label (B relative to A) + secondary (A relative to B) when asymmetric
- Legend: father/mother/marriage edge key + gender colour swatches
- Unlinked people: those in neither parent_child nor marriages → "ربط ↗" links to /parents page
- Mobile: panel hidden by default, slide-in drawer via ☰ button (bottom-left), backdrop overlay

### Design Settings Panel (Section 17)
- Opened via the ⚙ button in `.ft-zoom-controls`; `#settings-panel` toggles `.ft-hidden`
- `SETTINGS_FIELDS` maps each editable `TREE_CONFIG` key to its `<input>` id — add a new row
  there (and in the Blade markup) to expose any additional config value
- `bindSettingsInputs()` wires every field's `input` event to `TREE_CONFIG[key] = value` → `onConfigChanged()`
- `refreshLegendPreview()` keeps the static legend swatches (father/mother/marriage sample
  lines) showing the same width/dash/opacity as the real tree — call it after any `LINE` change
- "إعادة الضبط الافتراضي" (reset) button restores `DEFAULT_TREE_CONFIG` and re-populates inputs
- **CSS gotcha:** `.ft-settings-panel.ft-hidden { display: none; }` must exist as its own rule —
  `.ft-settings-panel` and `.ft-hidden` have equal specificity, so without this override whichever
  rule is declared later in the stylesheet wins the cascade regardless of which class was added last

## Authentication
- Login/register combined: /login → App\Livewire\Login (guest-only middleware)
- Two-step flow: step 1 = mobile only; step 2 = name fields (only shown for new mobiles)
- Known mobile → log in immediately (no name check — mobile is the identity)
- Unknown mobile → show 4 name fields → create account + log in (created_by=null)
- Logout: POST /logout (CSRF-protected form in nav)
- All app routes protected by `auth` middleware group in web.php
- User::getRememberTokenName() returns '' — no remember_token column in users table

## Next Steps
1. Photo upload for people (code scaffolded in PersonForm.php but paused; `php artisan storage:link` already run)
2. Birth/death year fields on people
3. Deeper kinship chains (nUp>=3 with maternal direction in ancestorGenitive)
4. Pinch-to-zoom on mobile (currently zoom buttons only)

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
