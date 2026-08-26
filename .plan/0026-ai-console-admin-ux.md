# 0026 — Friendlier `php artisan ai` admin console

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0026` / `ai-console-admin-ux`
- **Branch:** `feature/0025-ai-provider-registry` (built on the current registry PR)
- **Issue:** _n/a_ · **PR:** #45
- **ICP persona:** n/a (internal **admin** DX — the person configuring AI providers)
- **Target platform(s):** n/a (Artisan CLI)
- **Depends on:** 0025 (AI registry + `php artisan ai` console, PR #45)

<!-- Numbering note: 0004–0039 are loosely reserved by the backlog + AI-groundwork issue
     suggestions; 0026 is taken here as the next sequential .plan FILE. The "suggested plan
     0026" hint in issue #30 will renumber to the next free slot when that driver is built. -->

## Context / background

0025 shipped `php artisan ai` — an interactive console (Laravel Prompts) that manages AI
providers/models by delegating to the granular `ai:*` commands. It works, but for a non-expert
admin it's rougher than it should be:

- The main menu is a **flat 10-item list** with no sense of current state.
- **Adding a provider** asks for a raw `driver` key and a separate `base_url` — the admin must
  know that OpenRouter's driver is `openrouter` and its base URL, etc.
- The **model picker** is a single unfiltered scroll — OpenRouter returns **hundreds** of
  models, so finding one means scrolling forever.
- There's **no guided first run** (empty state) and **no chaining** (after adding a provider you
  bounce back to the menu instead of being offered "test it / add a model").

This slice makes the console **simpler, easier, and friendlier** without changing the underlying
registry or the granular commands.

## User story

> As an **admin configuring coldsocial's AI**, I want the `php artisan ai` console to guide me
> with presets, search, and a status view, so I can go from nothing to a working default model
> in a couple of minutes without knowing driver names, base URLs, or scrolling huge lists.

## Problem / pain (in the user's words)

> "I just want to plug in my OpenRouter key and pick a model. I don't know what a 'driver' or
> 'base URL' is, and I'm not scrolling through 300 models. Just show me where I'm at and walk me
> through it."

## In scope — the smallest valuable slice

Enhancements to the **`php artisan ai`** console only (all delegate to the existing `ai:*`
commands — no new persistence, no contract changes):

1. **Status header each loop.** Above the menu, show a one-glance summary: number of providers
   (and how many enabled) and the **default model per capability** (or "none set"). So the admin
   always sees where things stand instead of running "List".
2. **Guided first run (empty state).** With **no providers**, skip the bare menu and go straight
   into "Let's add your first AI provider", then loop into the normal menu afterwards.
3. **Provider presets.** "Add a provider" starts with a **preset picker** — OpenAI, OpenRouter,
   Anthropic, Google Gemini, GitHub Models, **Custom**. A preset **auto-fills `driver` +
   `base_url`**, so the admin only enters a **name** (pre-filled/suggested) and the **API key**.
   "Custom" keeps today's manual `driver` + `base_url` path.
4. **Searchable model picker.** When adding/choosing a model from a provider's live list, use a
   **type-to-filter search** (Laravel Prompts `search()`) instead of a fixed scroll, with the
   "Enter manually…" escape hatch preserved.
5. **Chained next-steps.** After **adding a provider**, offer (inline) to **test the connection**
   and **add a model**. After **adding a model**, offer to **set it as the default** for its
   capability and to **test it** — instead of returning to the menu each time.
6. **Friendlier copy + explicit cancel.** Clear labels, short helper hints, success confirmations
   with a suggested next step, and a **"Cancel / go back"** choice in each sub-flow so the admin
   can bail out with no side effects.

## Screens, states & UX (Artisan CLI)

- **First run (no providers):** greeting → straight into the preset-driven "add provider" flow.
- **Main loop:** status header (providers + defaults) → action menu → sub-flow → back to header.
- **Add provider:** preset picker → (name suggested, key via secret prompt) → success → "Test it
  now? / Add a model? / Back".
- **Add model:** provider picker → capability picker → **searchable** model picker (or manual) →
  "Set as default for `<capability>`? / Test it? / Back".
- **Cancel:** any sub-flow can be abandoned; nothing is written.
- Keys are always entered via a hidden prompt and never echoed (unchanged from 0025).

## Deliberately out of scope (YAGNI)

- The **web admin UI** (that's issue #41).
- Any **new capability, driver, or generation** behaviour (image/video/tts/stt drivers are
  #37–#40; the text/thinking tester already exists).
- Changing the **granular `ai:*` commands** or their signatures — the console keeps delegating.
- Editing existing providers/models field-by-field, bulk import, i18n, usage/cost analytics.
- Persisting any new data (this is purely presentation/flow over the existing registry).

## Acceptance criteria (testable)

**Behaviour / UX**

- [ ] Given **no providers**, when I run `php artisan ai`, then it greets me and goes straight
      into adding my first provider (no empty menu), then continues into the normal loop.
- [ ] Given **≥1 provider**, when the menu is shown, then a status line reports the provider
      count (+ enabled count) and the **default model per capability** (or "none set").
- [ ] When I choose **Add a provider**, then I first pick a **preset**; picking OpenAI /
      OpenRouter / Anthropic / Gemini / GitHub auto-fills the driver + base URL and only asks me
      for a name + key; **Custom** asks for driver + base URL as today.
- [ ] When I add/choose a model from a provider that supports listing, then the model picker is
      **searchable** (typing filters the list), with an "Enter manually…" option still available.
- [ ] After **adding a provider**, then I'm offered to test it and to add a model without
      returning to the menu.
- [ ] After **adding a model**, then I'm offered to set it as the default for its capability and
      to test it.
- [ ] Every sub-flow offers a **Cancel/Back** that returns to the menu with nothing written.

**Quality**

- [ ] The granular `ai:*` commands are unchanged; the console still delegates to them (no
      duplicated logic).
- [ ] `composer ci:check` is green (ESLint, Prettier, tsc, Pint, PHPStan, Pest).

## Data & backend

- **None.** No migrations, models, or persistence changes. Reads the existing `AiProvider` /
  `AiModel` for the status header and pickers.

## Design notes (SOLID / KISS)

- **Presets** = a small `array<string, array{driver: string, base_url: ?string, name: string}>`
  map (single source in the console) plus a `custom` branch. No new class — one consumer.
- **Status header** = one lightweight query (providers count/enabled + the default `AiModel` per
  capability) rendered with `note()` / a compact `table()`.
- **Searchable picker** = Laravel Prompts `search()` over the model list already fetched by
  `ModelCatalog` (filter in-memory; no extra requests).
- **Chaining** = after a delegated `ai:*` call succeeds, `confirm()` the offered next step and
  call the relevant sub-flow. Keep delegating to `ai:provider:add` / `ai:model:add` /
  `ai:model:default` / `ai:model:test` / `ai:provider:test` — **no logic moves out of the
  commands**, so automation stays intact.
- KISS: this is presentation + flow only. Resist adding a "console service" abstraction for a
  single console.

## Test plan (Pest — command tests)

- **Empty state:** with no providers, `php artisan ai` enters the add-provider flow (assert via
  the preset prompt appearing first), then a provider exists after completing it.
- **Status header:** with a provider + a default text model, the menu output contains the
  provider count and the default model identifier.
- **Presets:** choosing the OpenRouter preset creates a provider with `driver=openrouter` and the
  expected base URL from name + key only (no driver/base-URL prompts).
- **Searchable model picker:** with `Http::fake` returning many models, the add-model flow uses
  the search prompt and creates the chosen model. (Use PendingCommand `expectsSearch` — confirm
  the exact helper during build; fall back to a select assertion if the harness differs.)
- **Chaining:** after add-provider, the "test it / add a model" prompts appear; after add-model,
  the "set default / test" prompts appear.
- **Cancel:** cancelling a sub-flow writes nothing.
- Reuse `Http::fake` for model listing/testing; no real API calls.

## Skills to activate during build

`laravel-best-practices`, `pest-testing`. Laravel Prompts reference: `select`, `search`,
`confirm`, `text`, `password`, `note`, `table`, and the console-test helpers
(`expectsChoice` / `expectsQuestion` / `expectsConfirmation` / `expectsSearch`).

## Dependencies / new packages

- **None** (Laravel Prompts is already in use).

## Suggested build order

1. Status header + empty-state routing in `handle()`.
2. Provider presets in the add-provider flow (driver + base URL auto-fill; `custom` fallback).
3. Searchable model picker (swap the model `select` for `search`).
4. Chained next-steps after add-provider / add-model.
5. Friendlier copy + explicit Cancel/Back in each sub-flow.
6. Console command tests for each; `composer ci:check` green.

## Risks & open questions

- **Testing `search()`** — confirm the PendingCommand helper (`expectsSearch`) works with this
  Laravel version; if not, assert the resulting DB state and use a select-style expectation.
- **Preset base URLs** — verify each provider's current base URL during build (OpenAI,
  OpenRouter, Anthropic, Gemini, GitHub Models); keep them in the single presets map.
- Keep the flat `ai:*` command surface intact so scripted/CI usage is unaffected.

## Definition of Ready

- [x] Plan filled; scope cut with a non-trivial YAGNI list.
- [x] Acceptance criteria are testable via command tests.
- [ ] GitHub issue opened + linked; branch `feature/0026-ai-console-admin-ux` created.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest command tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green.
- [ ] Granular `ai:*` commands unchanged; no new persistence or single-consumer abstraction.
- [ ] PR merged, issue closed, `.github/memory.md` updated.
