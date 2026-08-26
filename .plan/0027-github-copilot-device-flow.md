# 0027 — GitHub Copilot provider (VS Code-style device flow)

## Metadata

- **Status:** Building
- **Sequence / slug:** `0027` / `github-copilot-device-flow`
- **Branch:** `feature/0025-ai-provider-registry` (stacked on the AI registry PR, like 0026)
- **Issue:** _pending_ · **PR:** #45 (same stack)
- **ICP persona:** n/a (internal **admin** DX — the person wiring up AI providers)
- **Target platform(s):** n/a
- **Depends on:** 0025 (AI registry + `php artisan ai` console), 0026 (console UX)

## Context / background

0025 shipped the provider/model registry and an OpenAI-compatible chat + catalog; 0026 made the
`php artisan ai` console friendly with provider presets. Every provider so far authenticates with
a **pasted API key**. GitHub Copilot can't: like VS Code, it authenticates with an **OAuth device
flow** and then exchanges that token for a short-lived Copilot API token on every call. This slice
adds Copilot as a first-class provider using that flow, reusing the existing OpenAI-compatible
chat/catalog once the request is authenticated.

## User story

> As an **admin with a GitHub Copilot subscription**, I want to sign coldsocial in with the same
> device-code flow VS Code uses, so that I can use my Copilot models without creating or pasting
> an API key.

## Problem / pain (in the user's words)

> "I already pay for Copilot in VS Code. I don't have an API key to paste — I just want to type a
> code on github.com/login/device and be done."

## In scope — the smallest valuable slice

- A `copilot` driver whose API base URL is `https://api.githubcopilot.com` (OpenAI-compatible for
  `/models` and `/chat/completions`).
- `php artisan ai:provider:copilot` — runs the **device flow**: request a code, show the user the
  `verification_uri` + `user_code`, poll until authorized, then store the returned **GitHub OAuth
  token** as the provider's encrypted `api_key` (driver `copilot`).
- Per-request auth: exchange the stored OAuth token for a **short-lived Copilot token** (cached
  until it expires) and send the editor headers Copilot requires — behind a single auth seam so
  the existing chat/catalog work unchanged.
- A **GitHub Copilot (device login)** entry in the `php artisan ai` provider preset picker that
  runs the flow instead of prompting for a key.

## Deliberately out of scope (YAGNI)

- Web/OAuth-callback UI for the flow (the CLI device flow is enough for the admin).
- Refresh-token bookkeeping beyond caching the exchanged token until its `expires_at`.
- A generic pluggable "auth strategy" registry — there are exactly **two** styles today
  (static key vs Copilot exchange); one `if` in the seam is the KISS choice until a third appears.
- Copilot-specific model metadata, per-model pricing, streaming, or embeddings.
- Auto-opening the browser / copying the code to the clipboard.

## Acceptance criteria (testable)

**Behaviour / UX**

- [ ] Given no matching provider, when I run `php artisan ai:provider:copilot`, then it prints the
      verification URL + user code, waits for authorization, and on success registers a `copilot`
      provider — **without ever printing the token**.
- [ ] Given the user denies or the code expires, when polling ends, then the command fails and no
      provider is created.
- [ ] In `php artisan ai`, choosing **GitHub Copilot (device login)** runs the flow and (on
      success) offers the same test/add-model next steps as other providers.

**Persistence / backend**

- [ ] The stored `api_key` (the GitHub OAuth token) is encrypted at rest and hidden from
      `toArray()` (inherited from `AiProvider`).
- [ ] A `copilot` provider lists models and completes chat via the OpenAI-compatible catalog/chat,
      authenticated with a token **exchanged** from the stored OAuth token and cached between calls.

**Safety / trust**

- [ ] The OAuth token and the exchanged Copilot token are never logged or printed.
- [ ] The public OAuth `client_id` and editor version strings live in `config/services.php`
      (env-overridable) — no secrets hardcoded.

**Quality**

- [ ] `composer ci:check` is green (Pint, PHPStan level 7, Pest). Existing catalog/chat tests
      still pass after the seam refactor.

## Data & backend

- **No migration** — reuses `AiProvider` (`driver = copilot`, encrypted `api_key` = OAuth token,
  `base_url = null` → resolved to `https://api.githubcopilot.com`).
- **`App\Ai\CopilotIdentity`** — static config accessors (client id + editor headers) shared by the
  device flow and the token exchange (two consumers → justified).
- **`App\Ai\GithubDeviceFlow`** — `requestCode()` + `pollForToken()` (uses `Illuminate\Support\Sleep`
  so tests fake the wait).
- **`App\Ai\CopilotToken`** — exchanges the OAuth token for a short-lived Copilot token, cached per
  provider until just before `expires_at`.
- **`App\Ai\ProviderRequest`** — the one auth seam: returns an authenticated `PendingRequest`
  (static bearer key for everyone; exchanged token + Copilot headers for `copilot`).
  `OpenAiCompatibleChat` + `OpenAiCompatibleCatalog` consume it (was inline `Http::withToken`).
- **Command:** `ai:provider:copilot`. Console preset delegates to it.
- **Registration:** `copilot` added to `OpenAiCompatible` base URLs and `ModelCatalog`'s
  OpenAI-compatible set.

## Design notes (SOLID / KISS)

- The seam (`ProviderRequest`) is introduced now because it has **two** real consumers (chat +
  catalog). It contains the only `driver === 'copilot'` branch — a strategy interface for two
  cases would violate KISS/YAGNI; refactor to one when a third auth style lands.
- Device-flow + token-exchange endpoints are stable GitHub URLs → class constants; only the tunable
  identity (client id, editor/plugin versions, integration id, user agent) is config.
- Copilot's `/models` + `/chat/completions` are OpenAI-shaped, so once the request is authenticated
  the existing catalog/chat/tester need no Copilot-specific code.

## Test plan (Pest — `tests/Feature/AiCopilotTest.php` + console additions)

- Device flow: `Http::fake` device-code + a pending→success access-token sequence (`Sleep::fake()`)
  registers a `copilot` provider with the token encrypted; output shows the code, never the token.
- Denied/expired: command fails, no provider created.
- Copilot catalog: `Http::fake` the token exchange + `/models`; `ModelCatalog::models()` returns
  them and the outgoing request carries `Authorization: Bearer <exchanged>` + `Copilot-Integration-Id`.
- Caching: two catalog calls → one token exchange (assert send count).
- Console: choosing the Copilot preset runs the flow and creates the provider.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green.
- [ ] Tokens encrypted + never logged; identity in config.
- [ ] PR updated, `.github/memory.md` row added.
