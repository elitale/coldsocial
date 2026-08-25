# <NNNN> — <Feature title>

<!--
HOW TO USE THIS TEMPLATE
- Copy this file to .plan/<NNNN>-<slug>.md (next zero-padded number, kebab-case slug) and fill
  EVERY section. No plan, no code.
- One feature = one plan = one branch = one PR = one issue.
- Guidance lives in HTML comments (invisible in rendered Markdown). Replace <angle-bracket>
  placeholders. Delete any section marked "(optional / if applicable)" that genuinely does not
  apply — but never leave an empty heading.
- Principles that govern every choice below: SOLID + YAGNI + KISS, tie-broken in the order
  KISS → YAGNI → SOLID. Only introduce an interface/factory/event/config flag once a SECOND
  concrete consumer exists. See .github/copilot-instructions.md.
-->

## Metadata

- **Status:** Discovered | Scoped | Building | InReview | Merged | Validated | Rejected
- **Sequence / slug:** `<NNNN>` / `<kebab-slug>`
- **Branch:** `feature/<NNNN>-<slug>`
- **Issue:** #<issue-number> · **PR:** #<pr-number>
- **ICP persona:** <Priya (solo founder) | Marcus (executive) | Sofia (creator) — .github/agents/icp.md>
- **Target platform(s):** <LinkedIn | TikTok | Instagram | YouTube | Facebook | n/a — prefer ONE first>
- **Depends on:** <plan numbers / PRs this builds on, or "none">

<!-- Status lifecycle: Discovered (ICP) → Scoped (PO) → Building → InReview → Merged (Developer)
     → Validated / Rejected (ICP). Keep this line and the .github/memory.md ledger row in sync. -->

## Context / background

<!-- 2–5 sentences. Where the product is now, what already shipped, and why THIS slice is next.
     Orient a developer who has zero other context. Link the prior plans this depends on. -->

<...>

## User story

> As a **<persona>**, I want **<capability>**, so that **<outcome>**.

<!-- Exactly ONE story per plan. Multiple stories = multiple plans (PO scope rule). -->

## Problem / pain (in the user's words)

<!-- Quote the persona. What is painful TODAY and why it matters now. Human, not technical. -->

> "<the pain, in the persona's own voice>"

## In scope — the smallest valuable slice

<!-- The single path / one platform / one screen that, shipped alone, the ICP would still call
     useful. Describe the actual user-facing behaviour, concretely and in order. -->

- <...>

### Screens, states & UX (if this has UI)

<!-- Name each screen/route and ALL its states — don't skip the unhappy paths. Delete if no UI. -->

- **<route / screen>:** <purpose>
- **Empty / first-run:** <what the user sees with no data>
- **Loading:** <skeleton / pulsing placeholder for deferred props>
- **Error / validation:** <inline messages and where they appear>
- **Success / confirmation:** <what confirms the action succeeded>
- **Theming & a11y:** works in light + dark; themed shadcn components (<list>); labels, focus,
  keyboard navigation.

## Deliberately out of scope (YAGNI)

<!-- MUST be non-trivial. Everything you cut. Each item is a candidate FUTURE plan, not this one.
     If you were tempted to add an abstraction "for the next platform", record it here instead. -->

- <...>
- <...>

## Acceptance criteria (testable, user-facing)

<!-- Each criterion must be assertable by a Pest test OR clickable by a reviewer. Use
     Given / When / Then. No "and also" or "eventually" — those are future slices, cut them. -->

**Behaviour / UX**

- [ ] Given <context>, when <action>, then <observable outcome>.

**Persistence / backend**

- [ ] <...>

**Safety / trust**

- [ ] Nothing is published or sent without the user's explicit approval (where relevant).
- [ ] <...>

**Quality**

- [ ] Screens use themed shadcn components (light + dark).
- [ ] `composer ci:check` is green (ESLint, Prettier, tsc, Pint, PHPStan, Pest).

## Data & backend (only what the criteria force into existence)

<!-- Delete anything no current acceptance criterion requires (YAGNI). No speculative fields. -->

- **Model(s) / migration:** <name; key columns + types; JSON-cast columns; FKs; indexes; cascade>.
- **Relationships:** <e.g. User hasOne X; X belongsTo User>.
- **Validation (Form Request):** <name; rules. Treat ALL input as untrusted — enums via `in:`,
  links via `url`, arrays validated with `*` element rules>.
- **Routes (named, Wayfinder):** <METHOD /path → name (middleware)>. Regenerate Wayfinder;
  never hardcode URLs on the frontend.
- **Controller / action:** <thin controller; business logic in an Action/Service ONLY if a
  second consumer exists — otherwise inline (KISS)>.
