# Project Memory — coldsocial

> Append-only shared memory for the agent loop. Every agent **reads this before acting** and
> **appends after acting**, so context compounds across features instead of resetting. Keep
> entries short and factual. Newest entries go at the **top** of each section.

## How to use this file

- **Feature ledger** — one row per feature as it moves through the loop. Update the row's
  status in place; add a new row per feature.
- **Decisions** — durable choices that outlive a single feature (a chosen pattern, a rejected
  approach, a standing constraint). Add; do not rewrite history — supersede with a new entry.
- **Open questions** — anything blocked awaiting a human or another agent.

Statuses: `Discovered` → `Scoped` → `Building` → `InReview` → `Merged` → `Validated`
(or `Rejected`).

---

## Feature ledger

| # | Feature | Status | Plan | Issue | PR | Owner (agent) | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 0002 | Project foundation: theme, shadcn/ui, auth | Scoped | [plan](../.plan/0002-project-foundation-theme-shadcn-auth.md) | [#1](https://github.com/elitale/coldsocial/issues/1) | [#2](https://github.com/elitale/coldsocial/pull/2) | Product Owner | Enabling foundation; build order in plan |

---

## Decisions

- **2026-08-25 — Auth stack = Laravel Fortify (headless).** For feature 0002 we back
  authentication with Fortify rather than hand-rolling or using a full UI kit: it's the
  official headless backend the Laravel React starter kit uses, pairs cleanly with Inertia
  pages, and leaves us owning the React UI. Two-factor and social sign-in are deferred (YAGNI).
- **2026-08-25 — Agent loop + standards bootstrapped.** Established the ICP → Product Owner →
  Product Developer loop, the `.plan/` convention (one feature = one plan = one branch = one
  PR = one issue), and the SOLID + YAGNI + KISS principle set (tie-break KISS → YAGNI →
  SOLID). Tech conventions remain owned by `AGENTS.md` and `.agents/skills/**`; this loop owns
  product scope and process.

---

## Open questions

- _None yet._
