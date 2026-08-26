# 0035 — Generate a week of drafts (5 posts)

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0035` / `generate-week`
- **Branch:** `feature/0035-generate-week` (stacked on 0034)
- **Issue:** #9 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — wants a week of content in one click, not one post at a time
- **Target platform(s):** LinkedIn
- **Depends on:** 0028 (TextGenerator), 0029 (updates), 0030/0034 (`Post` + status)

## Context / background

Single-draft generation (#8) makes one post from one update. Batch generation is the real
time-saver: press one button and get a week's worth of varied drafts to review. This reuses the
persona voice (now extracted to a shared `PersonaVoice`) and recent updates as context, and rotates
through five distinct angles so the posts don't all sound the same.

## User story

> As a **solo founder**, I want **to generate a week of LinkedIn drafts in one click**, so that
> **I have a batch to review instead of writing one at a time**.

## In scope — the smallest valuable slice

- A **Generate a week** button (post library) that creates **5** LinkedIn drafts.
- Each draft uses the persona voice + the user's recent updates as context, with a different
  **angle** (how-to, personal story, opinion, question, behind-the-scenes).
- Lands on the library showing the new drafts. Works even with **no** updates (persona-only).
- Fails cleanly (inline error, nothing created) when no text model is configured.

### Screens, states & UX

- **Library header:** a "Generate a week" button (shows "Generating…"); inline error on failure.
- **Success:** redirect to the library; 5 new draft cards appear.
- **Theming & a11y:** themed shadcn Button; light + dark.

## Deliberately out of scope (YAGNI)

- Choosing how many / which angles / a specific date spread across the week (#14 schedules).
- Regenerating the whole week, or de-duping against existing drafts.
- Async/queued batch generation — 5 sequential calls is fine for one click.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] Given a configured model, when the user generates a week, then **5** LinkedIn drafts are
      created and they land on the library.
- [ ] The prompts include the user's recent update text (when they have updates).
- [ ] A week can be generated with no updates (persona-only).
- [ ] No text model → inline error and **nothing** is created (all-or-nothing).

**Safety / trust**

- [ ] Only the signed-in user's own drafts are created; guests → login. Nothing is published.

**Quality**

- [ ] Themed shadcn (light + dark). `composer ci:check` green.

## Data & backend

- **No new model/migration** — creates `Post` rows (status defaults to `draft`).
- **Extract `App\Content\PersonaVoice::hints(?Persona): list<string>`** from
  `GenerateLinkedInDraft` (now two consumers → DRY).
- **Action `App\Content\GenerateWeeklyDrafts::forUser(User): Collection<Post>`** — 5 angles ×
  (persona voice + recent updates); generates all bodies first, then inserts, so a mid-batch
  failure creates nothing.
- **Controller:** `PostController@week` — run the action (catch `ProviderRequestException` →
  `back()->withErrors('generate')`), redirect to `posts.index`.
- **Route:** `POST /posts/week → posts.week`.

## Frontend (Inertia v3 + React)

- **Library (`posts/index.tsx`):** a "Generate a week" `<Form>` in the header; surface
  `errors.generate`.
- **Routes:** `@/routes/posts` (week) via Wayfinder.

## Security & privacy

- Drafts created via `auth()->user()->posts()`; prompts contain only the user's own persona +
  updates.

## Test plan (Pest — feature, RefreshDatabase, Http::fake)

- `PostWeekTest`: guest → login; generates 5 drafts (all `linkedin`); prompt includes recent
  update text (`Http::assertSent`); works with no updates; no model → error + nothing created.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
