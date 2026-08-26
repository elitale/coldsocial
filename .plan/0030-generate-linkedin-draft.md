# 0030 — Generate a single LinkedIn draft

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0030` / `generate-linkedin-draft`
- **Branch:** `feature/0030-generate-linkedin-draft` (stacked on 0029)
- **Issue:** #8 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — wants a post written from what she captured
- **Target platform(s):** LinkedIn (single platform first)
- **Depends on:** 0028 (TextGenerator), 0029 (Update capture)

## Context / background

Persona (0003) gives the voice, Updates (#7/0029) give the material, TextGenerator (#34/0028)
does the AI call. This slice joins them: turn one captured update into one LinkedIn draft the
user can read. It introduces the `Post` (draft) model that the library (#10), edit (#11),
approve (#13), schedule (#14) and publish (#19) features all build on.

## User story

> As a **solo founder**, I want **to turn one of my captured updates into a ready LinkedIn post
> with one click**, so that **I get a usable draft without staring at a blank page**.

## Problem / pain (in the user's words)

> "I jotted the news down — now just write the post for me in my voice so I can tweak and go."

## In scope — the smallest valuable slice

- A **Generate post** button on each update (Updates page).
- Clicking it builds a prompt from the author's **persona voice + the update** and calls
  `TextGenerator`, then stores the result as a LinkedIn **draft** (`Post`).
- Redirects to a **draft view** (`/posts/{post}`) showing the generated text.
- If no text model is configured, the action fails gracefully with an inline error (no draft).

### Screens, states & UX

- **Updates page:** each update gains a "Generate post" button (shows "Generating…" while busy);
  an inline error appears if generation can't run.
- **`/posts/{post}` (posts.show):** the draft body, a platform badge, a link back to the source
  update, and "Back to updates". Themed shadcn (Card, Badge, Button), light + dark.

## Deliberately out of scope (YAGNI)

- Listing/managing drafts (#10), editing (#11), regenerating/tweaking (#12), approving (#13).
- A week of drafts (#9) — this is exactly one.
- Post status/approval state — every post is a draft until #13 adds a status column.
- Queued/async generation — synchronous is fine for one short draft.
- Other platforms — LinkedIn only; a `platform` string keeps the door open without an enum yet.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] Given the user has an update and a default text model, when they click Generate, then a
      LinkedIn `Post` is created with the model's text and they land on the draft view.
- [ ] The prompt includes the update's text (so the draft is about the update).
- [ ] Given no text model is configured, when they click Generate, then they return with an
      inline error and no post is created.

**Safety / trust**

- [ ] A user can only generate from their own update (cross-user → 404) and only view their own
      draft (cross-user → 403). Guests are redirected to login. Nothing is published.

**Quality**

- [ ] Themed shadcn components (light + dark). `composer ci:check` green.

## Data & backend

- **Model / migration:** `Post` — `user_id` (FK cascade, indexed), `update_id` (FK, nullable,
  nullOnDelete — provenance survives update deletion), `platform` (string, default `linkedin`),
  `body` (text), timestamps.
- **Relationships:** `User hasMany Post`; `Post belongsTo User`; `Post belongsTo Update` as
  **`sourceUpdate()`** (named to avoid clobbering Eloquent's `Model::update()`).
- **Action:** `App\Content\GenerateLinkedInDraft::for(Update): Post` — builds the persona+update
  prompt, calls `TextGenerator`, persists the draft. Business logic out of the controller.
- **Validation (`GeneratePostRequest`):** `update_id` required|integer|exists; ownership enforced
  by resolving through `->updates()` (non-owned → 404).
- **Controller:** thin — `store` runs the action (catches `ProviderRequestException` →
  `back()->withErrors('generate')`); `show` authorizes then renders.
- **Routes:** `POST /posts → posts.store`, `GET /posts/{post} → posts.show` (auth, verified).

## Frontend (Inertia v3 + React)

- **Page:** `resources/js/pages/posts/show.tsx`; **button** added to `updates/index.tsx`.
- **Type:** `resources/js/types/post.ts`.
- **Routes:** `@/routes/posts` (Wayfinder). Generation errors surfaced via the form `errors` bag.

## Security & privacy

- All reads/writes scoped to `auth()->user()`. Provider keys stay in `ProviderRequest`; prompts
  contain the user's own persona/update only. No external publish.

## Test plan (Pest — feature, RefreshDatabase, Http::fake)

- `PostGenerateTest`: generates a draft (asserts owner/platform/update_id/body); prompt contains
  the update text (`Http::assertSent`); cross-user update → 404 + nothing sent; no model → inline
  error + no post; owner views draft; non-owner → 403; guest → login.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
