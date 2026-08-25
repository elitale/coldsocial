# 0001 — Generate a week of LinkedIn post drafts

> **EXAMPLE ONLY.** This plan is illustrative — it shows the expected shape of a real plan and
> is not an active feature. Delete or supersede it once the loop produces its first real plan.

- **Status:** Discovered (example)
- **Branch:** `feature/0001-generate-linkedin-week`
- **Issue:** #<tbd>
- **PR:** #<tbd>
- **ICP persona:** Priya — the solo entrepreneur / founder

## User story

> As **Priya (solo founder)**, I want **to generate a week of LinkedIn post drafts from my
> recent updates**, so that **I stay visible without writing anything from scratch**.

## Problem / pain (in the user's words)

"It's Monday and I have fifteen minutes. I know I should be posting on LinkedIn, but I stare at
a blank box and give up. By the time I think of something, the week's gone."

## In scope (the smallest valuable slice)

- From Priya's already-saved profile + recent updates, generate **5 LinkedIn draft posts**.
- Drafts are shown for review and are **editable**; nothing is published or scheduled yet.

## Deliberately out of scope (YAGNI)

- Other platforms (TikTok, Instagram, YouTube, Facebook) — later slices behind the same
  content-generation contract.
- Publishing, scheduling, and calendars.
- Performance/metrics tracking.
- Tone/brand-voice configuration UI (use a sensible default for this slice).
- Bulk regeneration, A/B variants, hashtag optimization.

## Acceptance criteria (testable, user-facing)

- [ ] Given Priya has a profile and at least one recent update, when she requests a week of
      LinkedIn posts, then she receives exactly 5 draft posts.
- [ ] Each draft is editable before anything else can happen to it.
- [ ] No draft is published or scheduled by this feature (drafts only).
- [ ] If she has no recent updates, she sees a clear empty-state prompting her to add one.

## Design notes (how — kept minimal, SOLID/KISS)

- A `GenerateLinkedInDrafts` Action takes the user + recent updates and returns draft models.
- Content generation sits behind a single `GeneratesPlatformContent` contract so the next
  platform (a future slice) is added by implementing it — not by branching on platform name.
  Only LinkedIn implements it now; do not build the others until their slice exists.

## Test plan (Pest)

- Feature test: user with updates → 5 editable drafts, none published.
- Feature test: user with no updates → empty state, no drafts created.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green.
- [ ] LinkedIn generation sits behind the `GeneratesPlatformContent` contract.
- [ ] No speculative scope; no single-consumer abstraction introduced.
- [ ] PR merged, issue closed, `.github/memory.md` updated.
