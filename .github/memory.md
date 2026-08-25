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
| — | _No features yet. The ICP agent surfaces the first need._ | — | — | — | — | — | Bootstrap complete |

---

## Decisions

- **2026-08-25 — Agent loop + standards bootstrapped.** Established the ICP → Product Owner →
  Product Developer loop, the `.plan/` convention (one feature = one plan = one branch = one
  PR = one issue), and the SOLID + YAGNI + KISS principle set (tie-break KISS → YAGNI →
  SOLID). Tech conventions remain owned by `AGENTS.md` and `.agents/skills/**`; this loop owns
  product scope and process.

---

## Open questions

- _None yet._
