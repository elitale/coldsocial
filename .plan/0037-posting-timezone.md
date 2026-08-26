# 0037 — Posting timezone in settings

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0037` / `posting-timezone`
- **Branch:** `feature/0037-posting-timezone` (off `main`)
- **Issue:** #16 · **PR:** _pending_
- **ICP persona:** any user — "9am" has to mean their 9am
- **Target platform(s):** n/a (a scheduling preference)
- **Depends on:** none (enables #14 scheduling)

## Context / background

Scheduling (#14) and the calendar (#15) need to know the user's timezone so a chosen time means
what they expect. This slice adds a `timezone` to the user, editable on a new **Posting** settings
page. It's a self-contained "good first issue" that unblocks scheduling.

## User story

> As a **user**, I want **to set my posting timezone in settings**, so that **scheduled times and
> the calendar are shown in my local time**.

## In scope — the smallest valuable slice

- A `timezone` on the user (IANA identifier, nullable → treated as UTC).
- A **Posting** settings page with a timezone selector (all IANA zones) + Save.
- A "Posting" entry in the settings nav.

### Screens, states & UX

- **`/settings/posting`:** timezone `<select>` (native, for typeahead over ~425 zones) + Save +
  "Saved" confirmation.
- **Validation:** invalid timezone rejected inline.
- **Theming & a11y:** themed select + shadcn Button/Label; light + dark; labelled field.

## Deliberately out of scope (YAGNI)

- Auto-detecting the browser timezone (could offer later).
- Per-post timezone overrides, quiet hours, or posting-frequency prefs.
- Using the timezone anywhere yet — scheduling (#14) consumes it.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] The posting settings page shows the user's current timezone.
- [ ] Saving a valid timezone persists it.
- [ ] An invalid timezone is rejected inline (value unchanged).

**Safety / trust**

- [ ] Guests are redirected to login. A user only edits their own timezone.

**Quality**

- [ ] Themed (light + dark). `composer ci:check` green.

## Data & backend

- **Migration:** add nullable `timezone` string to `users`.
- **Model:** add `timezone` to `User` fillable + `@property`.
- **Controller `Settings\PostingController`:** `edit` (render with current timezone +
  `timezone_identifiers_list()`), `update` (persist).
- **Validation (`Settings\PostingUpdateRequest`):** `timezone` required + Laravel `timezone` rule.
- **Routes:** `GET /settings/posting → posting.edit`, `PATCH /settings/posting → posting.update`
  (in the existing `auth` settings group).

## Frontend (Inertia v3 + React)

- **Page:** `resources/js/pages/settings/posting.tsx` (AppLayout + SettingsLayout + `<Form>`).
- **Nav:** add "Posting" to `layouts/settings/layout`.
- **Routes:** `@/routes/posting` via Wayfinder.

## Security & privacy

- Settings routes are `auth`-gated; the update writes only the current user's timezone, validated
  against the IANA list.

## Test plan (Pest — feature, RefreshDatabase)

- `PostingSettingsTest`: guest → login; page shows current timezone (`withoutVite`); valid
  timezone persists; invalid timezone rejected (unchanged).

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
