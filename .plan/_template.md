# <NNNN> — <Feature title>

- **Status:** Discovered | Scoped | Building | InReview | Merged | Validated | Rejected
- **Branch:** `feature/<NNNN>-<slug>`
- **Issue:** #<issue-number>
- **PR:** #<pr-number>
- **ICP persona:** <Priya | Marcus | Sofia — from .github/agents/icp.md>

## User story

> As a **<persona>**, I want **<capability>**, so that **<outcome>**.

## Problem / pain (in the user's words)

<Why this matters now, from the persona's point of view. What is painful today.>

## In scope (the smallest valuable slice)

- <The one path / one platform / one screen this feature delivers.>

## Deliberately out of scope (YAGNI)

- <Everything cut from this slice. Each item is a candidate future plan, not this one.>
- <If you were tempted to add an abstraction for "the next platform" — record it here.>

## Acceptance criteria (testable, user-facing)

- [ ] Given <context>, when <action>, then <observable outcome>.
- [ ] <Nothing is published without explicit user approval, where relevant.>
- [ ] <...>

## Design notes (how — kept minimal, SOLID/KISS)

<Only what's needed to orient the developer. Name the contract a new platform implements, if
relevant. Do not pre-design abstractions with a single consumer.>

## Test plan (Pest)

- <Feature test per acceptance criterion. Use factories. Feature tests preferred.>

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green.
- [ ] New platform work sits behind existing contracts (no `switch` on platform).
- [ ] No speculative scope; no single-consumer abstraction introduced.
- [ ] PR merged, issue closed, `.github/memory.md` updated.
