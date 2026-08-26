# 0025 — AI provider & model registry

## Metadata

- **Status:** Building
- **Sequence / slug:** `0025` / `ai-provider-registry`
- **Branch:** `feature/0025-ai-provider-registry`
- **Issue:** #29 · **PR:** _pending_
- **ICP persona:** n/a (groundwork enabling content generation for all personas)
- **Target platform(s):** n/a
- **Depends on:** none (base of the AI layer, tracker #43)

## Context / background

The product needs AI generation (content now; image/video/audio later). Before any provider can
be called, the app must **persist which providers and models are available** and which model is
the default for each capability. This is the data foundation the AI manager, artisan commands,
default-model selection, and fallback chain all build on. This slice is **persistence only** —
no provider calls.

## User story

> As an **admin**, I want to register AI providers and their models with encrypted credentials,
> so that the app has a single source of truth for which models power each capability.

## In scope — the smallest valuable slice

- `AiProvider` model + migration + factory: `name`, `slug` (unique), `driver`, `base_url`
  (nullable), `api_key` (**encrypted**, hidden), `enabled`, `settings` (json).
- `AiModel` model + migration + factory: `belongsTo(AiProvider)`, `identifier`, `label`
  (nullable), `capability` (enum cast), `enabled`, `is_default`, `settings` (json).
- `AiCapability` string enum: `text | thinking | image | video | tts | stt`.
- Enforce **at most one `is_default = true` per capability** (setting a new default clears the
  previous one).

## Deliberately out of scope (YAGNI)

- Calling any provider / the AI manager + drivers (#30).
- Artisan commands to manage providers/models (#31, #32).
- Fallback ordering (#33), usage logging (#42), admin UI (#41).
- Per-user model overrides.

## Acceptance criteria

- [ ] Providers and models persist with the fields above; `api_key` is encrypted at rest
      (raw DB value ≠ plaintext) and never appears in `toArray()`.
- [ ] `capability` is a typed enum (`AiCapability`); a model `belongsTo` exactly one provider,
      cascading on provider delete.
- [ ] At most one default model exists per capability — setting a new default clears the old one.
- [ ] Factories exist (with a `default()` state for models) for use in tests.

## Design notes (SOLID/KISS)

- Pure persistence + a small `AiCapability` enum. No provider calls, no manager here (that's #30).
- Encrypt `api_key` via the Eloquent `encrypted` cast; hide it via `#[Hidden]` (matching `User`).
- Enforce single-default in the model's `booted()` `saved` hook using a mass `update()` (which
  bypasses model events → no recursion). Simplest correct approach; no observer class for one rule.

## Test plan (Pest — `tests/Feature/AiProviderRegistryTest.php`)

- Provider + models persist; `api_key` encrypted (raw row ≠ plaintext) and absent from `toArray()`.
- `capability` casts to `AiCapability`; `provider` / `models` relations resolve; cascade delete.
- Setting a second default for the same capability unsets the first; defaults across *different*
  capabilities coexist.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green.
- [ ] Secrets encrypted + hidden; nothing logged.
- [ ] PR merged, issue #29 closed, `.github/memory.md` updated.
