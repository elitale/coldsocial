# 0028 — Wire content generation to the AI layer (default text model + fallback)

## Metadata

- **Status:** Building
- **Sequence / slug:** `0028` / `wire-content-generation`
- **Branch:** `feature/0028-wire-content-generation`
- **Issue:** #34 · **PR:** _pending_
- **ICP persona:** n/a (internal enabler; unblocks generation for Priya/Marcus/Sofia)
- **Target platform(s):** n/a (platform-agnostic text generation)
- **Depends on:** 0025 (AI provider & model registry, merged in #45)

## Context / background

The AI registry (0025) landed: `AiProvider`/`AiModel` with a per-capability default, an
OpenAI-compatible chat driver (`OpenAiCompatibleChat::complete`), and a `ProviderRequest`
auth seam. Nothing in the app calls it yet — the content features (#8 single draft, #9 weekly,
#12 regenerate) all need one dependable way to turn a prompt into text. This slice adds that
single entry point: resolve the default **text** model, generate, and fall back through the
other enabled text models when one fails. It closes the text half of the fallback-chain
groundwork (#33) and gives #8/#9/#12 something to build on.

## User story

> As a **content feature (on behalf of the user)**, I want **one call that turns a prompt into
> text using the configured default model and falls back when it fails**, so that **generation
> keeps working even if a provider is down or a key is rejected**.

## Problem / pain (in the user's words)

> "I set up my AI provider — now the app should just generate my post. If one model is having a
> bad day, don't fail in my face; try the next one."

## In scope — the smallest valuable slice

- A `TextGenerator` service with `generate(string $prompt, int $maxTokens = 512): string`.
- It selects candidate models = **enabled** text models belonging to **enabled** providers,
  ordered **default first**, then by id (stable order).
- It tries each candidate via `OpenAiCompatibleChat::complete`; on `ProviderRequestException`
  it logs a warning and continues to the next candidate.
- First success returns its text.
- No candidates → throws `ProviderRequestException` ("No enabled text model is configured…").
- All candidates fail → throws `ProviderRequestException` ("Every configured text model
  failed…"), chaining the last provider error.

## Deliberately out of scope (YAGNI)

- Any UI, route, or controller (that arrives with #8 "Generate a single LinkedIn draft").
- Prompt construction from persona/update (that is #8's job — this takes a ready prompt).
- Non-text capabilities (image/video/tts/stt/thinking) — a resolver generic over capability is
  **not** built until a second capability actually generates content (#37–#40, #44).
- Streaming, token/cost accounting, retries/backoff, rate limiting (#42).
- A pluggable strategy/interface for “generators” — one consumer today (KISS).

## Acceptance criteria (testable, user-facing)

**Behaviour**

- [ ] Given an enabled provider with a default text model, when `generate($prompt)` is called,
      then it returns the model's reply text.
- [ ] Given a default text model whose provider errors and a second enabled text model that
      succeeds, when `generate` is called, then it returns the second model's text (fallback).
- [ ] Given enabled text models that all error, when `generate` is called, then it throws
      `ProviderRequestException`.
- [ ] Given no enabled text model (none configured, or only disabled/other-capability), when
      `generate` is called, then it throws `ProviderRequestException`.

**Safety / trust**

- [ ] The service only reads from the registry and calls the provider HTTP API; it publishes /
      sends nothing. API keys continue to flow through `ProviderRequest` (never logged).

**Quality**

- [ ] `composer ci:check` is green (Pint, PHPStan, Pest; front-end unaffected).

## Data & backend (only what the criteria force into existence)

- **Model(s) / migration:** none — reuses `AiProvider` / `AiModel`.
- **Service:** `app/Ai/TextGenerator.php` (constructor-injected `OpenAiCompatibleChat`;
  auto-resolved by the container — no binding needed, single consumer).
- **Candidate query:** `AiModel` where `capability = text`, `enabled = true`, provider
  `enabled = true`, `orderByDesc(is_default)->orderBy(id)`, eager-load `provider`.
- **Errors:** reuse `ProviderRequestException` (distinct messages for “none configured” vs
  “all failed”); no new exception class until a caller must branch on the difference.

## Security & privacy

- **Authorization:** none — internal service, no direct user entry point yet.
- **Data handling:** provider `api_key` stays encrypted/hidden and is only used inside
  `ProviderRequest`; fallback logs include provider slug + model identifier + reason, never keys.

## Design notes (how — minimal, SOLID + YAGNI + KISS)

- Single small class; the fallback loop is a `foreach` over an ordered Eloquent collection —
  no chain-of-responsibility abstraction for one linear list (KISS).
- Capability is hard-coded to `Text` here; the query is trivially generalizable later, but
  generalizing now would be a single-consumer abstraction (YAGNI) — recorded as out of scope.
- Depends on the existing `OpenAiCompatibleChat` (open/closed: Copilot etc. already work
  through `ProviderRequest`, so no driver-specific code here).

## Test plan (Pest — feature tests, RefreshDatabase)

- `TextGeneratorTest`:
  - returns the default text model's reply (Http::fake success) → criterion 1.
  - falls back to the next enabled text model when the default's provider errors → criterion 2.
  - throws when every candidate errors → criterion 3.
  - throws when no enabled text model exists (only a disabled provider / non-text model) →
    criterion 4.
- Uses `AiProvider::factory()` (driver picks the faked base URL) + `AiModel::factory()`
  `->capability()` / `->default()` states; `Http::fake` keyed by each driver's base URL.

## Skills to activate during build

laravel-best-practices · pest-testing. Boost `search-docs` for Http client faking if needed.

## Dependencies / new packages

- none.

## Suggested build order

1. `app/Ai/TextGenerator.php` (candidate query + fallback loop).
2. `tests/Feature/TextGeneratorTest.php` (four criteria via `Http::fake`).
3. `vendor/bin/pint --dirty`; `composer ci:check` green; check off criteria.

## Risks & open questions

- Multiple providers with the same base URL in one test would make `Http::fake` ambiguous —
  tests use distinct drivers (openai vs openrouter) so faked URLs don't collide.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green.
