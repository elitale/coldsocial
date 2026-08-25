# Project Memory — coldsocial

> Append-only shared memory for the agent loop. Every agent **reads this before acting** and
> **appends after acting**, so context compounds across features instead of resetting. Keep
> entries short and factual. Newest entries go at the **top** of each section.

## How to use this file

- **Feature ledger** — one row per feature as it moves through the loop. Update the row's
  status in place; add a new row per feature.
- **Decisions** — durable choices that outlive a single feature (a chosen pattern, a rejected
  approach, a standing constraint). Add; do not rewrite history — supersede with a new entry.
- **Open questions** — anything blocked awaiting a human or another agent.

Statuses: `Discovered` → `Scoped` → `Building` → `InReview` → `Merged` → `Validated`
(or `Rejected`).

---

## Feature ledger

| # | Feature | Status | Plan | Issue | PR | Owner (agent) | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 0002 | Project foundation: theme, shadcn/ui, auth | InReview | [plan](../.plan/0002-project-foundation-theme-shadcn-auth.md) | [#1](https://github.com/elitale/coldsocial/issues/1) | [#2](https://github.com/elitale/coldsocial/pull/2) | Product Developer | Implemented; `composer ci:check` green (30 tests, 62 assertions); PR ready for review |

---

## Decisions

- **2026-08-25 — CI + tests run on Postgres (feature 0002).** Superseding the earlier SQLite
  test setup: CI spins up a `postgres:18-alpine` service and the suite runs against a
  dedicated `testing` database (never dev `laravel`), matching the Sail/prod engine. The test
  DB is pinned via `<env>`/`<server>` entries in `phpunit.xml` (pgsql @ `127.0.0.1:5432`, db
  `testing`, `sail`/`password`) so the exported `APP_ENV=local` / `DB_CONNECTION=sqlite` shell
  vars can't redirect it. Local runs use the Sail-provisioned `testing` DB
  (`create-testing-database.sql`), so `php artisan test` requires the Sail Postgres to be up.
- **2026-08-25 — Foundation build choices (feature 0002).** (1) Header-based app shell
  instead of porting the full ~700-line shadcn sidebar — KISS; sidebar can be a later
  enhancement. (2) Settings password routes named `user-password.*` to avoid colliding with
  Fortify's `password.update` (reset-password) route. (3) Lean UI deps: native elements where
  a Radix primitive wasn't essential (only `@radix-ui/react-slot` + `react-dropdown-menu`).
  (4) Test env is forced via `<server>` entries in `phpunit.xml` because this Sail shell
  exports `APP_ENV=local` into `$_SERVER`, which Laravel reads before PHPUnit's `<env>` —
  without this, CSRF stays active in tests and every write request 419s.
- **2026-08-25 — Auth stack = Laravel Fortify (headless).** For feature 0002 we back
  authentication with Fortify rather than hand-rolling or using a full UI kit: it's the
  official headless backend the Laravel React starter kit uses, pairs cleanly with Inertia
  pages, and leaves us owning the React UI. Two-factor and social sign-in are deferred (YAGNI).
- **2026-08-25 — Agent loop + standards bootstrapped.** Established the ICP → Product Owner →
  Product Developer loop, the `.plan/` convention (one feature = one plan = one branch = one
  PR = one issue), and the SOLID + YAGNI + KISS principle set (tie-break KISS → YAGNI →
  SOLID). Tech conventions remain owned by `AGENTS.md` and `.agents/skills/**`; this loop owns
  product scope and process.

---

## Open questions

- _None yet._
