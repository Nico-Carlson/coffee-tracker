# Devlog for Coffee Tracker

## Goal

- This is an app that is able to track pour over history. I wanted an app where i can store all my favorite coffees. What went right, and what went wrong.

## Stack

- PHP 8.x, PDO, MySQL/MariaDB, XAMPP, vanilla HTML/CSS

## Features

- create users, log in/out, add/delete different beans, add/delete different brews, view brew history

## How to work with me (instructions for any assistant)

- I write all the code myself to learn it. Show only the lines that change, and
  send a whole file only when it fixes syntax or style errors LazyVim flags.
  (When I explicitly ask you to write a file, do that.)
- One step at a time. Wait for my output before the next step. Never queue a
  list of steps.
- Tell me when to commit, branch, merge and tag. Give git commands with the
  message inline (`-m`), never open the editor.
- Say when something is a guess. Ask me to paste output instead of assuming
  database or file state.
- Keep this file current: record context, decisions and gotchas as we go.

## Context

- Purpose: portfolio project for an internship application to OVO Talent LLC,
  a Miami creator-marketing agency. Their software is an internal CRM
  (crm.ovotalent.com) built around relational data, deal pipelines, and
  state tracking. Goal: show full-stack execution, relational modeling, and
  clean code, not robotics or games.
- Deliverable: a 2-minute screen-recorded demo plus this GitHub repo. Rough
  outline: 0:00-0:25 stack and architecture, 0:25-1:15 live walkthrough,
  1:15-1:45 code and data layer (schema, a repository/query, the JOIN),
  1:45-2:00 tradeoffs and what comes next.
- Deadline: aim to finish by about 2026-11-10; sooner is better. Unfinished
  roadmap items can be mentioned in the video as future work.
- Target roadmap (not all required): normalized schema, MVC with a router and
  repositories, computed metrics (brew ratio, flow rate, averages), JSON API
  with async search, Docker, and PHPStan/PHP-CS-Fixer in CI.

## Environment

- XAMPP on Linux (Arch). PHP 8.2.12 (checked 2026-11-03). That branch's
  security support ends 2026-12-31 and the patch level is old.
- Decision: do not upgrade XAMPP. Move to Docker later with PHP 8.4 pinned in
  the Dockerfile. Until then, do not expose this XAMPP install to the internet.
- Database changes are applied by hand in phpMyAdmin from files in
  `database/migrations/` (numbered, run in order). `schema.sql` is the older
  full schema and does NOT yet match the live database (column order differs
  and it lacks `coffeeId` on `tblBrew`). Regenerate it from the live DB.
- LazyVim shows PSR-12 style warnings. Follow them: function braces on their own
  line, braces on every `if`, spaces around operators and after casts.

## Code map

- Root pages: `index`, `login`, `logout`, `newUser`, `coffees`, `newBrew`,
  `editBrew`, `brewHistory`, `errors`. `delete/` holds the two delete
  handlers. `includes/` holds `db_connect.php` (git-ignored; template is
  `db_connect.example.php`), `auth.php`, `helpers.php`, and the nav files.
