# 0034 — Approve a draft (approval gate)

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0034` / `approve-draft`
- **Branch:** `feature/0034-approve-draft` (stacked on 0033)
- **Issue:** #13 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — nothing goes out that she hasn't blessed
- **Target platform(s):** n/a (marks a LinkedIn draft ready)
- **Depends on:** 0030 (`Post`), 0031/0032/0033 (view/edit/tweak)

## Context / background

Drafts can be generated, edited and tweaked — but there's no explicit "this one is ready" signal.
Scheduling (#14) and publishing (#19) must only act on posts the user has blessed. This slice
introduces a `status` on `Post` (draft → approved) and the approve/unapprove actions. The gate is
enforced by scheduling/publishing when those land; here we establish the state + the control.

## User story

> As a **solo founder**, I want **to approve a draft when it's ready (and unapprove if I change my
> mind)**, so that **only content I've blessed can ever be scheduled or published**.

## In scope — the smallest valuable slice

- A `status` on `Post`: **draft** (default) or **approved**.
- **Approve** a draft; **Unapprove** an approved post (toggle) — from the draft view.
- Status shown as a badge on the draft view and in the library.

### Screens, states & UX

- **Draft view:** a status badge (Draft/Approved); **Approve** button when a draft, **Unapprove**
  when approved.
- **Library:** each card shows its status badge.
- **Theming & a11y:** themed shadcn Badge/Button; light + dark.

## Deliberately out of scope (YAGNI)

- Publishing / scheduling themselves (#14, #19, #20) — this only sets the flag they'll require.
- Auto-reverting to draft when an approved post is edited/tweaked — explicit toggle only for now.
- Extra statuses (scheduled/published) — added by their own features.
- Filtering the library by status.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] A newly generated post has status `draft`.
- [ ] Given a draft they own, when they approve it, then its status becomes `approved`.
- [ ] Given an approved post they own, when they unapprove it, then its status returns to `draft`.
- [ ] The library and draft view expose each post's status.

**Safety / trust**

- [ ] Only the owner can approve/unapprove (cross-user → 403); guests → login.

**Quality**

- [ ] Themed shadcn (light + dark). `composer ci:check` green.

## Data & backend

- **Migration:** add `status` string (default `draft`) to `posts`.
- **Enum:** `App\Enums\PostStatus` (`Draft`, `Approved`).
- **Model:** cast `status` → `PostStatus`; not fillable (set only in the approve/unapprove actions
  — never from user input).
- **Factory:** default `Draft` + an `approved()` state.
- **Controller:** `approve` / `unapprove` — owner check, set status, redirect to `posts.show`.
- **Routes:** `POST /posts/{post}/approve → posts.approve`, `POST /posts/{post}/unapprove →
  posts.unapprove`.

## Frontend (Inertia v3 + React)

- **Draft view (`posts/show.tsx`):** status badge + Approve/Unapprove toggle form.
- **Library (`posts/index.tsx`):** status badge per card.
- **Type:** add `status` to `resources/js/types/post.ts`.
- **Routes:** `@/routes/posts` (approve/unapprove) via Wayfinder.

## Security & privacy

- `abort_unless` owner on both actions. `status` is never mass-assignable (set via a typed enum in
  the controller), so no user can flip their own or others' status through the edit form.

## Test plan (Pest — feature, RefreshDatabase)

- `PostApproveTest`: new post is `draft`; owner approves → `approved`; owner unapproves → `draft`;
  cross-user approve → 403; guest → login; library exposes `status`.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
