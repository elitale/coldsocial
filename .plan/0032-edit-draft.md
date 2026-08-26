# 0032 — Edit a post draft

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0032` / `edit-draft`
- **Branch:** `feature/0032-edit-draft` (stacked on 0031)
- **Issue:** #11 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — the AI draft is close but needs her tweak
- **Target platform(s):** n/a (edits a LinkedIn draft's text)
- **Depends on:** 0030 (`Post`), 0031 (library + draft view)

## Context / background

Generation (#8) gives a draft; the library (#10) lists them. But the draft is rarely perfect —
the user needs to tweak the wording before it moves toward approval (#13). This slice adds a
simple edit form for a draft's body.

## User story

> As a **solo founder**, I want **to edit the text of a generated draft**, so that **it sounds
> exactly how I want before I approve it**.

## In scope — the smallest valuable slice

- **`/posts/{post}/edit` (posts.edit):** a form pre-filled with the draft body.
- **Save** (`PATCH /posts/{post}`) updates the body and returns to the draft view.
- **Edit** button on the draft view; **Cancel** returns without saving.

### Screens, states & UX

- **Edit page:** a large textarea pre-filled with the body; Save + Cancel.
- **Validation:** body required — inline error under the field.
- **Success:** redirect to the draft view showing the updated text.
- **Theming & a11y:** themed shadcn (Card, Textarea, Button); labelled field; light + dark.

## Deliberately out of scope (YAGNI)

- Editing platform/other fields — only the body is editable now.
- Regenerate/tweak-with-instruction (#12) and approve (#13).
- Autosave / revision history / diffing.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] Given a draft they own, when they open edit, then the form is pre-filled with its body.
- [ ] When they save a new body, then it's persisted and they return to the draft view.
- [ ] Empty body is rejected with an inline error (draft unchanged).

**Safety / trust**

- [ ] A user can only edit/update their own draft (cross-user edit + update → 403). Guests → login.

**Quality**

- [ ] Themed shadcn (light + dark). `composer ci:check` green.

## Data & backend

- **No new model/migration** — reuses `Post` (`Model::update()` is intact; the Update relation is
  `sourceUpdate()`).
- **Controller (`PostController`):** `edit` (owner check → render `posts/edit`) and `update`
  (owner check → `$post->update($request->validated())` → redirect to `posts.show`).
- **Validation (`PostUpdateRequest`):** `body` required|string|max:5000.
- **Routes:** `GET /posts/{post}/edit → posts.edit`, `PATCH /posts/{post} → posts.update`.

## Frontend (Inertia v3 + React)

- **Page:** `resources/js/pages/posts/edit.tsx` — pre-filled textarea, Save + Cancel.
- **Draft view:** add an **Edit** button (`posts/show.tsx`).
- **Routes:** `@/routes/posts` (edit/update/show) via Wayfinder.

## Security & privacy

- Both `edit` and `update` `abort_unless` the post belongs to `auth()->user()`.

## Test plan (Pest — feature, RefreshDatabase)

- `PostEditTest`: guest → login; owner sees pre-filled form; owner updates body; empty body
  rejected; non-owner edit + update → 403.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
