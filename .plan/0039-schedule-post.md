# 0039 — Schedule an approved post for a date & time

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0039` / `schedule-post`
- **Branch:** `feature/0039-schedule-post` (stacked on 0037 → 0038 → main)
- **Issue:** #14 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — batch content now, have it go out later
- **Target platform(s):** LinkedIn
- **Depends on:** 0034/0013 (`Post` + approval), 0037/#16 (user timezone)

## Context / background

Approval (#13) marks a post ready; a timezone lives on the user (#16). This slice lets the user
pin an approved post to a **date & time in their own timezone**, storing it as UTC. It's the input
the calendar (#15) and auto-publisher (#20) consume.

## User story

> As a **solo founder**, I want **to schedule an approved post for a specific date and time**, so
> that **it's queued to go out when I want without me being online**.

## In scope — the smallest valuable slice

- On the draft view, for **approved** (or already-scheduled) posts: a date-&-time picker + Schedule.
- The chosen local time is interpreted in the user's timezone and stored as UTC (`scheduled_at`);
  status becomes **scheduled**.
- **Reschedule** (pick a new time) and **Unschedule** (back to approved).
- Can't schedule a draft (must approve first) or a time in the past.

### Screens, states & UX

- **Draft view:** a "Schedule" card (only when approved/scheduled) with a `datetime-local` input;
  when scheduled, shows "Scheduled for <local time> (<tz>)" + Unschedule. Status badge shows
  **Scheduled**.
- **Validation/error:** required; past time and non-approved status rejected inline.

## Deliberately out of scope (YAGNI)

- Actually publishing at the scheduled time (#19 now / #20 queue) — this only records the time.
- The calendar view (#15).
- Recurring schedules, per-platform times, "best time to post" suggestions.
- Reverting to draft when a scheduled post is edited.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] An approved post can be scheduled; status becomes `scheduled` and `scheduled_at` is stored.
- [ ] The chosen time is interpreted in the user's timezone (09:00 Asia/Kolkata → 03:30 UTC).
- [ ] A draft can't be scheduled; a past time is rejected.
- [ ] Unschedule returns the post to `approved` and clears `scheduled_at`.

**Safety / trust**

- [ ] Owner-only (cross-user → 403); guests → login. Nothing is published — only queued.

**Quality**

- [ ] Themed shadcn (light + dark). `composer ci:check` green.

## Data & backend

- **Migration:** add nullable, indexed `scheduled_at` timestamp to `posts`.
- **Enum:** add `PostStatus::Scheduled`.
- **Model:** cast `scheduled_at` → datetime; not fillable (set in the controller).
- **Validation (`SchedulePostRequest`):** `scheduled_at` required|date (future + status checked in
  the controller, which needs the user's tz).
- **Controller:** `schedule` (owner + status guard → `Carbon::parse(input, userTz)->utc()`, reject
  past → set status + scheduled_at) and `unschedule`. `show` also passes `timezone` +
  `scheduledInput` (local value for the picker).
- **Routes:** `POST /posts/{post}/schedule`, `POST /posts/{post}/unschedule`.

## Frontend (Inertia v3 + React)

- **Draft view (`posts/show.tsx`):** Schedule card + `datetime-local` picker + Unschedule; local
  time rendered with `Intl` in the user's tz.
- **Shared `PostStatusBadge`** component (draft/approved/scheduled) used by show + library.
- **Type:** add `status: 'scheduled'` + `scheduled_at` to `post.ts`.

## Security & privacy

- `abort_unless` owner on schedule/unschedule; times are the user's own.

## Test plan (Pest — feature, RefreshDatabase)

- `SchedulePostTest`: guest → login; schedule an approved post; tz interpretation (Kolkata→UTC);
  draft rejected; past rejected; unschedule; cross-user → 403. (Acting users get a persona for the
  onboarding gate.)

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