- `includes/helpers.php`: `post()`, `e()` (escape output, use it for all echo),
  `parseBrewTime()`, `formatBrewTime()`, `readBrewInput()` (validates the brew
  form, returns `[params, errors]`; takes the list of the user's coffee IDs).
- Tables: `tblUser`, `tblCoffee` (unique per user + name), `tblBrew`
  (`coffeeId` FK to `tblCoffee`, `ON DELETE CASCADE`).

## Conventions and gotchas

- PDO prepared statements everywhere; every query on user data has
  `userID = :uid` in the WHERE.
- PDO can return IDs as strings. Cast to int before any strict `in_array`.
- Deleting a coffee also deletes its brews (cascade). The confirm dialog on
  the Coffees page must say so (not done yet).
- `declare(strict_types=1)` is not enabled yet: `formatBrewTime(?int)` can
  receive numeric strings from PDO. Add it after checking every caller.
- Git: one descriptive branch per piece of work; commit after each working
  step; merge to `main` with `--no-ff` when a branch is done; tag each
  finished milestone before deleting a branch. Do not merge a schema change
  before the code that uses it works (this was done once: `main` briefly had
  a NOT NULL column the forms did not fill).

## Workflow notes

- Shell is fish: quote globs (`--include="*.php"`). Editor is LazyVim (nvim) and
  flags PSR-12 style issues; fix them.
- Local tools: XAMPP at `/opt/lampp`, so `/opt/lampp/bin/mysqldump`,
  `/opt/lampp/bin/mysql`, `/opt/lampp/bin/php`. The MySQL user in
  `db_connect.php` cannot CREATE DATABASE, so a scratch database for testing a
  restore is not possible until Docker.
- `includes/db_connect.php` holds real credentials. Confirmed 2026-11-09: it is
  git-ignored (`.gitignore` line 2), not tracked, and never appeared in history
  (`git log --all -- includes/db_connect.php` is empty).
- Git: one descriptive branch per piece of work; commit after each working
  step; merge with `git merge --no-ff -m "<message>" <branch>`; tag
  milestones before deleting the branch. Tags so far:
  `brews-linked-by-coffee-id`, `utf8mb4-schema-cleanup`.
- Schema changes go in numbered files in `database/migrations/`, run by hand in
  phpMyAdmin. The matching code must change in the same sitting: do not test
  between the two (a NOT NULL column briefly broke inserts once).
- phpMyAdmin: `DATABASE()` can be `information_schema`, so filter by
  `TABLE_NAME LIKE 'tbl%'`. `SHOW CREATE TABLE` output is truncated; use
  `information_schema` queries instead.
- Push main and tags after each merge; run git fetch before starting work, since GitHub’s web interface can add commits.
- PHP-CS-Fixer config is .php-cs-fixer.dist.php at the repo root (@PSR12); always dry-run (--dry-run --diff) before fixing, because templates mix PHP and HTML.

## Demo talking points (for the 2-minute video)

- Normalized relational model: brews reference coffees by foreign key, with
  `ON DELETE CASCADE`; the history page and brew counts use JOINs.
- Migrations as version-controlled SQL; `schema.sql` regenerated from the live DB.
- PDO prepared statements (real, not emulated), `userID` scoping on every query,
  output escaped with `e()`, input validated in one function (`readBrewInput`).
- utf8mb4 end to end (tables and connection).
- Found and fixed a real bug during the refactor: the delete-coffee warning
  claimed brews would stay, which stopped being true after the cascade.
- Honest "next" list: Docker with a pinned PHP, CI, metrics dashboard, API.
- Metrics computed in SQL (aggregates, GROUP BY, JOIN) with a whitelisted date filter.

## Status

- [x] Brews linked to coffees by foreign key (`coffeeId`); `coffeeType` dropped
- [x] New/edit brew, brew history and coffees pages use `coffeeId` (JOINs)
- [x] Tables on utf8mb4; PDO connection on utf8mb4 with explicit options
- [x] `schema.sql` regenerated from the live database
- [x] Demo-data seeder (`database/seeds/DemoSeeder.php`), see Next steps
- [x] Style/security review: all files reviewed (see the 2026-11-09 entry)
- [ ] CSRF tokens on POST forms (branch `csrf-tokens`, in progress): helpers and both delete forms done; see the 2026-11-09 CSRF entry for what is left
- [ ] Replace browser `confirm()` popups with a styled `<dialog>` modal (keep the
      cascade warning on coffee delete)
- [ ] `declare(strict_types=1)` after checking callers (PDO ints vs `?int` params)
- [x] Metrics dashboard: KPI cards, by roast, by coffee, date range filter
      (by process and charts not done)
- [ ] Router and repositories (MVC), JSON API, async search
- [ ] Docker (PHP 8.4 pinned) + clean-install test of `schema.sql`; CI with
      PHPStan and PHP-CS-Fixer
- [ ] README refresh and new screenshots (`docs/screenshots` are from before
      the refactor); demo video

## Future features

- Change the favicon to a coffee-themed icon.
- Transition to a mobile app (the JSON API is the first step).
- Optional schema tightening: `roast` as ENUM, drop NOT NULL on optional text
  columns, add `createdAt`/`brewDate` precision if the dashboard needs it.
- Sortable table columns on the metrics page (click a header to toggle). Do it
  server-side with `?sort=` and `?dir=` mapped through a whitelist array to a
  fixed `ORDER BY` fragment, since column names cannot be bound parameters.
- Optional: fix or delete the early test brews with implausible `totalSeconds`.

## Next steps (in order)

1. Review the files listed in Status for lint and security, one at a time.
2. Metrics queries and dashboard.
3. Docker, then CI, then (if time) router/repositories and the API.

- New chat: paste this file first, plus the file being edited.

## Changelog

### 2026-11-09: CSRF tokens, part 1 (branch `csrf-tokens`, not merged yet)

- **Why:** the delete handlers pass every existing check (POST only, logged in,
  `userID` scoped) when another site auto-submits a hidden form with the user's
  session cookie. Tokens prove the user actually loaded our form.
- **Helpers** in `includes/helpers.php`: `csrfToken()` (one random token per
  session, `bin2hex(random_bytes(32))`), `csrfField()` (hidden input, goes in
  every POST form) and `csrfValid()` (`is_string()` guard, then
  `hash_equals()`, so a posted `csrfToken[]` cannot throw).
- **Wired so far:** the delete forms in `brewHistory.php` and `coffees.php`, and
  the matching checks in `delete/deleteBrew.php` and `delete/deleteCoffee.php`.
  A failed check sets `errMessage` ("Your form expired...") and redirects to
  `errors.php`. The check goes after the POST/id guard and before the `try`.
- **Tested by hand:** both deletes work normally; editing the hidden
  `csrfToken` value to `bad` in dev tools lands on the error page and nothing
  is deleted.
- **Not done yet, in this order:**
  1. Add-coffee form and POST branch in `coffees.php`.
  2. `newBrew.php`.
  3. `editBrew.php`. Guess, check the file first: it probably takes a POST from
     the Edit button in `brewHistory.php` to load the brew and a POST to save
     it. The save needs a token; the Edit button form in `brewHistory.php` may
     need one too, depending on how the page tells the two apart.
  4. `login.php` and `newUser.php`. These need tokens as well ("login CSRF"),
     even though nobody is logged in. The session already exists from
     `db_connect.php`.
  5. Logout: change the link in both nav files to a small POST form with a
     token, and make `logout.php` check it. Closes the logout-by-GET gap.
  6. Optional second layer: set `SameSite=Lax` explicitly where the session
     starts (`includes/db_connect.php` line 22, before `session_start()`).
  7. Final check: grep every `method="post"` and `method="POST"` form against
     `csrfField` so none are missed. Then merge with `--no-ff` and tag.
- **Talking point:** tokens plus `SameSite` cookies is defense in depth, since
  modern browsers already default session cookies to `Lax` but tokens do not
  depend on browser behavior.
- **Gotcha:** the token must be in the form of any `<dialog>` modal later, so
  the modal work comes after this.

### 2026-11-09: Style/security review, part 2 (branch `review-untouched-files`)

- **Reviewed:** `logout.php`, `index.php`, `errors.php`, `delete/deleteBrew.php`,
  `includes/brewNav.php`, `includes/signInNav.php`. This finishes the review.
- **logout.php:** also clears the session cookie after `session_destroy()`.
  Logout is still a GET, so it can be triggered cross-site (nuisance only);
  becomes a POST form with the CSRF work.
- **index.php:** removed a stray docblock, `==` to `===`, red inline style
  replaced by the `.error` class, viewport tag no longer blocks zoom, added
  the missing `</main>`. Confirmed `auth.php` sends `error=falseLoginStatus`
  and the message shows.
- **errors.php:** `errMessage` is read and cleared at the top instead of an
  `unset` after `</html>`; the `<p>` wrapper (invalid around `<h3>`) is now a
  `<div>`. It shows `brewNav.php` when logged in and `signInNav.php` otherwise,
  which fixed the unstyled links (the old hand-written `<nav>` had no
  `.brew-nav` class; `login.php` now stores only the heading).
- **Rule:** `errMessage` holds trusted HTML and is echoed raw. A grep of every
  assignment showed only fixed strings. Never put user input in it.
- **delete/deleteBrew.php:** `is_string()` guard before `ctype_digit()` (a
  posted `brewID[]` is an array), redirect moved out of the `try`, style fixes.
  Already correct: POST only, `userID` in the WHERE, prepared statement.
- **brewNav.php:** doc comment now lists `metrics`. Every nav page sets
  `$activePage` correctly. `signInNav.php` needed no change (guests see
  Logout and Coffees links; cosmetic).
- **Known gaps:** no CSRF tokens (matters most for the delete handlers), no
  login rate limiting, logout via GET.
- **Merged and tagged:** `style-security-review`.

### 2026-11-05: Style/security review, part 1 (branch `review-untouched-files`)

- **Reviewed so far:** `auth.php`, `login.php`, `newUser.php`.
- **auth.php:** PSR-12 brace placement, single-quoted `Location:` header.
  No security change. Note: `requireLogin()` relies on `db_connect.php` having
  started the session, so it must be required first.
- **login.php:** removed stray docblock, added `is_string()` guard on the
  password (a posted `password[]` made `password_verify()` throw a TypeError),
  single quotes, `(int)` cast spacing, closing `</main>`, and the viewport tag
  no longer blocks pinch zoom. Already correct: prepared query, bcrypt verify,
  `session_regenerate_id(true)`, same error for bad user or bad password.
- **newUser.php:** same `is_string()` guard, password length now 8 to 72
  (bcrypt ignores bytes past 72), SQLSTATE compared as the string `'23000'`,
  `echo` HTML block replaced by plain HTML with `e()` and the `.error` class,
  nav moved inside `<main class="center">`.
- **Gotcha:** a half-applied edit left an old block and a dangling `echo('`
  in `newUser.php`; `/opt/lampp/bin/php -l newUser.php` caught it. Run `php -l`
  after every large edit.
- **Not done (known gaps, good "next" talking points):** no login rate
  limiting, unknown usernames respond slightly faster than wrong passwords,
  no CSRF tokens yet.
- **Still to review:** `logout.php`, `index.php`, `errors.php`,
  `delete/deleteBrew.php`, `includes/brewNav.php`, `includes/signInNav.php`.
  Also check that `index.php` shows the `error=falseLoginStatus` message and
  that `errors.php` echoes `errMessage` as intended.
- **Tested by hand:** the four browser tests for `newUser.php` (signup, duplicate,
  short password, form loads) passed, and `php -l` is clean.

### 2026-11-05: Metrics dashboard (branch `metrics-dashboard`)

- **Change:** new `metrics.php` with a date range filter (30 days, 90 days, all
  time), four KPI cards (brews, avg rating, avg ratio, avg flow), a "By roast"
  table and a "By coffee" table. Aggregation is done in SQL (`COUNT`, `AVG`,
  `GROUP BY`, JOIN `tblBrew` to `tblCoffee`), scoped by `userID` like every
  other query. Metrics link added to `includes/brewNav.php`.
- **Definitions:** ratio = `waterGrams / coffeeGrams` (shown as 1:x). Flow rate =
  `waterGrams / totalSeconds` in g/s, bloom included. Both use `NULLIF` for
  zero divisors, and `AVG` skips NULL `waterGrams`.
- **Range filter:** `$_GET['range']` is checked against a whitelist array and
  falls back to 30. "All time" uses `1970-01-01` as the since-date, because
  `EMULATE_PREPARES => false` does not allow the same named parameter twice in
  one query (so no `:since IS NULL OR ...`).
- **Checked against the seeded data:** all-time roast counts 25/8/27 and ratings
  match the seeder entry; by-coffee brew counts sum to 60.
- **Empty state:** both tables show "No brews in this range."; KPI cards show
  dashes for averages.
- **Gotchas:**
  - PHP turns numeric array keys like `'30'` into ints, so comparing with
    `$range` needs a `(string)` cast.
  - Averages over 1 or 2 brews are noisy (Sumatra: 4.00 from one brew), so the
    Brews column stays next to the rating.
  - Early test users (6 and 8) have `totalSeconds` of 25000 to 31000. My guess
    is an old version stored times with the colon removed. `parseBrewTime()` is
    correct. Left alone, since the dashboard only shows the logged-in user.
  - One commit rewrote `assets/coffee.css` wholesale (716 changed lines): an
    editor formatter on save. Content is fine, the diff is just noisy.
- **Still open:** no "by process" breakdown, no chart, no sortable columns.

### 2026-11-05: Demo-data seeder (branch `demo-seeder`)

- **Change:** `database/seeds/DemoSeeder.php` creates a `demo` user (password
  `demo-password-123`, hashed with bcrypt cost 12 like `newUser.php`), 12
  coffees from 11 roasters, and 60 brews over the last 90 days.
- **Run:** `/opt/lampp/bin/php database/seeds/DemoSeeder.php` from the repo root.
  CLI only (it exits when run through a web server).
- **Design:** one PDO transaction, so it is all-or-nothing. Re-runnable: it
  deletes the old `demo` user first and the `ON DELETE CASCADE` foreign keys
  remove that user's coffees and brews. `mt_srand(2026)` makes the data
  identical on every run, so screenshots and the video stay reproducible.
  Ratings, grind, water temperature and notes follow the coffee's roast, so
  the dashboard has real differences to show.
- **Check:** average rating by roast on the seeded data was Light 4.16 (25
  brews), Medium 3.59 (27), Dark 3.88 (8). The small Dark sample explains why
  it is above Medium.
- **Note:** `userID` keeps increasing on each run (AUTO_INCREMENT), which is
  harmless. The demo password is a local development credential only.

### 2026-11-03: Regenerate schema.sql (branch `regenerate-schema-sql`)

- **Change:** `schema.sql` now matches the live database (including `coffeeId` on
  `tblBrew`, no `coffeeType`, utf8mb4). Generated with:
  `mysqldump -u nico -p --no-data --compact wp_miniSite tblUser tblCoffee tblBrew`
  piped through `sed` to strip `AUTO_INCREMENT=` counters and the `/*!40101*/`
  lines, and to add `IF NOT EXISTS`.
- **Note:** `text` columns show as `mediumtext` because `CONVERT TO utf8mb4`
  widens them. Column order is the live order (`userID` last), which is
  cosmetic.
- **Not tested:** loading `schema.sql` into a fresh database. The `nico` MySQL
  user cannot create databases. Test it for real when Docker is set up.
- **Still open:** looser types than ideal (`roast` is varchar not ENUM, some text
  columns NOT NULL). Fix in a later migration if time allows.

### 2026-11-03: Schema cleanup (branch `schema-cleanup`)

- **Found by:** dumping the live schema with `mysqldump --no-data` and
  comparing it to `schema.sql`. The live database had drifted from the repo.
- **Migrations** (run by hand in phpMyAdmin, in order):
  - `004_tidy_coffee_table.sql`: dropped the unused `tblCoffee.numBrews`
    column (grep confirmed nothing read it) and a duplicate unique index
    (`userID_2`).
  - `005_convert_to_utf8mb4.sql`: converted `tblUser`, `tblCoffee` and
    `tblBrew` from latin1 to `utf8mb4_unicode_ci`.
- **Connection:** `includes/db_connect.php` now sets `charset=utf8mb4` and PDO
  options (exception errors, associative fetch, real prepared statements with
  `EMULATE_PREPARES => false`). Without the charset, the connection would
  still have talked latin1 to the converted tables.
- **Gotcha:** `information_schema` queries run in phpMyAdmin may have
  `DATABASE()` set to `information_schema`. Filter by table name
  (`WHERE TABLE_NAME LIKE 'tbl%'`) instead.
- **Tested by hand:** accented coffee name saves and displays correctly;
  brew history, edit, and new brew still work after the fetch-mode change.
- **Still open:** `schema.sql` still describes the old schema and needs to be
  regenerated.

### 2026-11-03: Drop brews.coffeeType (branch `brews-use-coffee-id`)

- **Change:** `database/migrations/003_drop_brew_coffee_type.sql` drops
  `tblBrew.coffeeType`. `newBrew.php` and `editBrew.php` no longer write it.
  A repo-wide grep confirmed nothing else read it.
- **Order matters:** the column was NOT NULL with no default, so the code had to
  stop writing it and the migration had to run back to back, with no testing in
  between.
- **Tested by hand:** add brew, edit brew and change its coffee, existing brews
  still show the right names.
- **Still open:** `schema.sql` is out of date (still has `coffeeType`, lacks
  `coffeeId`). Regenerate it from the live database.

### 2026-11-03: PHP pages use coffeeId (branch `brews-use-coffee-id`)

- **Problem:** after the schema change, `coffeeId` is NOT NULL but the forms
  still posted and validated a coffee name.
- **Change:**
  - `includes/helpers.php`: `readBrewInput()` now takes a list of the user's
    coffee IDs, validates `coffeeId` as an int that must be in that list, and
    returns `:coffeeId`. This also stops a user logging a brew against
    another user's coffee.
  - `newBrew.php` and `editBrew.php`: the dropdown submits `coffeeId` (label
    is the coffee name); INSERT and UPDATE write `coffeeId`.
  - Style pass for LazyVim/PSR-12: braces on their own lines, braces on every
    `if`, spacing, `echo` HTML block replaced by plain HTML, and the
    `options()` helper in `editBrew.php` replaced by inline loops.
- **Gotcha:** PDO can return IDs as strings, so IDs are cast to int before the
  strict `in_array` check. Without the cast every coffee fails validation.
- **Temporary:** INSERT and UPDATE still write `coffeeType` (copied from the
  coffee's name) because it is NOT NULL and `brewHistory.php` still reads it.
- **Tested by hand:** add brew, failed validation keeps the selected coffee,
  edit rating only, switch a brew to another coffee.
- **Also changed:** `brewHistory.php` and `coffees.php` read through `coffeeId`
  (JOINs). The Coffees delete confirm used to say a coffee's brews "stay in
  your history", which stopped being true after the cascade, so it now says the
  brews are deleted too. `delete/deleteCoffee.php` got style fixes and a comment.

### 2026-11-03: Link brews to coffees (branch `link-brews-to-coffees`)

- **Problem:** `tblBrew` referenced a coffee only by the free-text column
  `coffeeType`, so a brew was not tied to a real row in `tblCoffee`.
- **Change:** added `tblBrew.coffeeId` (INT NOT NULL) with foreign key
  `fk_brew_coffee` -> `tblCoffee(coffeeId)`, `ON DELETE CASCADE` (deleting a
  coffee deletes its brews, same pattern as user -> coffee).
- **Migrations** (run by hand in phpMyAdmin, in order):
  - `database/migrations/001_link_brews_to_coffees.sql`: adds `coffeeId` as
    nullable and backfills it by matching `userID` + `coffeeName`.
  - `database/migrations/002_enforce_brew_coffee_fk.sql`: makes `coffeeId`
    NOT NULL and adds the foreign key.
- **Verified:** before migrating, a LEFT JOIN check found 0 brews without a
  matching coffee. After 001, 7 of 7 brews had a `coffeeId`. After 002,
  `information_schema` lists both `fk_brew_coffee` and `fk_brew_user`.
- **Still open:**
  - The live database's column order differs from `schema.sql`, so
    `schema.sql` needs to be regenerated from the real database.
