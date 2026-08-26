# 0036 — Onboarding gate (require a completed persona)

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0036` / `onboarding-gate`
- **Branch:** `feature/0036-onboarding-gate` (independent — branched off `main`)
- **Issue:** #25 · **PR:** _pending_
- **ICP persona:** any new user — the app is useless without a persona to write in their voice
- **Target platform(s):** n/a (routing/middleware)
- **Depends on:** 0003 (persona intake, already on `main`)

## Context / background

Persona intake (0003) exists, but nothing forces a new user through it — they can land on an empty
dashboard with no persona, so generation would have no voice to write in. This slice adds a gate:
signed-in users without a **completed** persona are routed to `/onboarding` until they finish it.

## User story

> As a **new user**, I want **to be taken straight to onboarding until my persona is set up**, so
> that **the app works properly the first time I use it**.

## In scope — the smallest valuable slice

- Middleware on the authenticated app routes: if the signed-in user has no persona (or an
  incomplete one, `completed_at === null`), redirect to `onboarding.edit`.
- The onboarding screens themselves are **not** gated (so the user can complete them).
- Once the persona is completed, the app is reachable normally.

## Deliberately out of scope (YAGNI)

- A progress meter / partial-completion resume beyond the existing wizard.
- Gating settings/profile routes (a user may still manage their account).
- Any new UI — this only redirects to the existing onboarding wizard.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] A signed-in user with no persona is redirected to `onboarding.edit` when hitting the app.
- [ ] A signed-in user whose persona is incomplete (`completed_at` null) is likewise redirected.
- [ ] A signed-in user with a completed persona can reach the dashboard.
- [ ] The onboarding page is reachable while a persona is incomplete (not gated).

**Safety / trust**

- [ ] Guests are still sent to login (auth handles them before the persona gate).

**Quality**

- [ ] `composer ci:check` green.

## Data & backend

- **Middleware `App\Http\Middleware\EnsurePersonaIsComplete`:** redirect to `onboarding.edit` when
  `auth user` exists, `persona?->completed_at === null`, and the request isn't already an
  `onboarding.*` route.
- **Registration:** add the middleware to the existing `['auth', 'verified']` route group in
  `routes/web.php` (runs only for authenticated, verified users; avoids a verification/onboarding
  redirect loop).
- **No model/migration** — reads the existing `persona.completed_at`.

## Frontend

- None — reuses the existing onboarding wizard.

## Security & privacy

- Gate only acts on the authenticated user's own persona; guests are unaffected (auth precedes it).

## Test plan (Pest — feature, RefreshDatabase)

- `OnboardingGateTest`: no persona → redirect onboarding; incomplete persona → redirect;
  completed persona → dashboard OK (`withoutVite`); onboarding page not gated (`withoutVite`);
  guest → login.

## Skills to activate during build

laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
