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
| 0002 | Project foundation: theme, shadcn/ui, auth | Merged | [plan](../.plan/0002-project-foundation-theme-shadcn-auth.md) | [#1](https://github.com/elitale/coldsocial/issues/1) | [#2](https://github.com/elitale/coldsocial/pull/2) | Product Developer | Merged to main; dashboard + sidebar included; CI green on Postgres |
| 0003 | User persona intake (onboarding wizard) | Merged | [plan](../.plan/0003-user-persona-intake.md) | [#3](https://github.com/elitale/coldsocial/issues/3) | [#4](https://github.com/elitale/coldsocial/pull/4) | Product Developer | Merged; Persona model + wizard (social links first, custom links, "what we think about you" summary) + sidebar/user-menu links |
| 0040 | Content calendar (month view) | InReview | [plan](../.plan/0040-content-calendar.md) | [#15](https://github.com/elitale/coldsocial/issues/15) | [#59](https://github.com/elitale/coldsocial/pull/59) | Product Developer | `GET /calendar` (`calendar.index`, auth+verified+persona). `CalendarController@index` groups the user's `scheduled_at` posts by their **local** day (user tz), month-scoped via `?month=YYYY-MM` (regex-validated, else current month), UTC bounds in the query. `calendar/index` uses the **shadcn `Calendar`** (react-day-picker, added via shadcn CLI): single-select, controlled month, month nav → Inertia `?month=` visit, scheduled days show a dot (custom `DayButton`), user's `today` highlighted; a side panel lists the **selected day's** posts (time + platform + excerpt) → `posts.show`; **Calendar** sidebar item. Timezone day-placement (2099-08-31 20:00 UTC → Sep 1 for Asia/Kolkata). Added deps `react-day-picker` + `date-fns`. Stacked on #58→#57→#56→main. CI green (152 tests / 536 assertions) |
| 0039 | Schedule an approved post | InReview | [plan](../.plan/0039-schedule-post.md) | [#14](https://github.com/elitale/coldsocial/issues/14) | [#58](https://github.com/elitale/coldsocial/pull/58) | Product Developer | `PostStatus::Scheduled` + nullable indexed `scheduled_at`. `PostController@schedule/unschedule`: owner-only; picker's local time is parsed in the user's tz → `->utc()` for storage; rejects past times and non-approved posts. Draft view gets a **Schedule** card (`datetime-local`, reschedule, unschedule, "Scheduled for <local> (<tz>)"). Shared `PostStatusBadge` (draft/approved/scheduled) used by show + library. Input for the calendar (#15) & auto-publish (#20) — nothing is published yet. Stacked on #57→#56→main. CI green (146 tests / 469 assertions) |
| 0037 | Posting timezone in settings | InReview | [plan](../.plan/0037-posting-timezone.md) | [#16](https://github.com/elitale/coldsocial/issues/16) | [#57](https://github.com/elitale/coldsocial/pull/57) | Product Developer | Nullable `timezone` on users + **Posting** settings page (native IANA `<select>`, Laravel `timezone` rule) + settings nav. Settings stay `auth`-only (not persona-gated). Unblocks #14/#15. Stacked on the #56 gate-tests hotfix. CI green (139 tests / 450 assertions) |
| 0038 | Fix: onboard test users after the persona gate | InReview | _n/a (hotfix)_ | _n/a_ | [#56](https://github.com/elitale/coldsocial/pull/56) | Product Developer | Regression fix — merging the onboarding gate (#25) with the post stack left ~31 post/update tests red (persona-less acting users → 302 to /onboarding). Gave each acting user a completed persona; re-sorted `web.php` imports (Pint drift). Test-only, no behavior change. CI green (135 tests / 431 assertions) |
| 0036 | Onboarding gate (require completed persona) | InReview | [plan](../.plan/0036-onboarding-gate.md) | [#25](https://github.com/elitale/coldsocial/issues/25) | [#54](https://github.com/elitale/coldsocial/pull/54) | Product Developer | `EnsurePersonaIsComplete` middleware on the `auth,verified` group redirects users without a completed persona (missing or `completed_at` null) to `onboarding.edit` (onboarding routes excluded); guests unaffected. Branched off `main`, independent of the #46–#53 content stack. CI green (91 tests / 275 assertions) |
| 0025 | AI provider & model registry + CLI | InReview | [plan](../.plan/0025-ai-provider-registry.md) | [#29](https://github.com/elitale/coldsocial/issues/29) | [#45](https://github.com/elitale/coldsocial/pull/45) | Product Developer | Groundwork (AI tracker #43, Phase A): AiProvider/AiModel + AiCapability enum; encrypted+hidden api_key; single default per capability. Folded in the full artisan CLI (#31 add/list/enable/disable/remove, #32 add/list/default) plus an interactive `php artisan ai` menu console (Laravel Prompts) so no command names need memorising. Attribute `#[Signature]` commands. Plus a provider model catalog: on add-model it verifies the API key and lists the provider's live models to pick (OpenAI-compatible `/models`; `ai:provider:test`). `ai:model:test` sends a real chat round-trip for text/thinking models (image/video/tts/stt report "not wired up yet" until their drivers land). CI green (77 tests) |
| 0026 | Friendlier `php artisan ai` admin console | InReview | [plan](../.plan/0026-ai-console-admin-ux.md) | _n/a_ | [#45](https://github.com/elitale/coldsocial/pull/45) | Product Developer | Admin-DX polish on the 0025 console (same branch/PR): guided first-run (auto-opens add-provider when none exist), status header (provider counts + default models per capability), provider presets that auto-fill the driver (openai/openrouter/anthropic/gemini/github), searchable model picker (`search()`) when the provider exposes a catalog, chained next-steps after add-provider (test connection → add model → test model), and a Cancel entry on every picker. CI green (80 tests / 240 assertions) |
| 0035 | Generate a week of drafts (5 posts) | InReview | [plan](../.plan/0035-generate-week.md) | [#9](https://github.com/elitale/coldsocial/issues/9) | [#53](https://github.com/elitale/coldsocial/pull/53) | Product Developer | **Generate a week** (POST /posts/week): `GenerateWeeklyDrafts` makes 5 drafts from persona voice + recent updates across 5 angles; all-or-nothing (bodies first, then insert); works with 0 updates; graceful no-model error. Extracted shared `App\Content\PersonaVoice` (used by #8 + #9). CI green (130 tests / 423 assertions) |
| 0034 | Approve a draft (approval gate) | InReview | [plan](../.plan/0034-approve-draft.md) | [#13](https://github.com/elitale/coldsocial/issues/13) | [#52](https://github.com/elitale/coldsocial/pull/52) | Product Developer | `status` on `Post` (`PostStatus` enum draft/approved, default draft) + `approve`/`unapprove` actions (owner-only; status set via typed enum, never mass-assignable). Draft view status badge + toggle; library cards show status. The gate scheduling (#14) & publishing (#19/#20) will require. CI green (125 tests / 408 assertions) |
| 0033 | Regenerate / tweak a draft with an instruction | InReview | [plan](../.plan/0033-regenerate-draft.md) | [#12](https://github.com/elitale/coldsocial/issues/12) | [#51](https://github.com/elitale/coldsocial/pull/51) | Product Developer | `posts.regenerate` + **Tweak this draft** form on the draft view: `App\Content\RewriteDraft` feeds current body + free-text instruction to `TextGenerator`, replaces the body in place; owner-only; instruction required; graceful no-model error. CI green (119 tests / 388 assertions) |
| 0032 | Edit a post draft | InReview | [plan](../.plan/0032-edit-draft.md) | [#11](https://github.com/elitale/coldsocial/issues/11) | [#50](https://github.com/elitale/coldsocial/pull/50) | Product Developer | `posts.edit` (pre-filled textarea) + `posts.update` (PATCH body); owner-only edit/update (403); body required; **Edit** button on the draft view. Reuses `Post` (`Model::update()` intact — Update relation is `sourceUpdate()`). CI green (113 tests / 370 assertions) |
| 0031 | Post library: list & manage drafts | InReview | [plan](../.plan/0031-post-library.md) | [#10](https://github.com/elitale/coldsocial/issues/10) | [#49](https://github.com/elitale/coldsocial/pull/49) | Product Developer | `GET /posts` lists the user's drafts newest-first (platform badge + date + `line-clamp` preview → draft view); `DELETE /posts/{post}` owner-only; empty state + **Posts** sidebar nav. Reuses `Post` (#8). CI green (108 tests / 349 assertions) |
| 0030 | Generate a single LinkedIn draft | InReview | [plan](../.plan/0030-generate-linkedin-draft.md) | [#8](https://github.com/elitale/coldsocial/issues/8) | [#48](https://github.com/elitale/coldsocial/pull/48) | Product Developer | `Post` (draft) model + one-click **Generate post** on each update: `App\Content\GenerateLinkedInDraft` builds a persona-voice + update prompt, calls `TextGenerator`, stores a `linkedin` draft; `/posts/{post}` draft view; graceful inline error when no text model; owner-scoped (generate 404 / view 403). `Post`→`Update` relation is `sourceUpdate()` (avoids clobbering `Model::update()`). Spine for #10/#11/#13/#14/#19. CI green (104 tests / 328 assertions) |
| 0029 | Capture updates & source material | InReview | [plan](../.plan/0029-capture-updates.md) | [#7](https://github.com/elitale/coldsocial/issues/7) | [#47](https://github.com/elitale/coldsocial/pull/47) | Product Developer | `Update` model + `/updates` page: capture free-text updates (+ optional source link), list newest-first, delete own; all scoped to the user (index/store via the `updates()` relation, `destroy` aborts 403 for non-owners). Thin controller + `UpdateStoreRequest`; themed shadcn form/list/empty-state + sidebar nav. Also added `withoutVite()` to the Feature suite (Pest `beforeEach`) so Inertia page-render tests don't need a build. Feeds #8. CI green (97 tests / 300 assertions) |
| 0028 | Wire content generation to AI layer (text + fallback) | InReview | [plan](../.plan/0028-wire-content-generation.md) | [#34](https://github.com/elitale/coldsocial/issues/34) | [#46](https://github.com/elitale/coldsocial/pull/46) | Product Developer | `App\Ai\TextGenerator`: resolves enabled text models (default first) from enabled providers and generates via `OpenAiCompatibleChat`, falling back through the rest on `ProviderRequestException`; throws a clear error when none configured or all fail. Backend enabler for #8/#9/#12; covers the text half of the fallback chain (#33). No UI yet. CI green (90 tests / 272 assertions) |
| 0027 | GitHub Copilot provider (VS Code device flow) | InReview | [plan](../.plan/0027-github-copilot-device-flow.md) | _pending_ | [#45](https://github.com/elitale/coldsocial/pull/45) | Product Developer | Adds a `copilot` driver + `php artisan ai:provider:copilot`: runs GitHub's OAuth **device flow** (`GithubDeviceFlow`, uses `Illuminate\Support\Sleep` so tests fake the wait), stores the returned GitHub OAuth token as the encrypted provider `api_key`. Per request, `CopilotToken` exchanges that token for a short-lived Copilot token (cached until just before `expires_at`) and adds editor headers, all behind a new `ProviderRequest` auth seam so the existing OpenAI-compatible catalog/chat/tester hit `https://api.githubcopilot.com` unchanged. Console gains a "GitHub Copilot (device login)" preset. Identity (client id + editor versions) in `config/services.github_copilot`. CI green (86 tests / 267 assertions) |

---

## Decisions

- **2026-08-26 — GitHub Copilot authenticates with the VS Code device flow, not a pasted key
  (feature 0027).** `ai:provider:copilot` runs GitHub's OAuth device flow and stores the returned
  GitHub OAuth token as the encrypted `AiProvider.api_key` (driver `copilot`, base URL resolves to
  `https://api.githubcopilot.com`). Copilot needs a *short-lived* token per call, so `CopilotToken`
  exchanges the OAuth token at `copilot_internal/v2/token` and caches it until just before
  `expires_at`. The one place that knows an auth style differs is `ProviderRequest` (a single
  `driver === 'copilot'` branch): static bearer key for everyone else, exchanged token + editor
  headers for Copilot. Chat + catalog (two consumers) inject `ProviderRequest`, so Copilot needed
  **no** Copilot-specific catalog/chat code — its `/models` + `/chat/completions` are OpenAI-shaped.
  Deliberately **not** a strategy interface (only two styles today → KISS/YAGNI; promote when a
  third lands). Editor identity (client id, editor/plugin versions, integration id, user agent)
  lives in `config/services.github_copilot`; the GitHub endpoints are class constants.

- **2026-08-26 — Dev runs on Sail; the test suite runs on the host (127.0.0.1).** `.env` keeps
  `DB_HOST=pgsql` for Sail (`sail artisan …`, app served on :80). The Pest suite is pinned to
  `DB_HOST=127.0.0.1` via `force="true"` in `phpunit.xml` (CI parity — CI's Postgres is a
  127.0.0.1 service — and host runs), so run `composer ci:check` / `php artisan test` from the
  **host**, not `sail artisan test` (inside the container 127.0.0.1 isn't the `pgsql` service).
  VS Code inherited a stray exported `DB_HOST=pgsql`; the phpunit `force` overrides it for host
  test runs, and Sail ignores it (container reads `.env`).
- **2026-08-26 — AI provider layer starts with a DB registry (feature 0025).** `AiProvider`
  (encrypted, hidden `api_key`) hasMany `AiModel`; `AiModel.capability` is the `AiCapability`
  enum (`text|thinking|image|video|tts|stt`). Exactly one default model per capability is
  enforced in `AiModel::booted()`'s `saved` hook via a mass `update()` (bypasses model events,
  so no recursion). The manager, drivers, artisan commands, and fallback chain build on this
  (issues #30–#44, tracker #43).
- **2026-08-26 — Switched to a sidebar app shell (supersedes the earlier header-shell choice).**
  Per user request the authenticated app now uses the shadcn `sidebar` (collapsible icon rail):
  `AppSidebar` (brand + nav + user footer via `NavUser`) inside `SidebarProvider`, with a
  `SidebarInset` top bar (trigger + breadcrumbs). `AppLayout` drives it; `app-header.tsx` was
  removed. `defaultOpen` comes from the shared `sidebarOpen` prop (backed by the `sidebar_state`
  cookie). Had to fix shadcn's `use-mobile` hook to not call `setState` in an effect (our
  `react-hooks/set-state-in-effect` lint rule). Re-add shadcn components with `--overwrite` to
  avoid the interactive-prompt hang.
- **2026-08-26 — Dashboard uses shadcn UI primitives (from `dashboard-01`).** Ran
  `shadcn add dashboard-01`, kept the reusable UI primitives (card, chart, table, badge, tabs,
  etc.) and built a dashboard of metric cards + a recharts area chart + an activity table
  inside our existing header `AppLayout`. Dropped the block's full sidebar shell
  (`app-sidebar`/`site-header`/`nav-*`) and the heavy drag-and-drop data-table to stay KISS and
  keep our header layout; removed the now-unused deps (`@dnd-kit/*`, `@tanstack/react-table`,
  `@tabler/icons-react`, `vaul`, `sonner`, `next-themes`, `zod`). Kept `recharts` — performance
  charts are core to coldsocial. Note: shadcn detects `pnpm` from `pnpm-workspace.yaml`; run it
  with that file moved aside (or it fails, since the project uses npm), and it prompts to
  overwrite existing components.
- **2026-08-25 — CI + tests run on Postgres (feature 0002).** Superseding the earlier SQLite
  test setup: CI spins up a `postgres:18-alpine` service and the suite runs against a
  dedicated `testing` database (never dev `laravel`), matching the Sail/prod engine. The test
  DB is pinned via `<env>`/`<server>` entries in `phpunit.xml` (pgsql @ `127.0.0.1:5432`, db
  `testing`, `sail`/`password`) so the exported `APP_ENV=local` / `DB_CONNECTION=sqlite` shell
  vars can't redirect it. Local runs use the Sail-provisioned `testing` DB
  (`create-testing-database.sql`), so `php artisan test` requires the Sail Postgres to be up.
- **2026-08-25 — Foundation build choices (feature 0002).** (1) Header-based app shell
  instead of porting the full ~700-line shadcn sidebar — KISS; sidebar can be a later
  enhancement. (2) Settings password routes named `user-password.*` to avoid colliding with
  Fortify's `password.update` (reset-password) route. (3) Lean UI deps: native elements where
  a Radix primitive wasn't essential (only `@radix-ui/react-slot` + `react-dropdown-menu`).
  (4) Test env is forced via `<server>` entries in `phpunit.xml` because this Sail shell
  exports `APP_ENV=local` into `$_SERVER`, which Laravel reads before PHPUnit's `<env>` —
  without this, CSRF stays active in tests and every write request 419s.
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
