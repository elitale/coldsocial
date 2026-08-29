# 0040 — Content calendar (month view of scheduled posts)

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0040` / `content-calendar`
- **Branch:** `feature/0040-content-calendar` (stacked on 0039 → 0037 → 0038 → main)
- **Issue:** #15 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — "see what's going out and when, at a glance"
- **Target platform(s):** any (reads `scheduled_at`)
- **Depends on:** 0039/#14 (`scheduled_at` + scheduling), 0037/#16 (user timezone)

## Context / background

Scheduling (#14) records _when_ each approved post goes out. This slice gives the user the obvious
companion view: a **month calendar** so they can see, at a glance, what is scheduled on which day —
placed on the correct day **in their own timezone**. Directly answers the user's ask: "a calendar
where we can see what post has been scheduled on that day."

## User story

> As a **solo founder**, I want **a month calendar of my scheduled posts**, so that **I can see
> what's going out on each day and spot gaps or clashes without opening every draft**.

## In scope — the smallest valuable slice

- A `/calendar` page: a month grid (Sun–Sat) of the current month.
- Each scheduled post shows on its day (time + platform + a short excerpt); clicking opens the draft.
- Prev / Today / Next month navigation (`?month=YYYY-MM`).
- Days are computed in the **user's timezone**; today is highlighted.
- **Calendar** sidebar nav item.

### Screens, states & UX

- **Calendar view:** header with the month label, a "shown in <tz>" hint, and prev/today/next
  controls; a bordered month grid; day cells list their posts as small clickable chips. Empty
  months simply render an empty grid.

## Deliberately out of scope (YAGNI)

- Drag-to-reschedule, week/day/agenda views, creating a post from a day cell.
- Showing published/draft/approved (unscheduled) posts — the calendar is for **scheduled** items.
- iCal export, multi-user/team calendars, colour-by-platform legend.

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] `/calendar` renders the current month; `?month=YYYY-MM` renders that month.
- [ ] A scheduled post appears on its day, at its local time, and links to the draft.
- [ ] Days are placed in the user's timezone (a post at 2099-08-31 20:00 UTC shows on **Sep 1** for
      an Asia/Kolkata user).
- [ ] Posts outside the viewed month, other users' posts, and unscheduled posts do not appear.

**Safety / trust**

- [ ] Auth + verified + completed persona required (same gate as the rest of the app); guests →
      login. Users only ever see their own posts.

**Quality**

- [ ] Themed shadcn grid (light + dark), keyboard-reachable nav. `composer ci:check` green.

## Data & backend

- **No migration** — reads `posts.scheduled_at` (from #14).
- **`CalendarController@index`:** resolves the user's tz; parses `?month` (validated `Y-m`, else
  current month); queries the user's `scheduled_at` posts within the month's UTC bounds; groups them
  by **local** day (`Y-m-d`) → `postsByDay`. Also returns `month`, `today`, `timezone`.
- **Route:** `GET /calendar` → `calendar.index` (auth + verified + persona group).

## Frontend (Inertia v3 + React)

- **`calendar/index.tsx`:** renders the shadcn **`Calendar`** (react-day-picker) in `single`
  select mode, controlled by the server `month`; month nav (`onMonthChange`) does an Inertia visit
  to `?month=YYYY-MM`; scheduled days show a dot (custom `DayButton`); the user's `today` is
  highlighted. A side panel lists the **selected day's** posts (time + platform + excerpt) linking
  to `posts.show`. Day placement is device-timezone-safe (Dates built via `new Date(y, m, d)`).
- **`components/ui/calendar.tsx`:** the shadcn Calendar component (added via the shadcn CLI).
- **Sidebar:** add a **Calendar** item.

## Security & privacy

- Query scoped to `$request->user()->posts()`; `?month` is regex-validated before parsing. No
  cross-user data.

## Test plan (Pest — feature, RefreshDatabase)

- `CalendarTest`: guest → login; a scheduled post shows on its day (id/platform/time); other months
  excluded; **timezone day-placement** (UTC → next local day); only own posts; unscheduled excluded.
  Acting users get a persona for the onboarding gate.

## Skills to activate during build

inertia-react-development · tailwindcss-development · wayfinder-development · laravel-best-practices · pest-testing.

## Dependencies / new packages

- `react-day-picker` + `date-fns` — pulled in by the shadcn `Calendar` component (user asked for
  the shadcn calendar UI).

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
