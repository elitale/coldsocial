# 0029 — Capture updates & source material

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0029` / `capture-updates`
- **Branch:** `feature/0029-capture-updates` (stacked on 0028)
- **Issue:** #7 · **PR:** #47
- **ICP persona:** Priya (solo founder) — has news to share but no time to write it up
- **Target platform(s):** n/a (raw material; platform-agnostic)
- **Depends on:** 0028 (branch base); generation (#8) will consume these updates

## Context / background

Persona intake (0003) captured *who* the user is. Generation (#8) needs *what's new* — the raw
updates and source material a post is built from. Nothing captures that yet. This slice adds the
simplest capture surface: a page to jot an update (plus an optional source link) and see the list
of what you've captured. #8 will read the latest update as the seed for a draft.

## User story

> As a **solo founder**, I want **to quickly jot down updates and source links about what I'm
> working on**, so that **the app has fresh material to turn into posts**.

## Problem / pain (in the user's words)

> "Stuff happens all week — a launch, a milestone, a hot take — and by the time I sit down to
> post, I've forgotten half of it. Let me dump it somewhere the moment it happens."

## In scope — the smallest valuable slice

- An **Updates** page (`GET /updates`) listing the user's updates, newest first.
- A form to **capture** an update: a required body (the material) + an optional source URL.
- **Delete** an update you no longer want.
- Sidebar nav entry so the page is reachable.

### Screens, states & UX

- **`/updates` (updates.index):** capture form on top, list below.
- **Empty / first-run:** friendly empty state ("No updates yet — jot down what's new").
- **Error / validation:** inline errors under the body / URL fields.
- **Success:** new update appears at the top of the list; form clears.
- **Theming & a11y:** themed shadcn (Card, Textarea, Input, Button, Label); labels + focus states;
  works light + dark.

## Deliberately out of scope (YAGNI)

- Editing an existing update (capture + delete is enough to be useful; edit is a later slice).
- Tags, categories, attachments/images, rich text, pasted-article scraping.
- Any AI/generation (that's #8) — this only stores material.
- Sharing/visibility — updates are strictly private to the owner.

## Acceptance criteria (testable, user-facing)

**Behaviour / UX**

- [ ] Given a signed-in user, when they submit a non-empty body, then the update is saved and
      shown at the top of their list.
- [ ] Given no updates, when the page loads, then an empty state is shown.
- [ ] Given an update they own, when they delete it, then it disappears from the list.

**Persistence / backend**

- [ ] An update belongs to the creating user; the list and delete are scoped to `auth()->user()`.
- [ ] Body is required (≤ 5000 chars); source URL, if present, must be a valid URL (≤ 2048).

**Safety / trust**

- [ ] A user cannot read or delete another user's updates (403 on cross-user delete).
- [ ] Guests are redirected to login.

**Quality**

- [ ] Screens use themed shadcn components (light + dark).
- [ ] `composer ci:check` is green.

## Data & backend

- **Model / migration:** `Update` — `user_id` (FK, cascade), `body` (text), `source_url`
  (string, nullable), timestamps. Index `user_id`.
- **Relationships:** `User hasMany Update`; `Update belongsTo User`.
- **Validation (Form Request `UpdateStoreRequest`):** `body` required|string|max:5000;
  `source_url` nullable|url|max:2048. `authorize()` returns true (route is auth-gated).
- **Routes (named, Wayfinder):** `GET /updates → updates.index`, `POST /updates → updates.store`,
  `DELETE /updates/{update} → updates.destroy` (all `auth`,`verified`). Regenerate Wayfinder.
- **Controller `UpdateController`:** thin — `index` renders `updates/index` with the user's
  updates; `store` creates via the relationship; `destroy` checks ownership (`abort_unless`) then
  deletes. No service layer (single call sites — KISS).
- **Factory:** `UpdateFactory` with a realistic body and an occasional source URL.

## Frontend (Inertia v3 + React)

- **Page:** `resources/js/pages/updates/index.tsx` via `Inertia::render('updates/index', …)`.
- **Components:** reuse shadcn Card/Textarea/Input/Button/Label + `InputError`; `<Form>` from
  `@inertiajs/react` posting to `store.url()` and `destroy.url({ update })`.
- **Routes:** import from `@/routes/updates` (Wayfinder) — no hardcoded paths.
- **Type:** `resources/js/types/update.ts`.
- **Nav:** add an "Updates" item to `app-sidebar`.

## Security & privacy

- **Authorization:** every query goes through `auth()->user()->updates()`; `destroy` aborts 403
  if the update isn't the user's. Updates are private PII — never shared across accounts.

## Design notes

- Thin controller + Form Request; persistence via the `updates()` relationship. No policy class
  yet — one ownership check inline (KISS); promote to a policy when a second action needs it.

## Test plan (Pest — feature, RefreshDatabase)

- `UpdateTest`: capture saves + scopes to user; empty body rejected; invalid URL rejected; index
  shows only own updates; owner can delete; non-owner gets 403; guest redirected to login.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Suggested build order

1. `make:model Update -mf`, migration columns, `User::updates()`.
2. `UpdateStoreRequest` + `UpdateController` + named routes; regenerate Wayfinder.
3. `updates/index.tsx` page + type + sidebar nav.
4. `UpdateTest`; `composer ci:check` green.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
