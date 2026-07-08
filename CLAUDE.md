# Shajaretna — شجرتنا

## Project Summary
Arab family tree web app built with Laravel + MySQL.
Handles polygamy, half-siblings, and consanguineous marriages.

## Stack
- Laravel 12, PHP 8.4, MySQL 8
- Authentication: mobile number + الاسم الرباعي (no email/password option)
- Frontend: Blade + Livewire 4, Tailwind CSS v4, Vite 8
- Node.js: must use v20+ (v22 via nvm). Run `nvm use 22` before `npm run build`.

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

### Constants (Section 2)
```
NODE  = { width:112, height:50, cornerRadius:8 }
GAP   = { couple:126, sibling:55, level:120, margin:75, topPad:80 }
COLOR = { nodeMale, nodeFemale, borderMale, borderFemale, nodeSelectedA/B, nodeOnPath,
          nodeDimmed, borderSelectedA/B/OnPath/Dimmed, edgeFather/Mother/Marriage/Highlight,
          textDefault/OnPath/Dimmed, genderPipMale/Female }
FIT_PADDING      = 0.88   (zoom-to-fit padding factor)
ROOT_X_GAP       = GAP.sibling * 3  (gap between structural root subtrees)
LINK_SNAP_RADIUS = NODE.width / 2   (56px — dragged center must be inside target rect)
```

### Layout Algorithm (Section 5)
- Paternal-first recursive subtree with couple-center model
- `structuralRoots` = people with no father AND not a marriage-only wife
- Marriage-only wives placed to the right of their husband at `GAP.couple` spacing
- Married-in husbands aligned to their wife's generation (regardless of whether wife has a father)
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

## Authentication
- Login/register combined: /login → App\Livewire\Login (guest-only middleware)
- Two-step flow: step 1 = mobile only; step 2 = name fields (only shown for new mobiles)
- Known mobile → log in immediately (no name check — mobile is the identity)
- Unknown mobile → show 4 name fields → create account + log in (created_by=null)
- Logout: POST /logout (CSRF-protected form in nav)
- All app routes protected by `auth` middleware group in web.php
- User::getRememberTokenName() returns '' — no remember_token column in users table

## How to Run the Project

### Fresh Clone Setup (new machine)
This section walks through getting the project running on a computer that has never had this code on it before (e.g. a home laptop). Every command below is typed into a terminal app.

**1. Download the code — `git clone`**
`git` is the tool that has been tracking every change made to this project. `clone` means "download a full copy of the repository, including its entire history" into a new folder. This creates a folder named `shajaretna` inside whatever folder your terminal is currently in.
```bash
git clone https://github.com/ramo-nadmah/shajaretna.git
```

**2. Move into the project folder — `cd`**
`cd` (change directory) tells the terminal "everything I type next happens inside this folder." Every command from here on assumes the terminal is standing inside `shajaretna` — if you close the terminal and reopen it later, you must `cd` back into the folder again before running any project command.
```bash
cd shajaretna
```

**3. Install the PHP libraries the app depends on — `composer install`**
This app doesn't write every line of code itself — it relies on ready-made PHP packages (the Laravel framework, Livewire, etc.), listed in a file called `composer.json`. **Composer** is the tool that reads that list and downloads each package into a `vendor/` folder. Nothing in the app runs without this. Run it while inside the `shajaretna` folder (from step 2):
```bash
composer install
```
(If Composer itself isn't installed on this computer yet, get it from https://getcomposer.org/download/ first.)

**4. Switch to the correct JavaScript engine version — `nvm use 22`**
The frontend build tool this project uses (Vite) requires Node.js version 20 or newer. **Node.js** is a program that runs JavaScript outside a browser (needed here just to build the CSS/JS files, not to run the app itself — that's PHP's job). **nvm** (Node Version Manager) lets one computer keep several Node versions installed side by side and switch between them. This switches the current terminal to version 22:
```bash
nvm use 22
```
(If `nvm` isn't installed, or Node 22 was never installed through it, see https://github.com/nvm-sh/nvm — after installing nvm itself, run `nvm install 22` once.)

**5. Install the JavaScript libraries the app depends on — `npm install`**
Same idea as Composer, but for frontend packages (Tailwind CSS, Vite) listed in `package.json`. **npm** (Node Package Manager) comes bundled with Node.js and downloads them into a `node_modules/` folder:
```bash
npm install
```

**6. Create your own local settings file — `cp .env.example .env`**
Laravel reads its configuration (database connection details, secret keys, app settings) from a file called `.env`. This file is intentionally never stored in git (it holds secrets and differs on every machine). The repo ships a template with placeholder values, `.env.example`. This command copies that template so you have your own editable `.env`:
```bash
cp .env.example .env
```

**7. Generate the app's secret encryption key — `php artisan key:generate`**
`php artisan` runs Laravel's own command-line tool (`artisan`), bundled with the framework. Laravel needs a random secret key to encrypt session data and cookies securely — without one, the app refuses to run. This command generates that key and writes it straight into your new `.env` file:
```bash
php artisan key:generate
```

**8. Point `.env` at your local database**
Open the `.env` file in any text editor and set these three lines:
```
DB_DATABASE=shajaretna
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```
This tells Laravel which MySQL database to connect to and which credentials to log in with — use whatever MySQL user already exists on this computer (or create one).

**9. Create the actual (empty) database — a `mysql` command**
Step 8 only told Laravel *where* to look; the database itself doesn't exist yet on a brand new machine. This command talks to MySQL directly and creates an empty database named `shajaretna`, set up with `utf8mb4` — the character encoding needed to correctly store Arabic text:
```bash
mysql -u root -e "CREATE DATABASE shajaretna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```
(Replace `root` with your own MySQL username if different; it may prompt for a password.)

**10. Build the database tables — `php artisan migrate`**
The database exists but is completely empty, with no tables. **Migrations** are step-by-step, version-controlled instructions (stored in `database/migrations/`) that describe how to build each table (`users`, `people`, `marriages`, `parent_child`). This command runs all of them in order:
```bash
php artisan migrate
```

Once this finishes, the project is fully installed. Continue with "Dev server" below to actually start it and view it in a browser.

### Prerequisites
- MySQL 8 must be running (database `shajaretna` already exists and is migrated)
- Node.js v22 via nvm: `nvm use 22`

### Dev server (two terminals)
```bash
# Terminal 1 — Vite asset watcher
nvm use 22 && npm run dev

# Terminal 2 — Laravel server (pick any free high port)
php artisan serve --port=19000
```
App is at **http://localhost:19000** — redirects to `/login`.

### One-shot build (no watcher needed)
```bash
nvm use 22 && npm run build
php artisan serve --port=19000
```

### Useful artisan commands
```bash
php artisan kinship:test          # run all 16 kinship label tests
php artisan tinker --execute '…'  # always single-quoted to avoid shell expansion
php artisan route:list --except-vendor
```

### Test credentials
Any mobile number works — first use registers, subsequent uses log in.
Use a 4-part Arabic name (الاسم الرباعي) on first login.

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
