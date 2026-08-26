# 0031 — Post library: list & manage drafts

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0031` / `post-library`
- **Branch:** `feature/0031-post-library` (stacked on 0030)
- **Issue:** #10 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — wants to see everything she's generated in one place
- **Target platform(s):** n/a (lists LinkedIn drafts today)
- **Depends on:** 0030 (`Post` model + draft view)

## Context / background

#8 (0030) generates one draft and shows it, but there's no way to see all your drafts or clean
up ones you don't want. This slice adds the library: a list of your drafts with view + delete.
Edit (#11), regenerate (#12) and approve (#13) build on this list next.

## User story

> As a **solo founder**, I want **to see all my generated drafts in one place and delete the
> ones I don't want**, so that **I can manage my content pipeline**.

## In scope — the smallest valuable slice

- **`/posts` (posts.index):** the user's drafts, newest first — platform badge, date, body
  preview; each links to the draft view (#8's `posts.show`).
- **Delete** a draft from the list.
- **Empty state** when there are no drafts.
- **Posts** entry in the sidebar nav.

### Screens, states & UX

- **Posts page:** heading + list of draft cards; click a card → draft view; Delete per card.
- **Empty / first-run:** "No drafts yet — generate one from an update."
- **Theming & a11y:** themed shadcn (Card, Badge, Button); light + dark; keyboard-navigable links.

## Deliberately out of scope (YAGNI)

- Editing (#11), regenerate/tweak (#12), approve (#13), scheduling (#14).
- Filtering / search / pagination / bulk actions — the list is small; add when it isn't.
- Status columns / tabs (drafts vs approved) — no status field until #13.
- Empty-state copy polish (#27 owns the deeper polish).

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] Given the user has drafts, when they open `/posts`, then their drafts are listed newest first.
- [ ] Given no drafts, when they open `/posts`, then an empty state is shown.
- [ ] Given a draft they own, when they delete it, then it's gone from the list.

**Safety / trust**

- [ ] The list shows only the user's own drafts; delete is owner-only (cross-user → 403); guests
      → login.

**Quality**

- [ ] Themed shadcn (light + dark). `composer ci:check` green.

## Data & backend

- **No new model/migration** — reuses `Post`.
- **Controller (`PostController`):** add `index` (renders `posts/index` with
  `user()->posts()->latest()->get()`) and `destroy` (owner check via `abort_unless`, delete,
  redirect to `posts.index`).
- **Routes:** `GET /posts → posts.index`, `DELETE /posts/{post} → posts.destroy` (auth, verified).

## Frontend (Inertia v3 + React)

- **Page:** `resources/js/pages/posts/index.tsx` — draft cards (Badge + date + `line-clamp` body
  preview), each linking to `posts.show`; per-card delete `<Form>`; empty state.
- **Nav:** add "Posts" to `app-sidebar`.
- **Routes:** `@/routes/posts` (index/show/destroy) via Wayfinder.

## Security & privacy

- All reads/deletes scoped to `auth()->user()`; `destroy` aborts 403 for non-owners.

## Test plan (Pest — feature, RefreshDatabase)

- `PostLibraryTest`: guest → login; index lists only the user's drafts newest-first; owner can
  delete; non-owner delete → 403.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
