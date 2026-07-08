# Shajaretna — شجرتنا

An Arab family tree web app. Lets you build out a family tree — including polygamous marriages, half-siblings, and marriages between blood relatives (common in Arab families) — and look up how any two people in the tree are related to each other, with the relationship spelled out as a proper Arabic kinship term (e.g. "عمه", "ابن خالتها").

## Tech Stack
- **Backend:** Laravel 13 (PHP 8.4)
- **Database:** MySQL 8
- **Frontend:** Blade templates + Livewire 4 (server-driven interactivity, no separate JS framework) + Tailwind CSS v4
- **Build tool:** Vite 8
- **Authentication:** mobile number + full Arabic name (الاسم الرباعي) — no email/password
- **Node.js:** requires v20 or newer (this project uses v22 via nvm)

## Getting Started

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

**11. (Optional) Fill the database with sample data — `php artisan db:seed`**
Right after migrating, every table is empty — no people, no families. A **seeder** is a script that inserts a batch of ready-made sample rows, so you have something to look at and test with immediately instead of manually adding every person by hand. This project's seeder (`database/seeders/DatabaseSeeder.php`) creates one login account plus a 4-generation sample family tree (14 people, including a two-wife marriage and a half-sibling case):
```bash
php artisan db:seed
```
Skip this step if you'd rather start with a completely empty tree and add real people yourself.

Once this finishes, the project is fully installed. Continue with "Running the Dev Server" below to actually start it and view it in a browser.

### Prerequisites (already-set-up machine)
- MySQL 8 must be running (database `shajaretna` already exists and is migrated)
- Node.js v22 via nvm: `nvm use 22`

### Running the Dev Server (two terminals)
```bash
# Terminal 1 — Vite asset watcher (rebuilds CSS/JS automatically as you edit files)
nvm use 22 && npm run dev

# Terminal 2 — Laravel's own web server (pick any free high port)
php artisan serve --port=19000
```
App is at **http://localhost:19000** — redirects to `/login`.

### One-shot Build (no watcher needed)
Use this when you just want to view the app once, without editing CSS/JS live:
```bash
nvm use 22 && npm run build
php artisan serve --port=19000
```

### Useful Artisan Commands
`artisan` is Laravel's built-in command-line tool.
```bash
php artisan kinship:test          # run all 16 kinship label tests
php artisan tinker --execute '…'  # open an interactive PHP console scoped to the app; always single-quote to avoid shell expansion
php artisan route:list --except-vendor   # list every URL route the app responds to
```

### Test Credentials
There's no fixed test account — any mobile number works. The first time a number logs in it registers a new account (asking for a full Arabic name); every time after that it just logs in.
