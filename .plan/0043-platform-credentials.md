# 0043 — Store & test social platform OAuth app credentials from the CLI

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0043` / `platform-credentials`
- **Branch:** `feature/0043-platform-credentials` (stacked on `feature/0042` → `0041` → `0038`)
- **Issue:** #62 · **PR:** _pending_
- **ICP persona:** the operator standing up a coldsocial instance
- **Depends on:** #17 (`SocialPlatform` enum + `LinkedInOAuth`)

## Context / background

#17 reads LinkedIn OAuth **app** credentials from `config/services.linkedin` (env). Operators need a
first-class way to store per-platform client id/secret in the DB and **test/retest** them from the
CLI — mirroring the existing `php artisan ai` provider console.

## User story

> As the **operator**, I want to store and test each platform's OAuth app credentials from the CLI,
> so I can wire up connections without editing `.env` and confirm the setup is correct.

## In scope — the smallest valuable slice

- `platform_credentials` table + `PlatformCredential` model (one row per platform; **secret
  encrypted + hidden**; `last_tested_at` + `test_passed` + `test_message`).
- Commands (attribute `#[Signature]` + Laravel Prompts):
  `social:credential:set` / `:test` / `:list` / `:remove` + the `social` interactive console.
- `LinkedInOAuth` resolves credentials from the DB, falling back to `config/services`.

## Deliberately out of scope (YAGNI)

- Credentials for coming-soon platforms (only connectable → LinkedIn today).
- A web UI, rotation reminders, per-user app credentials.
- A platform-agnostic credential tester (introduce with the 2nd connectable platform).

## Acceptance criteria (testable)

- [ ] `set` stores client id/secret (secret encrypted, never printed) + a redirect (defaults to the
      callback route); re-running updates the row in place; refuses a non-connectable platform.
- [ ] `test` verifies against the provider, records `last_tested_at` + result; `--test` on `set`
      runs it inline. Token/recognised-client → pass; `invalid_client` → fail.
- [ ] `list` shows the client id + status, never the secret; `remove` deletes.
- [ ] The connect flow uses stored credentials over `config/services`.
- [ ] `composer ci:check` green.

## Design notes (SOLID / KISS)

- `PlatformCredential` mirrors `AiProvider` conventions (attribute `#[Fillable]`/`#[Hidden]`,
  `casts()`, encrypted secret).
- Discrete commands are scriptable (options + `--test`/`--force`, silent redirect default); the
  `social` menu is the friendly path — mirrors the AI console.
- Test logic on `LinkedInOAuth::testCredentials(clientId, clientSecret)` (only LinkedIn is
  connectable); a generic tester waits for the 2nd platform.
- **No Socialite / no new dependency** (consistent with #17).

## Data & backend

- Migration: `platform_credentials` — `platform` (unique), `client_id`, `client_secret` (text,
  encrypted), `redirect_url?`, `last_tested_at?`, `test_passed?`, `test_message?`.
- `LinkedInOAuth`: DB-first credential resolution (config fallback) + `testCredentials()`
  (client-credentials probe → `invalid_client` = fail, otherwise recognised = pass).

## Gotchas discovered

- **larastan quirk:** `PlatformCredential::where('platform', …)->first()` is inferred **non-null**
  by larastan here (so `?->`/`??` on it trips `nullsafe.neverNull`). Resolved with an explicit
  ternary (`$c ? $c->client_id : (string) config(...)`) — runtime-correct and level-7 clean.
- **`$this->input->isInteractive()` is `true` under `$this->artisan()`** — interactive prompts fire
  in tests. Kept discrete commands scriptable (silent redirect default, `--test`/`--force`) so no
  prompt fires when options are supplied.

## Test plan (Pest — `Http::fake`)

- `SocialCredentialCommandTest`: encrypted-secret storage + never printed; update-in-place; reject
  non-connectable; `--test`; test pass/fail (`invalid_client`) with recorded result; list hides the
  secret; remove deletes; connect flow prefers stored creds.

## Dependencies / new packages

- none.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
