# Devlog for Coffee Tracker

## Goal

- This is an app that is able to track pour over history. I wanted an app where i can store all my favorite coffees. What went right, and what went wrong.

## Stack

- PHP 8.x, PDO, MySQL/MariaDB, XAMPP, vanilla HTML/CSS

## Features

- create users, log in/out, add/delete different beans, add/delete different brews, view brew history

## How to work with me (instructions for any assistant)

- I want to write out all the code to better understand the codebase and learn as i go
- one step at a time to prevent backtracking and multiple errors at once
- reminders on the best time to commit, merge, branch and tag

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

## Status

- [x] Brews linked to coffees by foreign key (`coffeeId`)
- [x] New/edit brew forms and brew history use `coffeeId` (history uses a JOIN)
- [ ] Check `coffees.php`; update the delete confirm to warn about cascade
- [ ] Drop `tblBrew.coffeeType`, and stop writing it in `newBrew`/`editBrew`
- [ ] Regenerate `schema.sql`; add a seeder with demo data
- [ ] Style pass on the remaining PHP files
- [ ] CSRF tokens on POST forms
- [ ] Metrics dashboard (ratio, flow rate, averages by roast and process)
- [ ] Router and repositories (MVC), JSON API, async search
- [ ] Docker (PHP 8.4), CI with PHPStan and PHP-CS-Fixer
- [ ] README refresh, screenshots, demo video
- [ ] Replace the browser `confirm()` popups (delete brew, delete coffee) with a styled in-page modal, using the native `<dialog>` element. It should keep the cascade warning on coffee delete.

## Next steps (in order)

1. `coffees.php`: warn about cascade in the delete confirm.
2. Remove the temporary `coffeeType` column (migration 003 plus code).
3. Regenerate `schema.sql`, then build the demo-data seeder.
4. Metrics queries and dashboard.

- New chat: paste this file first, plus the file being edited.

## Changelog

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
