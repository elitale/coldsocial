# 0033 — Regenerate / tweak a draft with an instruction

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0033` / `regenerate-draft`
- **Branch:** `feature/0033-regenerate-draft` (stacked on 0032)
- **Issue:** #12 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — "almost right, just make it punchier"
- **Target platform(s):** n/a (rewrites a LinkedIn draft's text)
- **Depends on:** 0028 (TextGenerator), 0030 (`Post`), 0031/0032 (view + edit)

## Context / background

The user can generate (#8) and hand-edit (#11) a draft. But often they don't want to rewrite it
themselves — they want to tell the AI "make it shorter" or "add a CTA" and have it redo the post.
This slice adds an instruction-driven rewrite on the draft view.

## User story

> As a **solo founder**, I want **to tell the AI how to change a draft and have it rewrite the
> post**, so that **I can dial it in without writing it myself**.

## In scope — the smallest valuable slice

- On the draft view: an **instruction** field + **Regenerate** button.
- Submitting sends the current body + the instruction to `TextGenerator`; the returned text
  **replaces** the draft body; the view refreshes.
- Fails gracefully (inline error) when no text model is configured.

### Screens, states & UX

- **Draft view:** a "Tweak this draft" card — instruction input + Regenerate (shows "Rewriting…").
- **Validation:** instruction required — inline error.
- **Error:** no model → inline error; draft unchanged.
- **Success:** the draft card shows the revised text (form clears).
- **Theming & a11y:** themed shadcn (Card, Input, Label, Button); labelled field; light + dark.

## Deliberately out of scope (YAGNI)

- Keeping the old version / revision history / undo (edit #11 is the manual fallback).
- Multiple variations at once, tone presets, or suggested-instruction chips.
- Regenerate-from-scratch off the source update — this rewrites the current body.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] Given a draft they own and a configured model, when they submit an instruction, then the
      body is replaced with the model's output and they return to the draft view.
- [ ] The model prompt includes both the instruction and the current body.
- [ ] Empty instruction is rejected inline (draft unchanged).
- [ ] No text model → inline error, draft unchanged.

**Safety / trust**

- [ ] Only the owner can regenerate (cross-user → 403); guests → login. Nothing is published.

**Quality**

- [ ] Themed shadcn (light + dark). `composer ci:check` green.

## Data & backend

- **No new model/migration** — updates `Post.body` in place.
- **Action `App\Content\RewriteDraft::apply(Post, string $instruction): void`** — builds a
  "revise this post per the instruction" prompt, calls `TextGenerator`, updates the body.
- **Validation (`RegeneratePostRequest`):** `instruction` required|string|max:500.
- **Controller:** `PostController@regenerate` — owner check, run the action (catch
  `ProviderRequestException` → `back()->withErrors('regenerate')`), redirect to `posts.show`.
- **Route:** `POST /posts/{post}/regenerate → posts.regenerate`.

## Frontend (Inertia v3 + React)

- **Draft view (`posts/show.tsx`):** add a "Tweak this draft" `<Form>` (instruction input +
  Regenerate) posting to `posts.regenerate`; surface `errors.instruction` + `errors.regenerate`.
- **Routes:** `@/routes/posts` (regenerate) via Wayfinder.

## Security & privacy

- `abort_unless` the post belongs to `auth()->user()`. Prompt contains only the user's own draft.

## Test plan (Pest — feature, RefreshDatabase, Http::fake)

- `PostRegenerateTest`: guest → login; owner regenerates (body replaced); prompt contains
  instruction + current body (`Http::assertSent`); instruction required (nothing sent, unchanged);
  no model → inline error, unchanged; cross-user → 403, nothing sent.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
