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

## Changelog

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
- **Still open:** `brewHistory.php` and `deleteCoffee.php` have not been
  checked yet; once `brewHistory.php` reads through a JOIN, drop `coffeeType`.
  Fix the `schema.sql` drift (needs regenerating from the live database).

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
  - `coffeeType` still exists because the PHP pages read it. Next step is
    switching the pages to `coffeeId`, then dropping the column.
  - The live database's column order differs from `schema.sql`, so
    `schema.sql` needs to be regenerated from the real database.