- **Factory / seeder:** <factory + realistic state for tests>.
- **Jobs / queue / schedule (if any):** <name; trigger; idempotency; failure handling>.

## Frontend (Inertia v3 + React)

<!-- Delete if backend-only. -->

- **Page(s):** `resources/js/pages/<...>.tsx` rendered via `Inertia::render`.
- **Component(s):** <reuse existing/shadcn first; list any NEW component and why it's needed>.
- **Data flow:** <`useForm` / `<Form>`; single submit vs multi-request; deferred props + an
  animated skeleton for the empty state>.
- **Routes:** import from `@/routes/*` or `@/actions/*` (Wayfinder) — no hardcoded paths.

## Platform integration (if this touches a social platform)

<!-- Substitutability is non-negotiable: a new platform is a NEW class behind an existing
     contract, resolved via the container — NEVER a switch over platform names
     (open/closed + Liskov). Interface-segregate so a platform that can't do X isn't forced to
     stub it. Delete this whole section if not applicable. -->

- **Contract(s) implemented:** <PublishesContent | ReportsMetrics — keep them separate>.
- **New concrete:** `<ClassName>` resolved via the Laravel container.
- **Secrets:** OAuth tokens / API keys via config + env only; never hardcoded, never logged.
- **Untrusted input:** treat every platform API response as untrusted; validate before use.

## Security & privacy

- **Authorization:** <who may read/mutate; policy or gate if needed>.
- **Data handling:** <PII stays private to the user's account; never shared; retention notes>.
- **Abuse / limits:** <rate limits, approval-before-publish, audit trail where relevant>.

## Design notes (how — minimal, SOLID + YAGNI + KISS)

<!-- Tie-break on conflict: KISS → YAGNI → SOLID. Name contracts; do NOT pre-design an
     abstraction that has a single consumer. Explain WHY this is the simplest thing that works. -->

- <Thin controller; validation in the Form Request; persistence via Eloquent; no premature
  service layer for a single call site.>
- <Storage/state choices and the reasoning; what you deliberately did NOT abstract, and why.>

## Test plan (Pest — feature tests preferred)

<!-- One assertion path per acceptance criterion. Use factories (+ custom states) and
     RefreshDatabase against the Postgres `testing` DB. `php artisan make:test --pest <Name>`. -->

- `<FeatureTest>`: <what it asserts → which acceptance criterion it maps to>.
- **Validation:** <invalid enum / URL / payload rejected; authorization denied>.
- **Auth gate (if relevant):** guest → login; unverified → verification.

## Skills to activate during build

<!-- Activate the matching skill BEFORE working in that domain — don't wait until stuck. -->

<inertia-react-development · tailwindcss-development · wayfinder-development ·
laravel-best-practices · pest-testing · fortify-development — keep only what's relevant>.
Use Boost `search-docs` for any version-specific ecosystem API.

## Dependencies / new packages

<!-- Adding a dependency or a new top-level folder needs approval (copilot-instructions §6). -->

- <package + why it's needed, or "none">.

## Suggested build order

1. <Backend: model + migration + factory; relationships>.
2. <Form Request + controller + named routes; regenerate Wayfinder>.
3. <shadcn components to add>.
4. <Page + components wired to one `useForm`; pre-fill from saved data>.
5. <Pest tests; `composer ci:check` green; self-review; check off criteria>.

## Risks & open questions

- <Risk / unknown → mitigation, or the decision needed before or during build.>

## Rollout / migration notes (optional)

- <Data migration, backfill, feature flag, or config toggle. Delete if N/A.>

## Definition of Ready (Product Owner's handoff gate)

- [ ] Every section above is filled (or deliberately removed as N/A).
- [ ] Scope is cut; the "Deliberately out of scope (YAGNI)" list is non-trivial.
- [ ] Acceptance criteria are testable and unambiguous.
- [ ] A GitHub issue is open and linked to this plan; branch name is specified.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green (ESLint, Prettier, tsc, Pint, PHPStan, Pest).
- [ ] New platform work sits behind existing contracts (no `switch` on platform).
- [ ] No speculative scope (YAGNI) and no single-consumer abstraction (KISS) introduced.
- [ ] Secrets via env/config; no tokens logged; platform input treated as untrusted.
- [ ] PR merged (squash), issue closed, `.github/memory.md` updated (Status → Merged/Validated).

## Notes / changelog (optional)

<!-- Running notes as scope legitimately clarifies during the build. A new NEED (not a
     clarification) goes back to the ICP as a fresh plan — it does not silently grow this one. -->

- <...>
